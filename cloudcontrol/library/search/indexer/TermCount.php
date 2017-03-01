<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 10:22
 */

namespace library\search\indexer;


use library\search\DocumentTokenizer;

class TermCount
{
	protected $dbHandle;
	protected $documents;
	protected $filters;

	/**
	 * TermCount constructor.
	 *
	 * @param resource $dbHandle
	 * @param array    $documents
	 * @param array    $filters
	 */
	public function __construct($dbHandle, $documents, $filters)
	{
		$this->dbHandle = $dbHandle;
		$this->documents = $documents;
		$this->filters = $filters;
	}

	public function execute()
	{
		foreach ($this->documents as $document) {
			$tokenizer = new DocumentTokenizer($document);
			$tokens = $tokenizer->getTokens();
			$documentTermCount = $this->applyFilters($tokens);
			$this->storeDocumentTermCount($document, $documentTermCount);
		}
	}

	protected function applyFilters($tokens)
	{
		foreach ($this->filters as $filterName) {
			$filterClassName = '\library\search\filters\\' . $filterName;
			$filter = new $filterClassName($tokens);
			$tokens = $filter->getFilterResults();
		}
		return $tokens;
	}

	protected function storeDocumentTermCount($document, $documentTermCount)
	{
		$db = $this->dbHandle;
		$sql = '
			INSERT INTO `term_count` (`documentPath`, `term`, `count`, `field`)
				 VALUES (:documentPath, :term, :count, :field);
		';
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':documentPath', $document->path);
		foreach ($documentTermCount as $field => $fieldTermCount) {
			$stmt->bindValue(':field', $field);
			foreach ($fieldTermCount as $term => $count) {
				$stmt->bindValue(':term', $term);
				$stmt->bindValue(':count', $count);
				if (!$stmt->execute()) {
					$errorInfo = $db->errorInfo();
					$errorMsg = $errorInfo[2];
					throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
				}
			}
		}
	}
}