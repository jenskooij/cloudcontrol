<?php
namespace library\components
{
	class CmsComponent extends BaseComponent
	{
		protected $storage;
		protected $invalidCredentialsMsg = 'Invalid username / password combination';
		
		public function run(\library\storage\Storage $storage)
		{
			$this->parameters['mainNavClass'] = 'default';
			$this->storage = $storage;
			$this->checkLogin();
			$this->routing();
		}
		
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
				if (isset($request::$post['title'])) {
					$this->storage->addDocumentType($request::$post);
					header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
					exit;
				}
			} elseif ($relativeCmsUri == '/configuration/document-types/delete' && isset($request::$get['slug'])) {
				$this->storage->deleteDocumentTypeBySlug($request::$get['slug']);
				header('Location: ' . $request::$subfolders . $this->parameters['cmsPrefix'] . '/configuration/document-types');
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
	}
}
?>