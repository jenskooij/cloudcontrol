<?php
namespace library\cc
{

	use library\components\Component;
	use library\storage\JsonStorage;

	/**
	 * Class Application
	 * @package library\cc
	 */
	class Application
	{
		/**
		 * @var \stdClass
		 */
		private $config;
		/**
		 * @var \library\storage\Storage $config
		 */
		private $storage;

		/**
		 * @var Request
		 */
		private $request;

		/**
		 * @var array
		 */
		private $matchedSitemapItems = array();

		/**
		 * @var array
		 */
		private $applicationComponents = array();

		/**
		 * Application constructor.
		 */
		public function __construct()
		{
			$this->config();
			$this->storage();
			
			$this->request = new Request();
			
			$this->sitemapMatching($this->request);
			
			$this->getApplicationComponents();
			
			$this->runApplicationComponents();
			$this->runSitemapComponents();

			$this->renderApplicationComponents();
			$this->renderSitemapComponents();

			dump($this);
		}

		/**
		 * Initialize the config
		 *
		 * @throws \Exception
		 */
		private function config()
		{
			$configPath = __DIR__ . '/../../config.json';
			if (realpath($configPath) !== false) {
				$json = file_get_contents($configPath);
				$this->config = json_decode($json);
			} else {
				throw new \Exception('Couldn\'t find config file in path ' . $configPath);
			}
		}

		/**
		 * Initialize the storage
		 */
		private function storage()
		{
			if ($this->getStorageType() == 'json') {
				$this->storage = new JsonStorage($this->getStoragePath());
			}
		}

		/**
		 * Loop through sitemap items and see if one matches the requestUri.
		 * If it does, add it tot the matchedSitemapItems array
		 *
		 * @param $request
		 */
		private function sitemapMatching($request)
		{
			$sitemap = $this->storage->getSitemap();
			$relativeUri = '/' . $request::$relativeUri;

			foreach ($sitemap as $sitemapItem) {
				if ($sitemapItem->regex) {
					$matches = array();
					if (preg_match_all($sitemapItem->url, $relativeUri, $matches)) {
						// Make a clone, so it doesnt add the matches to the original
						$matchedClone = clone $sitemapItem;
						$matchedClone->matches = $matches;
						$this->matchedSitemapItems[] = $matchedClone;
						return;
					}
				} else {
					if ($sitemapItem->url == $relativeUri) {
						$this->matchedSitemapItems[] = $sitemapItem;
						return;
					}
				}
			}
		}

		/**
		 * Loop through all application components and run them
		 *
		 * @throws \Exception
		 */
		private function runApplicationComponents()
		{
			foreach ($this->applicationComponents as $key => $applicationComponent) {
				$class = $applicationComponent->component;
				$parameters = $applicationComponent->parameters;
				$this->applicationComponents[$key]->{'object'} = $this->getComponentObject($class, null, $parameters, null);
				$this->applicationComponents[$key]->{'object'}->run($this->storage);
			}
		}

		/**
		 * Loop through all (matched) sitemap components and run them
		 *
		 * @throws \Exception
		 */
		private function runSitemapComponents()
		{
			foreach ($this->matchedSitemapItems as $key => $sitemapItem) {
				$class = $sitemapItem->component;
				$template = $sitemapItem->template;
				$parameters = $sitemapItem->parameters;
				
				$this->matchedSitemapItems[$key]->object = $this->getComponentObject($class, $template, $parameters, $sitemapItem);
				
				$this->matchedSitemapItems[$key]->object->run($this->storage);
			}
		}

		/**
		 * @param string $class
		 * @param string $template
		 * @param array  $parameters
		 * @param \stdClass  $matchedSitemapItem
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		private function getComponentObject($class='', $template='', $parameters=array(), $matchedSitemapItem)
		{
			$libraryComponentName = '\\library\\components\\' . $class;
			$userComponentName = '\\components\\' . $class;
			
			if (\autoLoad($libraryComponentName, false)) {
				$component = new $libraryComponentName($template, $this->request, $parameters, $matchedSitemapItem);
			} elseif (\autoLoad($userComponentName, false)) {
				$component = new $userComponentName($template, $this->request, $parameters, $matchedSitemapItem);
			} else {
				throw new \Exception('Could not load component ' . $class);
			}
			
			if (!$component instanceof Component) {
				throw new \Exception('Component not of type Component. Must inherit \library\components\Component');
			}
			
			return $component;
		}

		/**
		 * Loop through all application components and render them
		 */
		private function renderApplicationComponents()
		{
			foreach ($this->applicationComponents as $applicationComponent) {
				$applicationComponent->{'object'}->render();
			}
		}

		/**
		 * Loop through all (matched) sitemap components and render them
		 */
		private function renderSitemapComponents()
		{
			foreach ($this->matchedSitemapItems as $sitemapItem) {
				$this->setCachingHeaders();
				$sitemapItem->object->render($this);
				ob_clean();
				echo $sitemapItem->object->get();
				ob_end_flush();
				exit;
			}
		}

		public function getAllApplicationComponentParameters()
		{
			$allParameters = array();
			foreach ($this->applicationComponents as $applicationComponent) {
				$parameters = $applicationComponent->{'object'}->getParameters();
				$allParameters[] = $parameters;
			}
			return $allParameters;
		}

		public function unlockApplicationComponentParameters()
		{
			foreach ($this->applicationComponents as $applicationComponent) {
				$parameters = $applicationComponent->{'object'}->getParameters();
				extract($parameters);
			}
		}

		/**
		 * Set the default caching of pages to 2 days
		 */
		public function setCachingHeaders()
		{
			header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 2))); // 2 days
			header("Cache-Control: max-age=" . (60 * 60 * 24 * 2));
		}

		/**
		 * @return string
		 */
		public function getStorageType()
		{
			return $this->config->storageType;
		}

		/**
		 * @return string
		 */
		public function getStoragePath()
		{
			return $this->config->storagePath;
		}

		public function getApplicationComponents()
		{
			$this->applicationComponents = $this->storage->getApplicationComponents();
		}

	}
}