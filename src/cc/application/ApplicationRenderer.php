<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\cc\application;


use CloudControl\Cms\cc\Application;
use CloudControl\Cms\cc\Request;
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
        if (!empty($matchedSitemapItems)) {
            $this->setCachingHeaders();
        }
        foreach ($matchedSitemapItems as $sitemapItem) {
            $sitemapItem->object->render($this->application);
            ob_clean();
            echo $sitemapItem->object->get();
            ob_end_flush();
            exit;
        }
    }

    /**
     * Set the default caching of pages to 2 days
     * @throws \Exception
     */
    public function setCachingHeaders()
    {
        $expires = new \DateTime();
        $interval = new \DateInterval('P2D'); // 2 days
        $maxAge = date_create('@0')->add($interval)->getTimestamp();
        $expires = $expires->add($interval);

        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', $expires->getTimestamp()));
        header('Cache-Control: max-age=' . $maxAge);
    }
}