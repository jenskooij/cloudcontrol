<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 16:11
 */

namespace library\components\cms;


use library\components\CmsComponent;

class ImagesRouting implements CmsRouting
{

    /**
     * ImagesRouting constructor.
     * @param \library\cc\Request $request
     * @param mixed|string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/images') {
            $cmsComponent->subTemplate = 'cms/images';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_IMAGES);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGES, $cmsComponent->storage->getImages());
            $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getSmallestImageSet()->slug);
        } elseif ($relativeCmsUri == '/images.json') {
            header(CmsComponent::CONTENT_TYPE_APPLICATION_JSON);
            die(json_encode($cmsComponent->storage->getImages()));
        } elseif ($relativeCmsUri == '/images/new') {
            $cmsComponent->subTemplate = 'cms/images/form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_IMAGES);
            if (isset($_FILES[CmsComponent::FILES_PARAMETER_FILE])) {
                $cmsComponent->storage->addImage($_FILES[CmsComponent::FILES_PARAMETER_FILE]);
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/images');
                exit;
            }
        } elseif ($relativeCmsUri == '/images/delete' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
            $cmsComponent->storage->deleteImageByName($request::$get[CmsComponent::FILES_PARAMETER_FILE]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/images');
            exit;
        } elseif ($relativeCmsUri == '/images/show' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
            $cmsComponent->subTemplate = 'cms/images/show';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_IMAGES);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGE, $cmsComponent->storage->getImageByName($request::$get[CmsComponent::FILES_PARAMETER_FILE]));
        }
    }
}