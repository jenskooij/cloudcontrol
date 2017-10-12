<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\cc\application;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\Component;
use CloudControl\Cms\storage\Storage;

class ApplicationRunner
{
    private $storage;
    private $request;

    public function __construct(Storage $storage, Request $request)
    {
        $this->storage = $storage;
        $this->request = $request;
    }

    /**
     * Loop through all application components and run them
     *
     * @param $applicationComponents
     */
    public function runApplicationComponents($applicationComponents)
    {
        foreach ($applicationComponents as $key => $applicationComponent) {
            $class = $applicationComponent->component;
            $parameters = $applicationComponent->parameters;
            $applicationComponents[$key]->{'object'} = $this->getComponentObject($class, null, $parameters, null);
            $applicationComponents[$key]->{'object'}->run($this->storage);
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
     * Loop through all (matched) sitemap components and run them
     *
     * @param $matchedSitemapItems
     */
    public function runSitemapComponents($matchedSitemapItems)
    {
        foreach ($matchedSitemapItems as $key => $sitemapItem) {
            $class = $sitemapItem->component;
            $template = $sitemapItem->template;
            $parameters = $sitemapItem->parameters;

            $matchedSitemapItems[$key]->object = $this->getComponentObject($class, $template, $parameters, $sitemapItem);

            $matchedSitemapItems[$key]->object->run($this->storage);
        }
    }
}