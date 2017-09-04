<?php
/**
 * User: Jens
 * Date: 4-9-2017
 * Time: 20:18
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
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
     * @param $relativeCmsUri
     */
    protected function dashboardRouting($relativeCmsUri)
    {
        if ($relativeCmsUri == '' || $relativeCmsUri == '/') {
            $this->cmsComponent->subTemplate = 'dashboard';
            $this->cmsComponent->setParameter('activityLog', $this->cmsComponent->storage->getActivityLog()->getActivityLog());
            $documentCount = $this->cmsComponent->storage->getDocuments()->getTotalDocumentCount();
            $indexer = new Search($this->cmsComponent->storage);
            $indexedDocuments = $indexer->getIndexedDocuments();
            $this->cmsComponent->setParameter(CmsConstants::PARAMETER_SEARCH_NEEDS_UPDATE, $documentCount !== $indexedDocuments);
        }
    }

    /**
     * @param $request
     * @param $relativeCmsUri
     */
    protected function logOffRouting($request, $relativeCmsUri)
    {
        if ($relativeCmsUri == '/log-off') {
            $this->cmsComponent->storage->getActivityLog()->add('logged off', 'user');
            $_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL] = null;
            unset($_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]);
            header('Location: ' . $request::$subfolders . $this->cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX));
            exit;
        }
    }

    /**
     * @param $relativeCmsUri
     */
    protected function apiRouting($relativeCmsUri)
    {
        if ($relativeCmsUri == '/images.json') {
            header(CmsConstants::CONTENT_TYPE_APPLICATION_JSON);
            die(json_encode($this->cmsComponent->storage->getImages()->getImages()));
        } elseif ($relativeCmsUri == '/files.json') {
            header(CmsConstants::CONTENT_TYPE_APPLICATION_JSON);
            die(json_encode($this->cmsComponent->storage->getFiles()->getFiles()));
        } elseif ($relativeCmsUri == '/documents.json') {
            header(CmsConstants::CONTENT_TYPE_APPLICATION_JSON);
            die(json_encode($this->cmsComponent->storage->getDocuments()->getDocuments()));
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function documentRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_DOCUMENTS, $userRights)) {
            new DocumentRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function valuelistsRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_VALUELISTS, $userRights)) {
            new ValuelistRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function sitemapRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_SITEMAP, $userRights)) {
            new SitemapRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function redirectRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_SITEMAP, $userRights)) {
            new RedirectRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function imageRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_IMAGES, $userRights)) {
            new ImagesRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function filesRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_FILES, $userRights)) {
            new FilesRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $userRights
     * @param $relativeCmsUri
     */
    protected function configurationRouting($userRights, $relativeCmsUri)
    {
        if (in_array(CmsConstants::PARAMETER_CONFIGURATION, $userRights)) {
            new ConfigurationRouting($this->request, $relativeCmsUri, $this->cmsComponent);
        }
    }

    /**
     * @param $relativeCmsUri
     */
    protected function searchRouting($relativeCmsUri)
    {
        new SearchRouting($this->request, $relativeCmsUri, $this->cmsComponent);
    }
}