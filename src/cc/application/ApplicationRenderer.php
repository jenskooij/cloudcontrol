<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\cc\application;


use CloudControl\Cms\cc\Application;
use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CachableBaseComponent;
use CloudControl\Cms\storage\Storage;

class ApplicationRenderer
{
    protected $storage;
    protected $request;
    protected $application;
    const HEADER_POWERED_BY = 'Cloud Control - https://getcloudcontrol.org';

    /**
     * ApplicationRenderer constructor.
     * @param Application $application
     * @param Storage $storage
     * @param Request $request
     */
    public function __construct(Application $application, Storage $storage, Request $request)
    {
        $this->storage = $storage;
        $this->request = $request;
        $this->application = $application;
    }

    /**
     * Loop through all application components and render them
     * @param array $applicationComponents
     */
    public function renderApplicationComponents($applicationComponents)
    {
        foreach ($applicationComponents as $applicationComponent) {
            $applicationComponent->{'object'}->render();
        }
    }

    /**
     * Loop through all (matched) sitemap components and render them
     * @param array $matchedSitemapItems
     * @throws \Exception
     */
    public function renderSitemapComponents($matchedSitemapItems)
    {
        foreach ($matchedSitemapItems as $sitemapItem) {
            if (($sitemapItem->object instanceof CachableBaseComponent
                && !$sitemapItem->object->isCachable()) | !$sitemapItem->object instanceof CachableBaseComponent) {
                $sitemapItem->object->render($this->application);
                ob_clean();
                $this->setNotCachingHeaders();
                echo $sitemapItem->object->get();
                ob_end_flush();
                exit;
            } elseif ($sitemapItem->object instanceof CachableBaseComponent
                && $sitemapItem->object->isCachable()) {
                if (!$sitemapItem->object->isCacheValid()) {
                    $sitemapItem->object->render($this->application);
                }
                ob_clean();
                $this->setCachingHeaders($sitemapItem->object->getMaxAge());
                echo $sitemapItem->object->get();
                ob_end_flush();
                exit;
            }
        }
    }

    /**
     * Set the default caching of pages
     * @param string $intervalString
     */
    public function setCachingHeaders($intervalString = CachableBaseComponent::DEFAULT_MAXAGE)
    {
        $expires = new \DateTime();
        $interval = new \DateInterval($intervalString);
        $maxAge = date_create('@0')->add($interval)->getTimestamp();
        $expires = $expires->add($interval);
        header('X-Powered-By: ' . self::HEADER_POWERED_BY);
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', $expires->getTimestamp()));
        header('Cache-Control: max-age=' . $maxAge);
        header('Pragma: cache');
    }

    /**
     * Set non caching
     * @throws \Exception
     */
    public function setNotCachingHeaders()
    {
        header('X-Powered-By: ' . self::HEADER_POWERED_BY);
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}