<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:06
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;

abstract class CmsRouting
{
    protected static $routes = array();

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    abstract public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent);

    /**
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function doRouting($request, $relativeCmsUri, $cmsComponent)
    {
        if (array_key_exists($relativeCmsUri, $this::$routes)) {
            $method = $this::$routes[$relativeCmsUri];
            $this->$method($request, $cmsComponent);
        }
    }
}