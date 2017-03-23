<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:22
 */

namespace library\components\cms;


use library\components\CmsComponent;
use library\search\Indexer;
use library\search\Search;

class SearchRouting implements CmsRouting
{

	/**
	 * SearchRouting constructor.
	 *
	 * @param \library\cc\Request              $request
	 * @param                                  $relativeCmsUri
	 * @param \library\components\CmsComponent $cmsComponent
	 */
	public function __construct($request, $relativeCmsUri, $cmsComponent)
	{
		switch ($relativeCmsUri) {
			case '/search': $this->overviewRoute($cmsComponent); break;
			case '/search/update-index' : $this->updateIndexRoute($cmsComponent); break;
			case '/search/ajax-update-index': $this->ajaxUpdateIndexRoute($request, $cmsComponent); break;
			case '/search/manual-update-index' :
				$indexer = new Indexer($cmsComponent->storage);
				$indexer->updateIndex();
				break;
		}
	}

	/**
	 * @param \library\components\CmsComponent $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/search';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SEARCH);
		$documentCount = $cmsComponent->storage->getDocuments()->getTotalDocumentCount();
		$indexer = new Search($cmsComponent->storage);
		$indexedDocuments = $indexer->getIndexedDocuments();
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SEARCH_NEEDS_UPDATE, $documentCount !== $indexedDocuments);
	}

	/**
	 * @param \library\components\CmsComponent $cmsComponent
	 */
	private function updateIndexRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/search/update-index';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SEARCH);
	}

	private function ajaxUpdateIndexRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/search/update-index';
		if (isset($request::$get['step'])) {
			\set_time_limit(0); // Set max excecution time infinite
			\session_write_close(); // Close the session, so it doesnt create a lock on the sessionstorage, block other requests.
			$indexer = new Indexer($cmsComponent->storage);
			$step = $request::$get['step'];
			$this->stepRouting($step, $cmsComponent, $indexer);
		} else {
			$this->showJson('No step defined.', 'HTTP/1.0 500 Internal Server Error');
		}
	}

	private function showJson($obj, $httpHeader = 'HTTP/1.0 200 OK') {
		header($_SERVER['SERVER_PROTOCOL'] . $httpHeader, true);
		header('Content-type: application/json');
		die(json_encode($obj));
	}

	/**
	 * @param CmsComponent $cmsComponent
	 * @param string $step
	 * @param Indexer $indexer
	 */
	private function stepRouting($step, $cmsComponent, $indexer)
	{
		switch($step) {
			case 'resetIndex': $indexer->resetIndex(); break;
			case 'cleanPublishedDeletedDocuments': $cmsComponent->storage->getDocuments()->cleanPublishedDeletedDocuments(); break;git s
			case 'createDocumentTermCount':
				$documents = $cmsComponent->storage->getDocuments()->getPublishedDocumentsNoFolders();
				$indexer->createDocumentTermCount($documents);
				break;
			case 'createDocumentTermFrequency': $indexer->createDocumentTermFrequency(); break;
			case 'createTermFieldLengthNorm': $indexer->createTermFieldLengthNorm(); break;
			case 'createInverseDocumentFrequency': $indexer->createInverseDocumentFrequency(); break;
			case 'replaceOldIndex': $indexer->replaceOldIndex(); break;
			default : $this->showJson('Invalid step: ' . $step . '.', 'HTTP/1.0 500 Internal Server Error'); break;
		}
		$this->showJson('done');
	}
}