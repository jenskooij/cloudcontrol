<?php

namespace CloudControl\Cms\components {

    use CloudControl\Cms\cc\Application;
    use CloudControl\Cms\cc\Request;
    use CloudControl\Cms\storage\Storage;

    class BaseComponent implements Component
    {
        /**
         * @var string
         */
        protected $template;
        /**
         * @var \CloudControl\Cms\cc\Request
         */
        protected $request;
        /**
         * @var Storage
         */
        protected $storage;
        /**
         * @var mixed
         */
        protected $renderedContent;
        /**
         * @var array
         */
        protected $parameters = array();
        /***
         * @var \stdClass
         */
        protected $matchedSitemapItem;

        /**
         * BaseComponent constructor.
         *
         * @param string $template
         * @param Request $request
         * @param array $parameters
         * @param         $matchedSitemapItem
         */
        public function __construct($template = '', Request $request, $parameters = array(), $matchedSitemapItem)
        {
            $this->template = $template;
            $this->request = $request;
            $this->parameters = (array)$parameters;
            $this->matchedSitemapItem = $matchedSitemapItem;
        }

        /**
         * Hook for implementation in derived classes
         *
         * @param Storage $storage
         */
        public function run(Storage $storage)
        {
            $this->storage = $storage;
        }

        /**
         * Renders the template
         *
         * @param null|Application $application
         *
         * @throws \Exception
         */
        public function render($application = null)
        {
            $this->renderedContent = $this->renderTemplate($this->template, true, $application);
        }

        /**
         * Returns the rendered content
         *
         * @return mixed
         */
        public function get()
        {
            return $this->renderedContent;
        }

        /**
         * Decoupled render method, for usage in derived classes
         *
         * @param string $template
         *
         * @param bool $obClean
         * @param null | Application $application
         * @param Application $application
         *
         * @return string
         * @throws \Exception
         */
        public function renderTemplate($template = '', $obClean = true, $application = null)
        {
            $templatePath = $this->getTemplatePath($template, $application);
            if (realpath($templatePath) !== false) {
                if ($obClean) {
                    ob_clean();
                }
                $this->parameters['request'] = $this->request;
                if ($application !== null) {
                    $acParameters = $application->getAllApplicationComponentParameters();
                    foreach ($acParameters as $parameters) {
                        extract($parameters);
                    }
                }
                extract($this->parameters);
                include($templatePath);
                return ob_get_contents();
            } else {
                if ($template !== null) { // If template is null, its a application component, which doesnt have a template
                    throw new \Exception('Couldnt find template ' . $templatePath);
                }
            }
        }

        /**
         * Alias for renderTemplate for usage to include templates in other templates
         *
         * @param string $template
         *
         * @param array $parameters
         *
         * @return string
         * @throws \Exception
         */
        public function includeTemplate($template = '', $parameters = array())
        {
            if (is_array($parameters)) {
                foreach ($parameters as $name => $value) {
                    $this->parameters[$name] = $value;
                }
            }
            return $this->renderTemplate($template, false);
        }

        public function getParameters()
        {
            return $this->parameters;
        }

        /**
         * @param $template
         * @param null | Application $application
         * @return string
         */
        protected function getTemplateDir($template, $application = null)
        {
            $templatePath = '';
            if ($application !== null) {
                $templatePath = $application->getTemplateDir();
            }
            $templatePath = $templatePath . $template . '.php';
            return $templatePath;
        }

        /**
         * @param string $template
         * @param $application
         * @return string
         */
        private function getTemplatePath($template, $application)
        {
            $templateDir = $this->getTemplateDir($template, $application);
            if ($application !== null) {
                $rootDir = $application->getRootDir();
                if (strpos($templateDir, $rootDir) === false) {
                    $templatePath = $rootDir . DIRECTORY_SEPARATOR . $templateDir;
                } else {
                    $templatePath = $templateDir;
                }
            } else {
                $templatePath = $templateDir;
            }
            return $templatePath;
        }
    }
}