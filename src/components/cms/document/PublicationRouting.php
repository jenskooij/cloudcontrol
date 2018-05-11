<?php
/**
 * Created by jensk on 11-5-2018.
 */

namespace CloudControl\Cms\components\cms\document;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\storage\Cache;

class PublicationRouting extends CmsRouting
{
    protected static $routes = array(
        '/documents/publish-document' => 'publishDocumentRoute',
        '/documents/unpublish-document' => 'unpublishDocumentRoute',
    );

    const GET_PARAMETER_PUBLISHED = 'published';
    const GET_PARAMETER_UNPUBLISHED = 'unpublished';

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
     * @throws \Exception
     */
    protected function publishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->publishDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        $this->clearCacheAndLogActivity($request, $cmsComponent);
        $this->doAfterPublishRedirect($request, $cmsComponent);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function unpublishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->unpublishDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        $this->clearCacheAndLogActivity($request, $cmsComponent, 'times-circle-o', self::GET_PARAMETER_UNPUBLISHED);
        $this->doAfterPublishRedirect($request, $cmsComponent, self::GET_PARAMETER_UNPUBLISHED);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @param string $param
     */
    private function doAfterPublishRedirect($request, $cmsComponent, $param = 'published')
    {
        if ($cmsComponent->autoUpdateSearchIndex) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/search/update-index?returnUrl=' . urlencode($request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?' . $param . '=' . urlencode($request::$get[CmsConstants::GET_PARAMETER_SLUG]) . '&path=' . $this->getReturnPath($request)));
        } else {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?' . $param . '=' . urlencode($request::$get[CmsConstants::GET_PARAMETER_SLUG]) . '&path=' . $this->getReturnPath($request));
        }
        exit;
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @param string $icon
     * @param string $activity
     */
    private function clearCacheAndLogActivity(
        $request,
        $cmsComponent,
        $icon = 'check-circle-o',
        $activity = 'published'
    )
    {
        Cache::getInstance()->clearCache();
        $path = $request::$get[CmsConstants::GET_PARAMETER_SLUG];
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add($activity . ' document <a href="' . $docLink . '">' . $request::$get[CmsConstants::GET_PARAMETER_SLUG] . '</a>',
            $icon);
    }
}