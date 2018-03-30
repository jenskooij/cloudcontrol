<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:22
 */

namespace CloudControl\Cms\components\cms\configuration;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;

class DocumentTypeRouting extends CmsRouting
{
    protected static $routes = array(
        '/configuration/document-types' => 'overviewRoute',
        '/configuration/document-types/new' => 'newRoute',
        '/configuration/document-types/edit' => 'editRoute',
        '/configuration/document-types/delete' => 'deleteRoute',
    );

    /**
     * DocumentTypeRouting constructor.
     *
     * @param Request $request
     * @param String $relativeCmsUri
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
        $cmsComponent->subTemplate = 'configuration/document-types';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPES,
            $cmsComponent->storage->getDocumentTypes()->getDocumentTypes());
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/document-types-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $bricks = $cmsComponent->storage->getBricks()->getBricks();
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getDocumentTypes()->addDocumentType($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
            exit;
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_BRICKS, $bricks);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/document-types-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $documentType = $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG],
            false);
        $bricks = $cmsComponent->storage->getBricks()->getBricks();
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $cmsComponent->storage->getDocumentTypes()->saveDocumentType($request::$get[CmsConstants::GET_PARAMETER_SLUG],
                $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
            exit;
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPE, $documentType);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_BRICKS, $bricks);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocumentTypes()->deleteDocumentTypeBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
        exit;
    }
}