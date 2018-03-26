<?php
/**
 * Created by: Jens
 * Date: 25-3-2018
 */

namespace CloudControl\Cms\components;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components;
use CloudControl\Cms\storage\Storage;

class MultiComponent extends CachableBaseComponent
{
    /**
     * MultiComponent constructor.
     * Applies parameters in the namespace "self" to parent
     *
     * @param string $template
     * @param Request $request
     * @param array $parameters
     * @param $matchedSitemapItem
     */
    public function __construct($template = '', Request $request, $parameters = array(), $matchedSitemapItem)
    {
        $this->parameters = (array) $matchedSitemapItem->parameters;
        $selfParameters = $this->getParametersForNameSpace('self');
        $this->parameters = array_merge($this->parameters, $selfParameters);
        parent::__construct($template, $request, $this->parameters, $matchedSitemapItem);
    }

    /**
     * Runs all components that are set
     *
     * @param Storage $storage
     */
    public function run(Storage $storage)
    {
        parent::run($storage);

        $components = $this->getParametersWithoutNamespace();

        foreach ($components as $namespace => $component) {
            $this->loadComponent($namespace, $component);
        }
    }

    /**
     * Tries to determine component class name and wheter it exists
     *
     * @param $namespace String
     * @param $component String
     */
    private function loadComponent($namespace, $component)
    {
        $fullyQualifiedCloudControlComponent = '\\CloudControl\\Cms\\components\\' . $component;
        $fullyQualifiedComponent = '\\components\\' . $component;
        if (class_exists($fullyQualifiedCloudControlComponent, true)) {
            $this->runComponent($namespace, $fullyQualifiedCloudControlComponent);
        } elseif (class_exists($fullyQualifiedComponent, true)) {
            $this->runComponent($namespace, $fullyQualifiedComponent);
        }
    }

    /**
     * Instantiates the component and runs it
     *
     * @param $namespace String
     * @param $fullyQualifiedComponent String
     */
    private function runComponent($namespace, $fullyQualifiedComponent)
    {
        $parameters = $this->getParametersForNameSpace($namespace);
        $component = new $fullyQualifiedComponent('', $this->request, $parameters, $this->matchedSitemapItem);
        if ($component instanceof components\Component) {
            $component->run($this->storage);
            $parameters = $component->getParameters();
            foreach ($parameters as $name => $value) {
                $this->parameters[$namespace . '_' . $name] = $value;
            }
        }
    }

    /**
     * Retrieves all parameters for the given namespace
     *
     * @param $namespace
     * @return array
     */
    private function getParametersForNameSpace($namespace)
    {
        $parameters = array();
        foreach ($this->parameters as $key => $value) {
            if (0 === strpos($key, $namespace . ':')) {
                $parameters[substr($key, strlen($namespace) + 1)] = $value;
            }
        }
        return $parameters;
    }

    /**
     * Retrieves all parameters that have no namespace
     *
     * @return array
     */
    private function getParametersWithoutNamespace()
    {
        $parameters = array();
        foreach ($this->parameters as $key => $value) {
            if (strpos($key, ':') === false) {
                $parameters[$key] = $value;
            }
        }
        return $parameters;
    }
}