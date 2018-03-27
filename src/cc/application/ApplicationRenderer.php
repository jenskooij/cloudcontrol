<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\cc\application;


use CloudControl\Cms\cc\Application;
use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\CachableBaseComponent;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\storage\Storage;

class ApplicationRenderer
{
    protected $storage;
    protected $request;
    protected $application;

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
        if (count($matchedSitemapItems) < 1) {
            return;
        }

        $sitemapItem = current($matchedSitemapItems);
        $this->renderSitemapComponent($sitemapItem);
    }

    /**
     * Set the default caching of pages
     * @param string $intervalString
     * @throws \Exception
     */
    public function setCachingHeaders($intervalString = CachableBaseComponent::DEFAULT_MAXAGE)
    {
        $expires = new \DateTime();
        $interval = new \DateInterval($intervalString);
        $maxAge = date_create('@0')->add($interval)->getTimestamp();
        $expires = $expires->add($interval);
        ResponseHeaders::add(ResponseHeaders::HEADER_EXPIRES, gmdate('D, d M Y H:i:s \G\M\T', $expires->getTimestamp()));
        ResponseHeaders::add(ResponseHeaders::HEADER_CACHE_CONTROL, 'max-age=' . $maxAge);
        ResponseHeaders::add(ResponseHeaders::HEADER_PRAGMA, ResponseHeaders::HEADER_PRAGMA_CONTENT_CACHE);
    }

    /**
     * Set non caching
     * @throws \Exception
     */
    public function setNotCachingHeaders()
    {
        ResponseHeaders::add(ResponseHeaders::HEADER_CACHE_CONTROL, ResponseHeaders::HEADER_CACHE_CONTROL_CONTENT_NO_STORE_NO_CACHE_MUST_REVALIDATE_MAX_AGE_0);
        ResponseHeaders::add(ResponseHeaders::HEADER_PRAGMA, ResponseHeaders::HEADER_PRAGMA_CONTENT_NO_CACHE);
    }

    /**
     * @param $sitemapItem
     * @throws \Exception
     */
    private function renderSitemapComponent($sitemapItem)
    {
        $isCachable = ($sitemapItem->object instanceof CachableBaseComponent) && !CmsComponent::isCmsLoggedIn();

        $this->handleSitemapComponentCaching($sitemapItem, $isCachable);
        $this->sendHeaders();

        echo $sitemapItem->object->get();
        ob_end_flush();
        exit;
    }

    /**
     * @param $sitemapItem
     * @param $isCachable
     * @throws \Exception
     */
    private function handleSitemapComponentCaching($sitemapItem, $isCachable)
    {
        if ($isCachable === false || ($isCachable && !$sitemapItem->object->isCachable())) {
            $sitemapItem->object->render($this->application);
            ob_clean();
            $this->setNotCachingHeaders();
        } elseif ($isCachable && $sitemapItem->object->isCachable()) {
            if (!$sitemapItem->object->isCacheValid()) {
                $sitemapItem->object->render($this->application);
            }
            ob_clean();
            $this->setCachingHeaders($sitemapItem->object->getMaxAge());
        }
    }

    /**
     * Send headers
     */
    private function sendHeaders()
    {
        if (PHP_SAPI !== 'cli') {
            ResponseHeaders::sendAllHeaders();
        }
    }
}