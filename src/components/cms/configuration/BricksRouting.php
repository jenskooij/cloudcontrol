<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:27
 */

namespace CloudControl\Cms\components\cms\configuration;

use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;

class BricksRouting implements CmsRouting
{

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/configuration/bricks') {
            $this->overviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/bricks/new') {
            $this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/bricks/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->editRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/bricks/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->deleteRoute($request, $cmsComponent);
        }
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    private function overviewRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/bricks';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks()->getBricks());
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/bricks-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getBricks()->addBrick($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
            exit;
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/bricks-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        $brick = $cmsComponent->storage->getBricks()->getBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getBricks()->saveBrick($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
            exit;
        }
        $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICK, $brick);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getBricks()->deleteBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
        exit;
    }
}