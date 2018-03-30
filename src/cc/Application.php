<?php

namespace CloudControl\Cms\cc;

use CloudControl\Cms\cc\application\ApplicationRenderer;
use CloudControl\Cms\cc\application\ApplicationRunner;
use CloudControl\Cms\cc\application\UrlMatcher;
use CloudControl\Cms\services\FileService;
use CloudControl\Cms\services\ImageService;
use CloudControl\Cms\services\ValuelistService;
use CloudControl\Cms\storage\Cache;
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
     * @throws \Exception
     */
    public function __construct($rootDir, $configPath)
    {
        $this->rootDir = $rootDir;
        $this->configPath = $configPath;

        $this->config();
        $this->storage();

        Cache::getInstance()->setStoragePath($this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->storageDir);

        $this->request = new Request();
        ResponseHeaders::init();

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
            throw new \RuntimeException('Framework not initialized yet. Consider running composer install');
        }
    }

    /**
     * Initialize the storage
     * @throws \Exception
     */
    private function storage()
    {
        $this->storage = new Storage($this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->storageDir,
            $this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->imagesDir,
            $this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->filesDir);
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
            extract($parameters, EXTR_OVERWRITE);
        }
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

    /**
     * @throws \Exception
     */
    private function urlMatching()
    {
        $urlMatcher = new UrlMatcher($this, $this->storage);
        $urlMatcher->redirectMatching($this->request);
        $urlMatcher->sitemapMatching($this->request);
    }

    private function run()
    {
        $applicationRunner = new ApplicationRunner($this->storage, $this->request);
        $applicationRunner->runApplicationComponents($this->applicationComponents);
        $applicationRunner->runSitemapComponents($this->matchedSitemapItems);
    }

    /**
     * @throws \Exception
     */
    private function render()
    {
        $applicationRenderer = new ApplicationRenderer($this, $this->storage, $this->request);
        $applicationRenderer->renderApplicationComponents($this->applicationComponents);
        $applicationRenderer->renderSitemapComponents($this->matchedSitemapItems);
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