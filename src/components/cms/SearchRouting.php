<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:22
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\search\Indexer;
use CloudControl\Cms\search\Search;

class SearchRouting implements CmsRouting
{

    /**
     * SearchRouting constructor.
     *
     * @param \CloudControl\Cms\cc\Request $request
     * @param                                  $relativeCmsUri
     * @param \CloudControl\Cms\components\CmsComponent $cmsComponent
     * @throws \Exception
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        switch ($relativeCmsUri) {
            case '/search': $this->overviewRoute($cmsComponent); break;
            case '/search/update-index' : $this->updateIndexRoute($cmsComponent, $request); break;
            case '/search/ajax-update-index': $this->ajaxUpdateIndexRoute($request, $cmsComponent); break;
            case '/search/manual-update-index' : $indexer = new Indexer($cmsComponent->storage); $indexer->updateIndex(); break;
        }
    }

    /**
     * @param \CloudControl\Cms\components\CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function overviewRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'search';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_SEARCH);
        $documentCount = $cmsComponent->storage->getDocuments()->getTotalDocumentCount();
        $indexer = new Search($cmsComponent->storage);
        $indexedDocuments = $indexer->getIndexedDocuments();
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SEARCH_NEEDS_UPDATE, $documentCount !== $indexedDocuments);
    }

    /**
     * @param \CloudControl\Cms\components\CmsComponent $cmsComponent
     * @param Request $request
     */
    private function updateIndexRoute($cmsComponent, $request)
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

    private function ajaxUpdateIndexRoute($request, $cmsComponent)
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

    /**
     * @param $obj
     * @param int $httpHeader
     */
    private function showJson($obj, $httpHeader = 200)
    {
        http_response_code($httpHeader);
        header('Content-type: application/json');
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