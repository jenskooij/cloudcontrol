<?php
namespace library\components
{

	use library\crypt\Crypt;
	use library\storage\Storage;

	class CmsComponent extends BaseComponent
	{
		/**
		 * @var \library\storage\Storage
		 */
		protected $storage;
		/**
		 * @var string
		 */
		protected $invalidCredentialsMsg = 'Invalid username / password combination';

		/**
		 * @param \library\storage\Storage $storage
		 *
		 * @return void
		 */
		public function run(Storage $storage)
		{
			$this->parameters['mainNavClass'] = 'default';
			$this->storage = $storage;

			$remoteAddress = $_SERVER['REMOTE_ADDR'];
			$this->checkWhiteList($remoteAddress);
			$this->checkBlackList($remoteAddress);

			$this->checkLogin();

			$this->parameters['userRights'] = $_SESSION['cloudcontrol']->rights;

			$this->routing();
		}

		/**
		 * See if a user is logged or wants to log in and
		 * takes appropriate actions.
		 *
		 * @throws \Exception
		 */
		protected function checkLogin()
		{
			$request = $this->request;
			
			if (!isset($_SESSION['cloudcontrol'])) {
				if (isset($request::$post['username'], $request::$post['password'])) {
					$user = $this->storage->getUserByUsername($request::$post['username']);
					$crypt = new Crypt();
					if (empty($user)) {
						$crypt->encrypt($request::$post['password'], 16); // Buy time, to avoid brute forcing
						$this->parameters['errorMsg'] = $this->invalidCredentialsMsg;
						$this->showLogin();
					} else {
						$salt = $user->salt;
						$password = $user->password;
						
						$passwordCorrect = $crypt->compare($request::$post['password'], $password, $salt);
						
						if ($passwordCorrect) {
							$_SESSION['cloudcontrol'] = $user;
						} else {
							$this->parameters['errorMsg'] = $this->invalidCredentialsMsg;
							$this->showLogin();
						}
					}
				} else {
					$this->showLogin();
				}
			}
		}

		/**
		 * Overrides normal behaviour and only renders the
		 * login screen
		 *
		 * @throws \Exception
		 */
		protected function showLogin()
		{
			$loginTemplatePath = __DIR__ . '/../../templates/cms/login.php';
			if (realpath($loginTemplatePath) !== false) {
				ob_clean();
				$this->parameters['request'] = $this->request;
				extract($this->parameters);
				include($loginTemplatePath);
				ob_end_flush();
				exit;
			} else {
				throw new \Exception('Cannot load login template ' . $loginTemplatePath);
			}
		}

