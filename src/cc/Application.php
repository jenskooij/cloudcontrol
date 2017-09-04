<?php

namespace CloudControl\Cms\cc {

    use CloudControl\Cms\components\Component;
    use CloudControl\Cms\storage\Storage;
    use Whoops\Handler\PrettyPageHandler;
    use Whoops\Run;

    class Application
    {
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
         */
        public function __construct()
        {
            $this->config();
            $this->storage();

            $this->request = new Request();

            $whoops = new Run;
            $whoops->pushHandler(new PrettyPageHandler);
            $whoops->register();

            $this->redirectMatching($this->request);
            $this->sitemapMatching($this->request);

            $this->getApplicationComponents();

            $this->runApplicationComponents();
            $this->runSitemapComponents();

            $this->renderApplicationComponents();
            $this->renderSitemapComponents();
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

        private function redirectMatching($request)
        {
            $redirects = $this->storage->getRedirects()->getRedirects();
            $relativeUri = '/' . $request::$relativeUri;

            foreach ($redirects as $redirect) {
                if (preg_match_all($redirect->fromUrl, $relativeUri, $matches)) {
                    $toUrl = preg_replace($redirect->fromUrl, $redirect->toUrl, $relativeUri);
                    if (substr($toUrl, 0, 1) == '/') {
                        $toUrl = substr($toUrl, 1);
                    }
                    if ($redirect->type == '301') {
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Location: ' . $request::$subfolders . $toUrl);
                        exit;
                    } elseif ($redirect->type == '302') {
                        header('Location: ' . $request::$subfolders . $toUrl, true, 302);
                        exit;
                    } else {
                        throw new \Exception('Invalid redirect type.');
                    }
                }
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
            $sitemap = $this->storage->getSitemap()->getSitemap();
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
         * @param array $parameters
         * @param \stdClass|null $matchedSitemapItem
         *
         * @return mixed
         * @throws \Exception
         */
        private function getComponentObject($class = '', $template = '', $parameters = array(), $matchedSitemapItem)
        {
            $libraryComponentName = '\\CloudControl\Cms\\components\\' . $class;
            $userComponentName = '\\components\\' . $class;

            if (!class_exists($libraryComponentName, false)) {
                $component = new $libraryComponentName($template, $this->request, $parameters, $matchedSitemapItem);
            } elseif (!class_exists($userComponentName, false)) {
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

        public function getStorageDir()
        {
            return $this->config->storageDir;
        }

        public function getApplicationComponents()
        {
            $this->applicationComponents = $this->storage->getApplicationComponents()->getApplicationComponents();
        }

        public function getRootDir()
        {
            return $this->config->rootDir;
        }
    }
}