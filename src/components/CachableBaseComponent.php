<?php
/**
 * Created by jensk on 25-10-2017.
 */

namespace CloudControl\Cms\components;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\storage\Cache;

class CachableBaseComponent extends BaseComponent
{
    const DEFAULT_MAXAGE = 'P7D';

    const PARAMETER_CACHABLE = 'cachable';
    const PARAMETER_MAXAGE = 'maxage';

    protected $cachable = true;
    protected $maxAge = self::DEFAULT_MAXAGE;
    protected $cacheValidity;
    protected $cache;

    /**
     * CachableBaseComponent constructor.
     * @param string $template
     * @param Request $request
     * @param array $parameters
     * @param $matchedSitemapItem
     */
    public function __construct($template = '', Request $request, $parameters = array(), $matchedSitemapItem)
    {
        parent::__construct($template, $request, $parameters, $matchedSitemapItem);

        if (isset($this->parameters[self::PARAMETER_CACHABLE]) && $this->parameters[self::PARAMETER_CACHABLE] === 'false') {
            $this->cachable = false;
        }

        if (isset($this->parameters[self::PARAMETER_MAXAGE])) {
            $this->maxAge = $this->parameters[self::PARAMETER_MAXAGE];
        }

        $this->setCacheValidity();
    }

    public function get()
    {
        $isCachable = $this->isCachable();
        if ($isCachable && $this->isCacheValid()) {
            return $this->cache->contents;
        } elseif ($isCachable && !$this->isCacheValid()) {
            $this->createCache($this->renderedContent);
        }
        return $this->renderedContent;
    }


    /**
     * @return bool
     */
    public function isCachable()
    {
        return $this->cachable;
    }

    /**
     * @return bool
     */
    public function isCacheValid()
    {
        if ($this->isLoggedIn()) {
            return false;
        }
        if ($this->cacheValidity === null) {
            return $this->setCacheValidity();
        }
        return $this->cacheValidity;
    }

    private function isLoggedIn()
    {
        return CmsComponent::isCmsLoggedIn();
    }

    /**
     * @return string
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    protected function setCacheValidity()
    {
        if ($this->cacheValidity === null) {
            $this->cache = Cache::getInstance()->getCacheForPath(Request::$requestUri);
            $cacheExists = $this->cache !== false;
            $cacheExpired = false;
            if ($cacheExists) {
                $cacheCreationStamp = (int)$this->cache->creationStamp;
                $currentTime = time();
                $cacheAge = $currentTime - $cacheCreationStamp;
                $maxAgeInterval = new \DateInterval($this->maxAge);
                $maxAgeSeconds = date_create('@0')->add($maxAgeInterval)->getTimestamp();
                $cacheExpired = $cacheAge > $maxAgeSeconds;
            }
            $this->cacheValidity = $cacheExists && !$cacheExpired;
        }
        return $this->cacheValidity;
    }

    /**
     * Sets the new cache, unless a cms user is logged in
     * @param $renderedContent
     * @throws \RuntimeException
     */
    private function createCache($renderedContent)
    {
        if (!CmsComponent::isCmsLoggedIn()) {
            Cache::getInstance()->setCacheForPath(Request::$requestUri, $renderedContent);
        }
    }

}