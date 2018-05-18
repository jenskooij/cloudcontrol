<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:22
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\search\Indexer;
use CloudControl\Cms\search\SearchAnalyzer;

class SearchRouting extends CmsRouting
{
    protected static $routes = array(
        '/search' => 'overviewRoute',
        '/search/update-index' => 'updateIndexRoute',
        '/search/ajax-update-index' => 'ajaxUpdateIndexRoute',
        '/search/manual-update-index' => 'manualUpdateRoute',
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
     * @param \CloudControl\Cms\components\CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function overviewRoute(/** @scrutinizer ignore-unused */ $request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'search';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_SEARCH);
        $this->setSearchNeedsUpdateParameter($cmsComponent);

        $searchAnalyzer = new SearchAnalyzer($cmsComponent->storage);
        $searchAnalysis = $searchAnalyzer->getSearchAnalysis();
        $cmsComponent->setParameter('searchAnalysis', $searchAnalysis);
    }

    /**
     * @param \CloudControl\Cms\components\CmsComponent $cmsComponent
     * @param Request $request
     */
    protected function updateIndexRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'search/update-index';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_SEARCH);
        if (isset($_GET['returnUrl'])) {
            $returnUrl = $_GET['returnUrl'];
        } else {
            $returnUrl = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/search';
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_RETURN_URL, $returnUrl);
    }

    protected function ajaxUpdateIndexRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'search/update-index';
        if (isset($request::$get['step'])) {
            \set_time_limit(0); // Set max excecution time infinite
            \session_write_close(); // Close the session, so it doesnt create a lock on the sessionstorage, block other requests.
            $indexer = new Indexer($cmsComponent->storage);
            $step = $request::$get['step'];
            $this->stepRouting($step, $cmsComponent, $indexer);
        } else {
            $this->showJson('No step defined.', 500);
        }
    }

    protected function manualUpdateRoute(/** @scrutinizer ignore-unused */ $request, $cmsComponent)
    {
        $indexer = new Indexer($cmsComponent->storage);
        $indexer->updateIndex();
    }

    /**
     * @param $obj
     * @param int $httpHeader
     */
    private function showJson($obj, $httpHeader = 200)
    {
        http_response_code($httpHeader);
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
        ResponseHeaders::sendAllHeaders();
        die(json_encode($obj));
    }

    /**
     * @param CmsComponent $cmsComponent
     * @param string $step
     * @param Indexer $indexer
     * @throws \Exception
     */
    private function stepRouting($step, $cmsComponent, $indexer)
    {
        switch ($step) {
            case 'resetIndex': $indexer->resetIndex(); break;
            case 'cleanPublishedDeletedDocuments': $cmsComponent->storage->getDocuments()->cleanPublishedDeletedDocuments(); break;
            case 'createDocumentTermCount':
                $documents = $cmsComponent->storage->getDocuments()->getPublishedDocumentsNoFolders();
                $indexer->createDocumentTermCount($documents);
                break;
            case 'createDocumentTermFrequency': $indexer->createDocumentTermFrequency(); break;
            case 'createTermFieldLengthNorm': $indexer->createTermFieldLengthNorm(); break;
            case 'createInverseDocumentFrequency': $indexer->createInverseDocumentFrequency(); break;
            case 'replaceOldIndex':
                $indexer->replaceOldIndex();
                $cmsComponent->storage->getActivityLog()->add('updated search index', 'search');
                break;
            default : $this->showJson('Invalid step: ' . $step . '.', 500); break;
        }
        $this->showJson('done');
    }
}