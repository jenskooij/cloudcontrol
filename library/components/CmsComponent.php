<?php
namespace library\components
{

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
					$crypt = new \library\crypt\Crypt();
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
			$loginTemplatePath = __DIR__ . '../../../templates/cms/login.php';
			if (realpath($loginTemplatePath) !== false) {
				ob_clean();
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
			
			$pos = strpos($request::$relativeUri, $this->parameters['cmsPrefix']);
			if ($pos !== false) {
				$relativeCmsUri = substr_replace($request::$relativeUri, '', $pos, strlen($this->parameters['cmsPrefix']));
			}
			
			$template = null;
			
			if ($relativeCmsUri == '' || $relativeCmsUri == '/') {
				$template = 'cms/dashboard';
			} elseif ($relativeCmsUri == '/sitemap') {
				$template = 'cms/sitemap';
				if (isset($request::$post['save'])) {					
					$this->storage->saveSitemap($request::$post);
				}
				$this->parameters['mainNavClass'] = 'sitemap';
				$this->parameters['sitemap'] = $this->storage->getSitemap();
			} elseif ($relativeCmsUri == '/sitemap/new') {
				$template = 'cms/sitemap/form';
				$this->parameters['mainNavClass'] = 'sitemap';
				if (isset($request::$post['title'], $request::$post['template'], $request::$post['component'])) {
					$this->storage->addSitemapItem($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/sitemap');
					exit;
				}
			} elseif ($relativeCmsUri == '/sitemap/edit' && isset($request::$get['slug'])) {
				$template = 'cms/sitemap/form';
				$this->parameters['mainNavClass'] = 'sitemap';
				$sitemapItem = $this->storage->getSitemapItemBySlug($request::$get['slug']);
				if (isset($request::$post['title'], $request::$post['template'], $request::$post['component'])) {
					$this->storage->saveSitemapItem($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/sitemap');
					exit;
				}
				$this->parameters['sitemapItem'] = $sitemapItem;
			} elseif ($relativeCmsUri == '/sitemap/delete' && isset($request::$get['slug'])) {
				$this->storage->deleteSitemapItemBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/sitemap');
				exit;
			} elseif ($relativeCmsUri == '/configuration') {
				$template = 'cms/configuration';
				$this->parameters['mainNavClass'] = 'configuration';
			} elseif ($relativeCmsUri == '/configuration/document-types') {
				$template = 'cms/configuration/document-types';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['documentTypes'] = $this->storage->getDocumentTypes();
			} elseif ($relativeCmsUri == '/configuration/document-types/new') {
				$template = 'cms/configuration/document-types-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$bricks = $this->storage->getBricks();
				if (isset($request::$post['title'])) {
					$this->storage->addDocumentType($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
					exit;
				}
				$this->parameters['bricks'] = $bricks;
			} elseif ($relativeCmsUri == '/configuration/document-types/edit' && isset($request::$get['slug'])) {
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
			} elseif ($relativeCmsUri == '/configuration/document-types/delete' && isset($request::$get['slug'])) {
				$this->storage->deleteDocumentTypeBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
				exit;
			} elseif ($relativeCmsUri == '/configuration/bricks') {
				$template = 'cms/configuration/bricks';
				$this->parameters['mainNavClass'] = 'configuration';
				$this->parameters['bricks'] = $this->storage->getBricks();
			} elseif ($relativeCmsUri == '/configuration/bricks/new') {
				$template = 'cms/configuration/bricks-form';
				$this->parameters['mainNavClass'] = 'configuration';
				if (isset($request::$post['title'])) {
					$this->storage->addBrick($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/bricks');
					exit;
				}
			} elseif ($relativeCmsUri == '/configuration/bricks/edit' && isset($request::$get['slug'])) {
				$template = 'cms/configuration/bricks-form';
				$this->parameters['mainNavClass'] = 'configuration';
				$brick = $this->storage->getBrickBySlug($request::$get['slug']);
				if (isset($request::$post['title'])) {
					$this->storage->saveBrick($request::$get['slug'], $request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/bricks');
					exit;
				}
				$this->parameters['brick'] = $brick;
			} elseif ($relativeCmsUri == '/configuration/bricks/delete' && isset($request::$get['slug'])) {
				$this->storage->deleteBrickBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/bricks');
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