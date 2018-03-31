<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:32
 */

namespace CloudControl\Cms\components\cms\configuration;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;

class ImageSetRouting extends CmsRouting
{
    protected static $routes = array(
        '/configuration/image-set' => 'overviewRoute',
        '/configuration/image-set/new' => 'newRoute',
        '/configuration/image-set/edit' => 'editRoute',
        '/configuration/image-set/delete' => 'deleteRoute',
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
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    protected function overviewRoute(/** @scrutinizer ignore-unused */ $request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/image-set';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_IMAGE_SET,
            $cmsComponent->storage->getImageSet()->getImageSet());
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/image-set-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $imageSet = $cmsComponent->storage->getImageSet()->getImageSetBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getImageSet()->saveImageSet($request::$get[CmsConstants::GET_PARAMETER_SLUG],
                $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/image-set');
            exit;
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_IMAGE_SET, $imageSet);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/image-set-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getImageSet()->addImageSet($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/image-set');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getImageSet()->deleteImageSetBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/image-set');
        exit;
    }
}