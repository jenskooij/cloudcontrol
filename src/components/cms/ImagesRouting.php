<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 16:11
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\CmsComponent;

class ImagesRouting extends CmsRouting
{
    protected static $routes = array(
        '/images' => 'overviewRoute',
        '/images/new' => 'newRoute',
        '/images/new-ajax' => 'newAjaxRoute',
        '/images/delete' => 'deleteRoute',
        '/images/show' => 'showRoute',
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
    protected function overviewRoute(/** @scrutinizer ignore-unused */ $request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'images';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_IMAGES);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_IMAGES, $cmsComponent->storage->getImages()->getImages());
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SMALLEST_IMAGE,
            $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'images/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_IMAGES);
        if (isset($_FILES[CmsConstants::FILES_PARAMETER_FILE])) {
            $cmsComponent->storage->getImages()->addImage($_FILES[CmsConstants::FILES_PARAMETER_FILE]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/images');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newAjaxRoute(/** @scrutinizer ignore-unused */ $request, $cmsComponent)
    {
        if (isset($_FILES[CmsConstants::FILES_PARAMETER_FILE])) {
            $image = $cmsComponent->storage->getImages()->addImage($_FILES[CmsConstants::FILES_PARAMETER_FILE]);
            ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
            ResponseHeaders::sendAllHeaders();
            die(json_encode($image));
        }
        die('error occured');
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    protected function deleteRoute($request, $cmsComponent)
    {
        if (isset($request::$get[CmsConstants::FILES_PARAMETER_FILE])) {
            $cmsComponent->storage->getImages()->deleteImageByName($request::$get[CmsConstants::FILES_PARAMETER_FILE]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/images');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    protected function showRoute($request, $cmsComponent)
    {
        if (isset($request::$get[CmsConstants::FILES_PARAMETER_FILE])) {
            $cmsComponent->subTemplate = 'images/show';
            $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_IMAGES);
            $cmsComponent->setParameter(CmsConstants::PARAMETER_IMAGE,
                $cmsComponent->storage->getImages()->getImageByName($request::$get[CmsConstants::FILES_PARAMETER_FILE]));
        }
    }
}