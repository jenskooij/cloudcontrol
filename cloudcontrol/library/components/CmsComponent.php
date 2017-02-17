<?php
namespace library\components {

	use library\components\cms\ConfigurationRouting;
	use library\components\cms\DocumentRouting;
	use library\components\cms\FilesRouting;
	use library\components\cms\ImagesRouting;
	use library\components\cms\SitemapRouting;
	use library\crypt\Crypt;
	use library\storage\Storage;

	class CmsComponent extends BaseComponent
	{
		/**
		 * @var \library\storage\JsonStorage
		 */
		public $storage;

		const INVALID_CREDENTIALS_MESSAGE = 'Invalid username / password combination';

		const MAIN_NAV_CLASS = 'default';

		const PARAMETER_APPLICATION_COMPONENT = 'applicationComponent';
		const PARAMETER_APPLICATION_COMPONENTS = 'applicationComponents';
		const PARAMETER_BLACKLIST_IPS = 'blacklistIps';
		const PARAMETER_BODY = 'body';
		const PARAMETER_BRICK = 'brick';
		const PARAMETER_BRICKS = 'bricks';
		const PARAMETER_CMS_PREFIX = 'cmsPrefix';
		const PARAMETER_CONFIGURATION = 'configuration';
		const PARAMETER_DOCUMENT = 'document';
		const PARAMETER_DOCUMENTS = 'documents';
		const PARAMETER_DOCUMENT_TYPE = 'documentType';
		const PARAMETER_DOCUMENT_TYPES = 'documentTypes';
		const PARAMETER_ERROR_MESSAGE = 'errorMsg';
		const PARAMETER_FILES = 'files';
		const PARAMETER_FOLDER = 'folder';
		const PARAMETER_IMAGE = 'image';
		const PARAMETER_IMAGES = 'images';
		const PARAMETER_IMAGE_SET = 'imageSet';
		const PARAMETER_MAIN_NAV_CLASS = 'mainNavClass';
		const PARAMETER_MY_BRICK_SLUG = 'myBrickSlug';
		const PARAMETER_SITEMAP = 'sitemap';
		const PARAMETER_SITEMAP_ITEM = 'sitemapItem';
		const PARAMETER_SMALLEST_IMAGE = 'smallestImage';
		const PARAMETER_STATIC = 'static';
		const PARAMETER_USER = 'user';
		const PARAMETER_USERS = 'users';
		const PARAMETER_USER_RIGHTS = 'userRights';
		const PARAMETER_WHITELIST_IPS = 'whitelistIps';

		const POST_PARAMETER_COMPONENT = 'component';
		const POST_PARAMETER_PASSWORD = 'password';
		const POST_PARAMETER_SAVE = 'save';
		const POST_PARAMETER_TEMPLATE = 'template';
		const POST_PARAMETER_TITLE = 'title';
		const POST_PARAMETER_USERNAME = 'username';

		const GET_PARAMETER_PATH = 'path';
		const GET_PARAMETER_SLUG = 'slug';

		const FILES_PARAMETER_FILE = 'file';

		const SESSION_PARAMETER_CLOUD_CONTROL = 'cloudcontrol';

		const LOGIN_TEMPLATE_PATH = 'cms/login';

		const CONTENT_TYPE_APPLICATION_JSON = 'Content-type:application/json';

		public $subTemplate = null;


		/**
		 * @param \library\storage\Storage $storage
		 *
		 * @return void
		 */
		public function run(Storage $storage)
		{
			$this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = self::MAIN_NAV_CLASS;
			$this->storage = $storage;

			$remoteAddress = $_SERVER['REMOTE_ADDR'];
			$this->checkWhiteList($remoteAddress);
			$this->checkBlackList($remoteAddress);

			$this->checkLogin();

			$this->parameters[self::PARAMETER_USER_RIGHTS] = $_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL]->rights;

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

			if (!isset($_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL])) {
				if (isset($request::$post[self::POST_PARAMETER_USERNAME], $request::$post[self::POST_PARAMETER_PASSWORD])) {
					$this->checkLoginAttempt($request);
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
			$loginTemplatePath = self::LOGIN_TEMPLATE_PATH;
			$this->renderTemplate($loginTemplatePath);
			ob_end_flush();
			exit;
		}

		/**
		 * As an exception, to keep the initial file structure simple
		 * the cms implements it's own routing, apart from the regular sitemap functionality
		 *
		 * @throws \Exception
		 */
		protected function routing()
		{
			$relativeCmsUri = $this->getRelativeCmsUri($this->request);

			$userRights = $_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL]->rights;

			$this->dashboardRouting($relativeCmsUri);
			$this->logOffRouting($this->request, $relativeCmsUri);
			$this->apiRouting($relativeCmsUri);
			$this->documentRouting($userRights, $relativeCmsUri);
			$this->sitemapRouting($userRights, $relativeCmsUri);
			$this->imageRouting($userRights, $relativeCmsUri);
			$this->filesRouting($userRights, $relativeCmsUri);
			$this->configurationRouting($userRights, $relativeCmsUri);

			$this->renderBody();
		}

		/**
		 * @param $remoteAddress
		 *
		 * @throws \Exception
		 */
		private function checkWhiteList($remoteAddress)
		{
			if (isset($this->parameters[self::PARAMETER_WHITELIST_IPS])) {
				$whitelistIps = explode(',', $this->parameters[self::PARAMETER_WHITELIST_IPS]);
				$whitelistIps = array_map("trim", $whitelistIps);
				if (!in_array($remoteAddress, $whitelistIps)) {
					throw new \Exception('Ip address ' . $remoteAddress . ' is not on whitelist');
				}
			}
		}

