<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\cc\application;


use CloudControl\Cms\cc\Application;
use CloudControl\Cms\cc\Request;
use CloudControl\Cms\storage\Storage;

class UrlMatcher
{
    private $application;
    private $storage;

    /**
     * UrlMatcher constructor.
     * @param Application $application
     * @param Storage $storage
     */
    public function __construct(Application $application, Storage $storage)
    {
        $this->application = $application;
        $this->storage = $storage;
    }

    /**
     * Loop through the redirects and see if a redirect needs to take place
     *
     * @param Request $request
     * @throws \Exception
     */
    public function redirectMatching(Request $request)
    {
        $redirects =$this->storage->getRedirects()->getRedirects();
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
     * @param Request $request
     */
    public function sitemapMatching($request)
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
                    $this->application->addMatchedSitemapItem($matchedClone);
                    return;
                }
            } else {
                if ($sitemapItem->url == $relativeUri) {
                    $this->application->addMatchedSitemapItem($sitemapItem);
                    return;
                }
            }
        }
    }

}