		/**
		 * As an exception, to keep the initial file structure simple
		 * the cms implements it's own routing, apart from the regular sitemap functionality
		 *
		 * @throws \Exception
		 */
		protected function routing()
		{
			$request = $this->request;

			// TODO Use regex match parameter instead of calculating relative uri
			$pos = strpos($request::$relativeUri, $this->parameters['cmsPrefix']);
			if ($pos !== false) {
				$relativeCmsUri = substr_replace($request::$relativeUri, '', $pos, strlen($this->parameters['cmsPrefix']));
			}
			
			$template = null;

			$userRights = $_SESSION['cloudcontrol']->rights;
			
			if ($relativeCmsUri == '' || $relativeCmsUri == '/') {
				$template = 'cms/dashboard';
			} elseif ($relativeCmsUri == '/documents' && in_array('documents', $userRights)) {
				$template = 'cms/documents';
				$this->parameters['documents'] = $this->storage->getDocuments();
				$this->parameters['mainNavClass'] = 'documents';
			} elseif ($relativeCmsUri == '/documents.json') {
				header('Content-type:application/json');
				die(json_encode($this->storage->getDocuments()));
				exit;
			} elseif ($relativeCmsUri == '/documents/new-folder' && isset($request::$get['path']) && in_array('documents', $userRights)) {
				$template = 'cms/documents/folder-form';
				$this->parameters['mainNavClass'] = 'documents';
				if (isset($request::$post['title'], $request::$post['path'])) {
					$this->storage->addDocumentFolder($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/documents');
					exit;
				}
			} elseif ($relativeCmsUri == '/documents/new-document' && isset($request::$get['path']) && in_array('documents', $userRights)) {
				$template = 'cms/documents/document-form';
				$this->parameters['mainNavClass'] = 'documents';
				$this->parameters['smallestImage'] = $this->storage->getSmallestImageSet()->slug;
				if (isset($request::$get['documentType'])) {
					if (isset($request::$post['title'], $request::$get['documentType'], $request::$get['path'])) {
						$this->storage->addDocument($request::$post);
						header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/documents');
						exit;
					}
					$this->parameters['documentType'] = $this->storage->getDocumentTypeBySlug($request::$get['documentType'], true);
					$this->parameters['bricks'] = $this->storage->getBricks();
				} else {
					$this->parameters['documentTypes'] = $this->storage->getDocumentTypes();
				}
			} elseif ($relativeCmsUri == '/documents/edit-document' && isset($request::$get['slug']) && in_array('documents', $userRights)) {
				$template = 'cms/documents/document-form';
				$this->parameters['mainNavClass'] = 'documents';
				$this->parameters['smallestImage'] = $this->storage->getSmallestImageSet()->slug;
				if (isset($request::$post['title'], $request::$get['slug'])) {
					$this->storage->saveDocument($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/documents');
					exit;
				}
				$this->parameters['document'] = $this->storage->getDocumentBySlug($request::$get['slug']);
				$request::$get['path'] = $request::$get['slug'];
				$this->parameters['documentType'] = $this->storage->getDocumentTypeBySlug($this->parameters['document']->type, true);
				$this->parameters['bricks'] = $this->storage->getBricks();
			} elseif ($relativeCmsUri == '/documents/get-brick' && isset($request::$get['slug']) && in_array('documents', $userRights)) {
				$this->parameters['smallestImage'] = $this->storage->getSmallestImageSet()->slug;
				$this->template = 'cms/documents/brick';
				$this->parameters['brick'] = $this->storage->getBrickBySlug($request::$get['slug']);
			} else if ($relativeCmsUri == '/documents/edit-folder' && isset($request::$get['slug']) && in_array('documents', $userRights)) {

				$template = 'cms/documents/folder-form';
				$folder = $this->storage->getDocumentFolderBySlug($request::$get['slug']);

				$path = $request::$get['slug'];
				$path = explode('/', $path);
				array_pop($path);
				$path = implode('/', $path);

				$request::$get['path'] = '/' . $path;

				if (isset($request::$post['title'], $request::$post['content'])) {
					$this->storage->saveDocumentFolder($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/documents');
					exit;
				}

				$this->parameters['mainNavClass'] = 'documents';
				$this->parameters['folder'] = $folder;
			} else if ($relativeCmsUri == '/documents/delete-document' && isset($request::$get['slug']) && in_array('documents', $userRights)) {
				$this->storage->deleteDocumentBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/documents');
				exit;
			} else if ($relativeCmsUri == '/documents/delete-folder' && isset($request::$get['slug']) && in_array('documents', $userRights)) {
				$this->storage->deleteDocumentFolderBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/documents');
				exit;
			} elseif ($relativeCmsUri == '/sitemap' && in_array('sitemap', $userRights)) {
				$template = 'cms/sitemap';
				if (isset($request::$post['save'])) {					
					$this->storage->saveSitemap($request::$post);
				}
				$this->parameters['mainNavClass'] = 'sitemap';
				$this->parameters['sitemap'] = $this->storage->getSitemap();
			} elseif ($relativeCmsUri == '/sitemap/new' && in_array('sitemap', $userRights)) {
				$template = 'cms/sitemap/form';
				$this->parameters['mainNavClass'] = 'sitemap';
				if (isset($request::$post['title'], $request::$post['template'], $request::$post['component'])) {
					$this->storage->addSitemapItem($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/sitemap');
					exit;
				}
			} elseif ($relativeCmsUri == '/sitemap/edit' && isset($request::$get['slug']) && in_array('sitemap', $userRights)) {
				$template = 'cms/sitemap/form';
				$this->parameters['mainNavClass'] = 'sitemap';
				$sitemapItem = $this->storage->getSitemapItemBySlug($request::$get['slug']);
				if (isset($request::$post['title'], $request::$post['template'], $request::$post['component'])) {
					$this->storage->saveSitemapItem($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/sitemap');
					exit;
				}
				$this->parameters['sitemapItem'] = $sitemapItem;
			} elseif ($relativeCmsUri == '/sitemap/delete' && isset($request::$get['slug']) && in_array('sitemap', $userRights)) {
				$this->storage->deleteSitemapItemBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/sitemap');
				exit;
			} elseif ($relativeCmsUri == '/images' && in_array('images', $userRights)) {
				$template = 'cms/images';
				$this->parameters['mainNavClass'] = 'images';
				$this->parameters['images'] = $this->storage->getImages();
				$this->parameters['smallestImage'] = $this->storage->getSmallestImageSet()->slug;
			} elseif ($relativeCmsUri == '/images.json') {
				header('Content-type:application/json');
				die(json_encode($this->storage->getImages()));
				exit;
			} elseif ($relativeCmsUri == '/images/new' && in_array('images', $userRights)) {
				$template = 'cms/images/form';
				$this->parameters['mainNavClass'] = 'images';
				if (isset($_FILES['file'])) {
					$this->storage->addImage($_FILES['file']);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/images');
					exit;
				}
			} elseif ($relativeCmsUri == '/images/delete' && isset($request::$get['file']) && in_array('images', $userRights)) {
				$this->storage->deleteImageByName($request::$get['file']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/images');
				exit;
			} elseif ($relativeCmsUri == '/images/show' && isset($request::$get['file']) && in_array('images', $userRights)) {
				$template = 'cms/images/show';
				$this->parameters['mainNavClass'] = 'images';
				$this->parameters['image'] = $this->storage->getImageByName($request::$get['file']);
			} elseif ($relativeCmsUri == '/files' && in_array('files', $userRights)) {
				$template = 'cms/files';
				$this->parameters['mainNavClass'] = 'files';
				$this->parameters['files'] = $this->storage->getFiles();
			} elseif ($relativeCmsUri == '/files.json') {
				header('Content-type:application/json');
				die(json_encode($this->storage->getFiles()));
				exit;
			} elseif ($relativeCmsUri == '/files/new' && in_array('files', $userRights)) {
				$template = 'cms/files/form';
				$this->parameters['mainNavClass'] = 'files';
				if (isset($_FILES['file'])) {
					$this->storage->addFile($_FILES['file']);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/files');
					exit;
				}
			} elseif ($relativeCmsUri == '/files/get' && isset($request::$get['file']) && in_array('files', $userRights)) {
				$file = $this->storage->getFileByName($request::$get['file']);
				$path = realpath(__DIR__ . '/../../www/files/');
				$quoted = sprintf('"%s"', addcslashes(basename($path . '/' . $file->file), '"\\'));
				$size   = filesize($path . '/' . $file->file);

				header('Content-Description: File Transfer');
				header('Content-Type: ' . $file->type);
				header('Content-Disposition: attachment; filename=' . $quoted);
				header('Content-Transfer-Encoding: binary');
				header('Connection: Keep-Alive');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . $size);

				readfile($path . '/' . $file->file);
				exit;
			} elseif ($relativeCmsUri == '/files/delete' && isset($request::$get['file']) && in_array('files', $userRights)) {
				$this->storage->deleteFileByName($request::$get['file']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/files');
				exit;
			} elseif ($relativeCmsUri == '/configuration' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration';
				$this->parameters['mainNavClass'] = 'configuration';
			} elseif ($relativeCmsUri == '/configuration/users' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/users';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['users'] = $this->storage->getUsers();
			} elseif ($relativeCmsUri == '/configuration/users/new' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/users-form';
				$this->parameters['mainNavClass'] = 'configuration';
				if (isset($_POST['username'])) {
					$this->storage->addUser($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/users');
					exit;
				}
			} elseif ($relativeCmsUri == '/configuration/users/delete' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$this->storage->deleteUserBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/users');
				exit;
			} elseif ($relativeCmsUri == '/configuration/users/edit' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/users-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['user'] = $this->storage->getUserBySlug($request::$get['slug']);
				if (isset($_POST['username'])) {
					$this->storage->saveUser($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/users');
					exit;
				}
			} elseif ($relativeCmsUri == '/configuration/document-types' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/document-types';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['documentTypes'] = $this->storage->getDocumentTypes();
			} elseif ($relativeCmsUri == '/configuration/document-types/new' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/document-types-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$bricks = $this->storage->getBricks();
				if (isset($request::$post['title'])) {
					$this->storage->addDocumentType($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
					exit;
				}
				$this->parameters['bricks'] = $bricks;
			} elseif ($relativeCmsUri == '/configuration/document-types/edit' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/document-types-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$documentType = $this->storage->getDocumentTypeBySlug($request::$get['slug']);
				$bricks = $this->storage->getBricks();
				if (isset($request::$post['title'])) {
					$this->storage->saveDocumentType($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
					exit;
				}
				$this->parameters['documentType'] = $documentType;
				$this->parameters['bricks'] = $bricks;
			} elseif ($relativeCmsUri == '/configuration/document-types/delete' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$this->storage->deleteDocumentTypeBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
				exit;
			} elseif ($relativeCmsUri == '/configuration/bricks' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/bricks';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['bricks'] = $this->storage->getBricks();
			} elseif ($relativeCmsUri == '/configuration/bricks/new' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/bricks-form';
				$this->parameters['mainNavClass'] = 'configuration';
				if (isset($request::$post['title'])) {
					$this->storage->addBrick($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/bricks');
					exit;
				}
			} elseif ($relativeCmsUri == '/configuration/bricks/edit' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/bricks-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$brick = $this->storage->getBrickBySlug($request::$get['slug']);
				if (isset($request::$post['title'])) {
					$this->storage->saveBrick($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/bricks');
					exit;
				}
				$this->parameters['brick'] = $brick;
			} elseif ($relativeCmsUri == '/configuration/bricks/delete' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$this->storage->deleteBrickBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/bricks');
				exit;
			} elseif ($relativeCmsUri == '/configuration/image-set' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/image-set';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['imageSet'] = $this->storage->getImageSet();
			} elseif ($relativeCmsUri == '/configuration/image-set/edit' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/image-set-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$imageSet = $this->storage->getImageSetBySlug($request::$get['slug']);
				if (isset($request::$post['title'])) {
					$this->storage->saveImageSet($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/image-set');
					exit;
				}
				$this->parameters['imageSet'] = $imageSet;
			} elseif ($relativeCmsUri == '/configuration/image-set/new' && in_array('configuration', $userRights)) {
				$template = 'cms/configuration/image-set-form';
				$this->parameters['mainNavClass'] = 'configuration';
				if (isset($request::$post['title'])) {
					$this->storage->addImageSet($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/image-set');
					exit;
				}
			} elseif ($relativeCmsUri == '/configuration/image-set/delete' && isset($request::$get['slug']) && in_array('configuration', $userRights)) {
				$this->storage->deleteImageSetBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/image-set');
				exit;
			} elseif ($relativeCmsUri == '/log-off') {
				$_SESSION['cloudcontrol'] = null;
				unset($_SESSION['cloudcontrol']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix']);
				exit;
			}

			if ($template != null) {
				$this->parameters['body'] = $this->renderTemplate($template);
			}			
		}

		private function checkWhiteList($remoteAddress)
		{
			if (isset($this->parameters['whitelistIps'])) {
				$whitelistIps = explode(',', $this->parameters['whitelistIps']);
				$whitelistIps = array_map("trim", $whitelistIps);
				if (!in_array($remoteAddress, $whitelistIps)) {
					throw new \Exception('Ip address ' . $remoteAddress . ' is not on whitelist');
				}
			}
		}

		private function checkBlackList($remoteAddress)
		{
			if (isset($this->parameters['blacklistIps'])) {
				$blacklistIps = explode(',', $this->parameters['blacklistIps']);
				$blacklistIps = array_map("trim", $blacklistIps);
				if (in_array($remoteAddress, $blacklistIps)) {
					throw new \Exception('Ip address ' . $remoteAddress . ' is on blacklist');
				}
			}
		}
	}
}
?>