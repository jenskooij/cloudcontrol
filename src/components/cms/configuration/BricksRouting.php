<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:27
 */

namespace CloudControl\Cms\components\cms\configuration;

use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;

class BricksRouting extends CmsRouting
{
    protected static $routes = array(
        '/configuration/bricks' => 'overviewRoute',
        '/configuration/bricks/new' => 'newRoute',
        '/configuration/bricks/edit' => 'editRoute',
        '/configuration/bricks/delete' => 'deleteRoute',
    );

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        $this->doRouting($request, $relativeCmsUri, $cmsComponent);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    protected function overviewRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/bricks';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_BRICKS, $cmsComponent->storage->getBricks()->getBricks());
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/bricks-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getBricks()->addBrick($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/bricks-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $brick = $cmsComponent->storage->getBricks()->getBrickBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getBricks()->saveBrick($request::$get[CmsConstants::GET_PARAMETER_SLUG],
                $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
            exit;
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_BRICK, $brick);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getBricks()->deleteBrickBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
        exit;
    }
}