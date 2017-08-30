<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:37
 */

namespace CloudControl\Cms\components\cms\configuration;

use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;

class ApplicationComponentRouting implements CmsRouting
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
        if ($relativeCmsUri == '/configuration/application-components') {
            $this->overviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/application-components/new') {
            $this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/application-components/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->editRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/application-components/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->deleteRoute($request, $cmsComponent);
        }
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    private function overviewRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/application-components';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_APPLICATION_COMPONENTS, $cmsComponent->storage->getApplicationComponents()->getApplicationComponents());
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/application-components-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getApplicationComponents()->addApplicationComponent($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/application-components');
            exit;
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/application-components-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        $applicationComponent = $cmsComponent->storage->getApplicationComponents()->getApplicationComponentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getApplicationComponents()->saveApplicationComponent($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/application-components');
            exit;
        }

        $cmsComponent->setParameter(CmsComponent::PARAMETER_APPLICATION_COMPONENT, $applicationComponent);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getApplicationComponents()->deleteApplicationComponentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/application-components');
        exit;
    }
}