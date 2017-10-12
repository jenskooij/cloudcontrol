<?php

namespace CloudControl\Cms\cc {

    use CloudControl\Cms\cc\application\UrlMatcher;
    use CloudControl\Cms\components\Component;
    use CloudControl\Cms\services\FileService;
    use CloudControl\Cms\services\ImageService;
    use CloudControl\Cms\services\ValuelistService;
    use CloudControl\Cms\storage\Storage;
    use Whoops\Handler\PrettyPageHandler;
    use Whoops\Run;

    class Application
    {
        /**
         * @var string
         */
        protected $rootDir;
        /**
         * @var string
         */
        protected $configPath;
        /**
         * @var \stdClass
         */
        private $config;
        /**
         * @var \CloudControl\Cms\storage\Storage
         */
        private $storage;

        /**
         * @var \CloudControl\Cms\cc\Request
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
         * @param string $rootDir
         * @param string $configPath
         */
        public function __construct($rootDir, $configPath)
        {
            $this->rootDir = $rootDir;
            $this->configPath = $configPath;

            $this->config();
            $this->storage();

            $this->request = new Request();

            $this->setExceptionHandler();

            $this->startServices();

            $this->urlMatching();

            $this->getApplicationComponents();

            $this->run();
            $this->render();
        }

        /**
         * Initialize the config
         *
         * @throws \Exception
         */
        private function config()
        {
            if (realpath($this->configPath) !== false) {
                $json = file_get_contents($this->configPath);
                $this->config = json_decode($json);
                $this->config->rootDir = $this->rootDir;
            } else {
                throw new \Exception('Framework not initialized yet. Consider running composer install');
            }
        }

        /**
         * Initialize the storage
         */
        private function storage()
        {
            $this->storage = new Storage($this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->storageDir, $this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->imagesDir, $this->config->filesDir);
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
         * @param array $parameters
         * @param \stdClass|null $matchedSitemapItem
         *
         * @return Component
         * @throws \Exception
         */
        private function getComponentObject($class = '', $template = '', $parameters = array(), $matchedSitemapItem)
        {
            $libraryComponentName = '\\CloudControl\Cms\\components\\' . $class;
            $userComponentName = '\\components\\' . $class;

            if (class_exists($libraryComponentName)) {
                $component = new $libraryComponentName($template, $this->request, $parameters, $matchedSitemapItem);
            } elseif (class_exists($userComponentName)) {
                $component = new $userComponentName($template, $this->request, $parameters, $matchedSitemapItem);
            } else {
                throw new \Exception('Could not load component ' . $class);
            }

            if (!$component instanceof Component) {
                throw new \Exception('Component not of type Component. Must inherit \CloudControl\Cms\components\Component');
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
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 2))); // 2 days
            header("Cache-Control: max-age=" . (60 * 60 * 24 * 2));
        }

        /**
         * @return string
         */
        public function getTemplateDir()
        {
            return $this->config->templateDir;
        }

        /**
         * @return string
         */
        public function getStorageDir()
        {
            return $this->config->storageDir;
        }

        public function getApplicationComponents()
        {
            $this->applicationComponents = $this->storage->getApplicationComponents()->getApplicationComponents();
        }

        /**
         * @return string
         */
        public function getRootDir()
        {
            return $this->config->rootDir;
        }

        private function setExceptionHandler()
        {
            $whoops = new Run;
            $whoops->pushHandler(new PrettyPageHandler);
            $whoops->register();
        }

        private function urlMatching()
        {
            $urlMatcher = new UrlMatcher($this, $this->storage);
            $urlMatcher->redirectMatching($this->request);
            $urlMatcher->sitemapMatching($this->request);
        }

        private function run()
        {
            $this->runApplicationComponents();
            $this->runSitemapComponents();
        }

        private function render()
        {
            $this->renderApplicationComponents();
            $this->renderSitemapComponents();
        }

        private function startServices()
        {
            FileService::getInstance()->init($this->storage);
            ImageService::getInstance()->init($this->storage);
            ValuelistService::getInstance()->init($this->storage);
        }

        public function addMatchedSitemapItem($matchedClone)
        {
            $this->matchedSitemapItems[] = $matchedClone;
        }
    }
}