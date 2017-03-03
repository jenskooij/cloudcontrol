<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:22
 */

namespace library\components\cms;


use library\components\CmsComponent;
use library\search\Indexer;

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
		if ($relativeCmsUri === '/search') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri === '/search/update-index') {
			$this->updateIndexRoute($cmsComponent);
		}
	}

	/**
	 * @param \library\components\CmsComponent $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/search';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SEARCH);
		$documentCount = $cmsComponent->storage->getTotalDocumentCount();
		$indexer = new Indexer($cmsComponent->storage);
		$indexedDocuments = $indexer->getIndexedDocuments();
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SEARCH_NEEDS_UPDATE, $documentCount !== $indexedDocuments);
	}

	/**
	 * @param \library\components\CmsComponent $cmsComponent
	 */
	private function updateIndexRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/search/update-index';
		$indexer = new Indexer($cmsComponent->storage);
		$log = $indexer->updateIndex();
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SEARCH);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SEARCH_LOG, $log);
	}
}