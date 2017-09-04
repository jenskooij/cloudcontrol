<?php
/**
 * Created by jensk on 4-9-2017.
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;

class ValuelistRouting implements CmsRouting
{

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        if ($relativeCmsUri == '/valuelists') {
            $this->valuelistsRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/valuelists/new') {
            $this->newValuelistRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/valuelists/edit' && isset($request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
            $this->editValuelistRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/valuelists/delete' && isset($request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
            $this->deleteValuelistRoute($request, $cmsComponent);
        }
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    private function valuelistsRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'valuelists';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_VALUELISTS, $cmsComponent->storage->getValuelists()->getValuelists());
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_VALUELISTS);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function newValuelistRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'valuelists/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_VALUELISTS);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE])) {
            $slug = $cmsComponent->storage->getValuelists()->addValuelist($request::$post);
            $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/valuelists/edit?slug=' . $slug;
            $cmsComponent->storage->getActivityLog()->add('created valuelist <a href="' . $docLink . '">' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . '</a>', 'plus');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/valuelists');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function editValuelistRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'valuelists/form';
        $folder = $cmsComponent->storage->getValuelists()->getValuelistBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);

        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
            $cmsComponent->storage->getValuelists()->saveValuelist($request::$get[CmsConstants::GET_PARAMETER_SLUG], $request::$post);
            $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/valuelists/edit?slug=' . $request::$get[CmsConstants::GET_PARAMETER_SLUG];
            $cmsComponent->storage->getActivityLog()->add('edited valuelist <a href="' . $docLink . '">' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . '</a>', 'pencil');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/valuelists');
            exit;
        }

        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_VALUELISTS);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_VALUELIST, $folder);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteValuelistRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getValuelists()->deleteValuelistBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        $cmsComponent->storage->getActivityLog()->add('deleted valuelist ' . $request::$get[CmsConstants::GET_PARAMETER_SLUG], 'trash');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/valuelists');
        exit;
    }
}