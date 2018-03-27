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

    const HEADER_X_POWERED_BY = 'X-Powered-By: ';
    const HEADER_X_POWERED_BY_CONTENT = 'Cloud Control - https://getcloudcontrol.org';
    const HEADER_X_FRAME_OPTIONS = "X-Frame-Options: ";
    const HEADER_X_FRAME_OPTIONS_CONTENT = "SAMEORIGIN";
    const HEADER_X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options: ';
    const HEADER_X_CONTENT_TYPE_OPTIONS_CONTENT = 'nosniff';
    const HEADER_REFERRER_POLICY = 'Referrer-Policy: ';
    const HEADER_REFERRER_POLICY_CONTENT = 'strict-origin-when-cross-origin';
    const HEADER_X_XSS_PROTECTION = 'X-XSS-Protection: ';
    const HEADER_X_XSS_PROTECTION_CONTENT = '1; mode=block';
    const HEADER_SET_COOKIE = 'Set-Cookie: ';
    const HEADER_CONTENT_SECURITY_POLICY = 'Content-Security-Policy: ';
    const HEADER_CONTENT_SECURITY_POLICY_CONTENT_SECURE = 'default-src https: \'unsafe-inline\' \'unsafe-eval\'';
    const HEADER_CONTENT_SECURITY_POLICY_CONTENT_INSECURE = 'default-src \'self\' https: \'unsafe-inline\' \'unsafe-eval\'';
    const HEADER_CONTENT_SECURITY_POLICY_CONTENT_LOCALHOST = 'default-src * \'unsafe-inline\' \'unsafe-eval\' data: blob:;';
    const HEADER_X_CONTENT_SECURITY_POLICY = 'X-Content-Security-Policy: '; // For IE
    const HEADER_STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security: ';
    const HEADER_STRICT_TRANSPORT_SECURITY_CONTENT = 'max-age=31536000;';

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

        $this->setExceptionHandler();

        $this->startServices();

        $this->urlMatching();

        $this->getApplicationComponents();
        $this->run();
        $this->setHeaders();
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
     */
    private function storage()
    {
        $this->storage = new Storage($this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->storageDir,
            $this->config->rootDir . DIRECTORY_SEPARATOR . $this->config->imagesDir, $this->config->filesDir);
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

    /**
     * Sets default headers. Please note that caching headers are set
     * here: \CloudControl\Cms\cc\application\ApplicationRenderer::setCachingHeaders
     * and here: \CloudControl\Cms\cc\application\ApplicationRenderer::setNotCachingHeaders
     */
    private function setHeaders()
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        header(self::HEADER_X_POWERED_BY . self::HEADER_X_POWERED_BY_CONTENT);
        header(self::HEADER_X_FRAME_OPTIONS . self::HEADER_X_FRAME_OPTIONS_CONTENT);
        header(self::HEADER_X_CONTENT_TYPE_OPTIONS . self::HEADER_X_CONTENT_TYPE_OPTIONS_CONTENT);
        header(self::HEADER_REFERRER_POLICY . self::HEADER_REFERRER_POLICY_CONTENT);
        header(self::HEADER_X_XSS_PROTECTION . self::HEADER_X_XSS_PROTECTION_CONTENT);
        header(self::HEADER_SET_COOKIE . '__Host-sess=' . session_id() . '; path=' . Request::$subfolders . '; Secure; HttpOnly; SameSite');
        if (Request::isSecure()) {
            header(self::HEADER_CONTENT_SECURITY_POLICY . self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_SECURE);
            header(self::HEADER_X_CONTENT_SECURITY_POLICY . self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_SECURE);
            header(self::HEADER_STRICT_TRANSPORT_SECURITY . self::HEADER_STRICT_TRANSPORT_SECURITY_CONTENT);
        } elseif (Request::isLocalhost()) {
            header(self::HEADER_CONTENT_SECURITY_POLICY . self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_LOCALHOST);
            header(self::HEADER_X_CONTENT_SECURITY_POLICY . self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_LOCALHOST);
        } else {
            header(self::HEADER_CONTENT_SECURITY_POLICY . self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_INSECURE);
            header(self::HEADER_X_CONTENT_SECURITY_POLICY . self::HEADER_CONTENT_SECURITY_POLICY_CONTENT_INSECURE);
        }

    }
}