		/**
		 * @param $remoteAddress
		 *
		 * @throws \Exception
		 */
		private function checkBlackList($remoteAddress)
		{
			if (isset($this->parameters[self::PARAMETER_BLACKLIST_IPS])) {
				$blacklistIps = explode(',', $this->parameters[self::PARAMETER_BLACKLIST_IPS]);
				$blacklistIps = array_map("trim", $blacklistIps);
				if (in_array($remoteAddress, $blacklistIps)) {
					throw new \Exception('Ip address ' . $remoteAddress . ' is on blacklist');
				}
			}
		}

		/**
		 * @param $request
		 *
		 * @return mixed|string
		 */
		private function getRelativeCmsUri($request)
		{
			// TODO Use regex match parameter instead of calculating relative uri
			$pos = strpos($request::$relativeUri, $this->parameters[self::PARAMETER_CMS_PREFIX]);
			$relativeCmsUri = '/';
			if ($pos !== false) {
				$relativeCmsUri = substr_replace($request::$relativeUri, '', $pos, strlen($this->parameters[self::PARAMETER_CMS_PREFIX]));
			}

			return $relativeCmsUri;
		}

		/**
		 * @param $relativeCmsUri
		 */
		private function apiRouting($relativeCmsUri)
		{
			if ($relativeCmsUri == '/images.json') {
				header(self::CONTENT_TYPE_APPLICATION_JSON);
				die(json_encode($this->storage->getImages()));
			} elseif ($relativeCmsUri == '/files.json') {
				header(self::CONTENT_TYPE_APPLICATION_JSON);
				die(json_encode($this->storage->getFiles()));
			} elseif ($relativeCmsUri == '/documents.json') {
				header(self::CONTENT_TYPE_APPLICATION_JSON);
				die(json_encode($this->storage->getDocuments()));
			}
		}

		private function logOffRouting($request, $relativeCmsUri)
		{
			if ($relativeCmsUri == '/log-off') {
				$_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL] = null;
				unset($_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL]);
				header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX]);
				exit;
			}
		}

		public function setParameter($parameterName, $parameterValue)
		{
			$this->parameters[$parameterName] = $parameterValue;
		}

		public function getParameter($parameterName)
		{
			return $this->parameters[$parameterName];
		}

		/**
		 * @param $relativeCmsUri
		 */
		protected function dashboardRouting($relativeCmsUri)
		{
			if ($relativeCmsUri == '' || $relativeCmsUri == '/') {
				$this->subTemplate = 'cms/dashboard';
			}
		}

		/**
		 * @param $userRights
		 * @param $relativeCmsUri
		 */
		protected function documentRouting($userRights, $relativeCmsUri)
		{
			if (in_array(self::PARAMETER_DOCUMENTS, $userRights)) {
				new DocumentRouting($this->request, $relativeCmsUri, $this);
			}
		}

		/**
		 * @param $userRights
		 * @param $relativeCmsUri
		 */
		protected function sitemapRouting($userRights, $relativeCmsUri)
		{
			if (in_array(self::PARAMETER_SITEMAP, $userRights)) {
				new SitemapRouting($this->request, $relativeCmsUri, $this);
			}
		}

		/**
		 * @param $userRights
		 * @param $relativeCmsUri
		 */
		protected function imageRouting($userRights, $relativeCmsUri)
		{
			if (in_array(self::PARAMETER_IMAGES, $userRights)) {
				new ImagesRouting($this->request, $relativeCmsUri, $this);
			}
		}

		/**
		 * @param $userRights
		 * @param $relativeCmsUri
		 */
		protected function filesRouting($userRights, $relativeCmsUri)
		{
			if (in_array(self::PARAMETER_FILES, $userRights)) {
				new FilesRouting($this->request, $relativeCmsUri, $this);
			}
		}

		/**
		 * @param $userRights
		 * @param $relativeCmsUri
		 */
		protected function configurationRouting($userRights, $relativeCmsUri)
		{
			if (in_array('configuration', $userRights)) {
				new ConfigurationRouting($this->request, $relativeCmsUri, $this);
			}
		}

		protected function renderBody()
		{
			if ($this->subTemplate !== null) {
				$this->parameters[self::PARAMETER_BODY] = $this->renderTemplate($this->subTemplate);
			}
		}

		/**
		 * @param $crypt
		 * @param $request
		 */
		protected function invalidCredentials($crypt, $request)
		{
			$crypt->encrypt($request::$post[self::POST_PARAMETER_PASSWORD], 16); // Buy time, to avoid brute forcing
			$this->parameters[self::PARAMETER_ERROR_MESSAGE] = self::INVALID_CREDENTIALS_MESSAGE;
			$this->showLogin();
		}

		/**
		 * @param $user
		 * @param $crypt
		 * @param $request
		 */
		protected function checkPassword($user, $crypt, $request)
		{
			$salt = $user->salt;
			$password = $user->password;

			$passwordCorrect = $crypt->compare($request::$post[self::POST_PARAMETER_PASSWORD], $password, $salt);

			if ($passwordCorrect) {
				$_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL] = $user;
			} else {
				$this->parameters[self::PARAMETER_ERROR_MESSAGE] = self::INVALID_CREDENTIALS_MESSAGE;
				$this->showLogin();
			}
		}

		/**
		 * @param $request
		 */
		protected function checkLoginAttempt($request)
		{
			$user = $this->storage->getUserByUsername($request::$post[self::POST_PARAMETER_USERNAME]);
			$crypt = new Crypt();
			if (empty($user)) {
				$this->invalidCredentials($crypt, $request);
			} else {
				$this->checkPassword($user, $crypt, $request);
			}
		}
	}
}