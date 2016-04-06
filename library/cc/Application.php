<?php
namespace library\cc
{
	use \library\components;
	
	class Application
	{
		private $config;
		private $storage;
		
		private $request;
		
		private $matchedSitemapItems = array();
		
		private $applicationComponents = array();
		
		public function __construct()
		{
			/*
				TODO
				1. Sitemap matching
				2. Running the attached components
				3. attaching the components to the "controller"
				4. Display the template attached to the sitemapitem
			*/
			$this->config();
			$this->storage();
			
			$this->request = new \library\cc\Request();
			
			$this->sitemapMatching($this->request);
			
			$this->getApplicationComponents();
			
			$this->runApplicationComponents();
			$this->runSitemapComponents();
			
			$this->renderApplicationComponents();
			$this->renderSitemapComponents();
			
			dump($this);
			
			if (preg_match_all('/\\/article\\/\\d+\\/.*/', '/' . $relativeUri, $matches)) {
				dump($matches);
			}
			
			dump($this->toAscii("hälló hallo metw   01ieﻇﺵﻅ﬩ﬡДЧëma(^&()) h?aha: ﻇﺵﻅ﬩ﬡДЧ"));
		}
		
		function toAscii($str, $replace=array(), $delimiter='-') {
			if( !empty($replace) ) {
				$str = str_replace((array)$replace, ' ', $str);
			}

			$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
			$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

			return $clean;
		}
		
		private function config()
		{
			$configPath = __DIR__ . '../../../config.json';
			if (realpath($configPath) !== false) {
				$json = file_get_contents($configPath);
				$this->config = json_decode($json);
			} else {
				throw new \Exception('Couldnt find config file in path ' . $configPath);
			}
		}
		
		private function storage()
		{
			if ($this->getStorageType() == 'json') {
				$this->storage = new \library\storage\JsonStorage($this->getStoragePath());
			}
		}
		
		private function sitemapMatching($request)
		{
			$sitemap = $this->storage->getSitemap();
			$relativeUri = '/' . $request::$relativeUri;
			
			foreach ($sitemap as $sitemapItem) {
				if ($sitemapItem->regex) {
					if (preg_match_all($sitemapItem->url, $relativeUri, $matches)) {
						$this->matchedSitemapItems[] = $sitemapItem;
					}
				} else {
					if ($sitemapItem->url == $relativeUri) {
						$this->matchedSitemapItems[] = $sitemapItem;
					}
				}
			}
		}
		
		private function runApplicationComponents()
		{
			foreach ($this->applicationComponents as $key => $applicationComponent) {
				$class = $applicationComponent->component;
				$template = $applicationComponent->template;
				$parameters = $applicationComponent->parameters;
				$this->applicationComponents[$key]['object'] = $this->getComponentObject($class, $template, $parameters);
			}
		}
		
		private function runSitemapComponents()
		{
			foreach ($this->matchedSitemapItems as $key => $sitemapItem) {
				$class = $sitemapItem->component;
				$template = $sitemapItem->template;
				$parameters = $sitemapItem->parameters;
				
				$this->matchedSitemapItems[$key]->object = $this->getComponentObject($class, $template, $parameters);
				
				$this->matchedSitemapItems[$key]->object->run($this->storage);
			}
		}
		
		private function getComponentObject($class, $template, $parameters)
		{
			$libraryComponentName = '\\library\\components\\' . $class;
			$userComponentName = '\\components\\' . $class;
			
			if (\autoLoad($libraryComponentName, false)) {
				$component = new $libraryComponentName($template, $this->request, $parameters);
			} elseif (\autoLoad($userComponentName, false)) {
				$component = new $userComponentName($template, $this->request, $parameters);
			} else {
				throw new \Exception('Could not load component ' . $class);
			}
			
			if (!$component instanceof \library\components\Component) {
				throw new \Exception('Component not of type Component. Must inherit \library\components\Component');
			}
			
			return $component;
		}
		
		private function renderApplicationComponents()
		{
			foreach ($this->applicationComponents as $applicationComponent) {
				$applicationComponent['object']->render();
			}
		}
		
		private function renderSitemapComponents()
		{
			foreach ($this->matchedSitemapItems as $sitemapItem) {
				$sitemapItem->object->render();
				ob_clean();
				echo $sitemapItem->object->get();
				ob_end_flush();
				exit;
			}
		}
		
		public function getStorageType()
		{
			return $this->config->storageType;
		}
		
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
?>