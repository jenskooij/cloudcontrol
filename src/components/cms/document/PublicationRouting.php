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
    const GET_PARAMETER_SCHEDULED = 'scheduled';

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
        $publicationDate = $this->getPublicationDate();
        $cmsComponent->storage->getDocuments()->publishDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG],
            $publicationDate);
        $scheduled = $publicationDate > time();
        $this->logPublicationActivity($request, $cmsComponent, $scheduled);
        $this->doAfterPublishRedirect($request, $cmsComponent, $scheduled ? self::GET_PARAMETER_SCHEDULED : self::GET_PARAMETER_PUBLISHED);
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
    ) {
        Cache::getInstance()->clearCache();
        $path = $request::$get[CmsConstants::GET_PARAMETER_SLUG];
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add($activity . ' document <a href="' . $docLink . '">' . $request::$get[CmsConstants::GET_PARAMETER_SLUG] . '</a>',
            $icon);
    }

    /**
     * @return int
     */
    private function getPublicationDate()
    {
        if (isset($_GET['publicationDate'])) {
            return intval($_GET['publicationDate']);
        }

        if (isset($_GET['date'], $_GET['time'])) {
            $time = strtotime($_GET['date'] . ' ' . $_GET['time']);
            return $time === false ? time() : $time;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @param bool $scheduled
     */
    private function logPublicationActivity($request, $cmsComponent, $scheduled)
    {
        if ($scheduled) {
            $this->clearCacheAndLogActivity($request, $cmsComponent, 'clock-o', 'scheduled publication for');
        } else {
            $this->clearCacheAndLogActivity($request, $cmsComponent);
        }
    }
}