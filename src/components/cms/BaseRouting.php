<?php
/**
 * User: Jens
 * Date: 4-9-2017
 * Time: 20:18
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\search\Search;

class BaseRouting implements CmsRouting
{
    protected $userRights;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var string
     */
    protected $relativeCmsUri;
    /**
     * @var CmsComponent
     */
    protected $cmsComponent;

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        $this->request = $request;
        $this->relativeCmsUri = $relativeCmsUri;
        $this->cmsComponent = $cmsComponent;
    }

    /**
     * @param $userRights
     */
    public function setUserRights($userRights)
    {
        $this->userRights = $userRights;
    }

    /**
     * Call the different routing methods
     * @throws \Exception
     */
    public function route()
    {
        $this->dashboardRouting($this->relativeCmsUri);
        $this->logOffRouting($this->request, $this->relativeCmsUri);
        $this->apiRouting($this->relativeCmsUri);
        $this->documentRouting($this->userRights, $this->relativeCmsUri);
        $this->valuelistsRouting($this->userRights, $this->relativeCmsUri);
        $this->sitemapRouting($this->userRights, $this->relativeCmsUri);
        $this->redirectRouting($this->userRights, $this->relativeCmsUri);
        $this->imageRouting($this->userRights, $this->relativeCmsUri);
        $this->filesRouting($this->userRights, $this->relativeCmsUri);
        $this->configurationRouting($this->userRights, $this->relativeCmsUri);
        $this->searchRouting($this->relativeCmsUri);
    }

    /**
     * @param string $relativeCmsUri
     * @throws \Exception
     */
    protected function dashboardRouting($relativeCmsUri)
    {
        if ($relativeCmsUri === '' || $relativeCmsUri === '/') {
            $this->cmsComponent->subTemplate = 'dashboard';
            $this->cmsComponent->setParameter('activityLog',
                $this->cmsComponent->storage->getActivityLog()->getActivityLog());
            $documentCount = $this->cmsComponent->storage->getDocuments()->getTotalDocumentCount();
            $indexer = new Search($this->cmsComponent->storage);
            $indexedDocuments = $indexer->getIndexedDocuments();
            $this->cmsComponent->setParameter(CmsConstants::PARAMETER_SEARCH_NEEDS_UPDATE,
                $documentCount !== $indexedDocuments);
        }
    }

    /**
     * @param Request $request
     * @param string $relativeCmsUri
     */
    protected function logOffRouting($request, $relativeCmsUri)
    {
        if ($relativeCmsUri === '/log-off') {
            $this->cmsComponent->storage->getActivityLog()->add('logged off', 'user');
            $_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL] = null;
            unset($_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]);
            header('Location: ' . $request::$subfolders . $this->cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX));
            exit;
        }
    }

    /**
     * @param string $relativeCmsUri
     * @throws \Exception
     */
    protected function apiRouting($relativeCmsUri)
    {
        $this->imagesApiRouting($relativeCmsUri);
        $this->filesApiRouting($relativeCmsUri);
        $this->documentsApiRouting($relativeCmsUri);
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     * @throws \Exception
     */
    protected function documentRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_DOCUMENTS, $userRights, true)) {
            new DocumentRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     */
    protected function valuelistsRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_VALUELISTS, $userRights, true)) {
            new ValuelistRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     */
    protected function sitemapRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_SITEMAP, $userRights, true)) {
            new SitemapRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     */
    protected function redirectRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_SITEMAP, $userRights, true)) {
            new RedirectRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     */
    protected function imageRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_IMAGES, $userRights, true)) {
            new ImagesRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     */
    protected function filesRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_FILES, $userRights, true)) {
            new FilesRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param string $relativeCmsUri
     */
    protected function configurationRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_CONFIGURATION, $userRights, true)) {
            new ConfigurationRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param string $relativeCmsUri
     * @throws \Exception
     */
    protected function searchRouting($relativeCmsUri)
    {
        new SearchRouting($this->request, $relativeCmsUri, $this->cmsComponent);
    }

    /**
     * @param $relativeCmsUri
     */
    protected function imagesApiRouting($relativeCmsUri)
    {
        if ($relativeCmsUri === '/images.json') {
            ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
            ResponseHeaders::sendAllHeaders();
            die(json_encode($this->cmsComponent->storage->getImages()->getImages()));
        }
    }

    /**
     * @param $relativeCmsUri
     */
    protected function filesApiRouting($relativeCmsUri)
    {
        if ($relativeCmsUri === '/files.json') {
            ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
            ResponseHeaders::sendAllHeaders();
            die(json_encode($this->cmsComponent->storage->getFiles()->getFiles()));
        }
    }

    /**
     * @param $relativeCmsUri
     */
    protected function documentsApiRouting($relativeCmsUri)
    {
        if ($relativeCmsUri === '/documents.json') {
            ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
            ResponseHeaders::sendAllHeaders();
            die(json_encode($this->cmsComponent->storage->getDocuments()->getDocuments()));
        }
    }
}