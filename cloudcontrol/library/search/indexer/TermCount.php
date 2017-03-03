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
				 VALUES ';
		$values = array();
		$quotedDocumentPath = $db->quote($document->path);
		foreach ($documentTermCount as $field => $countArray) {
			$quotedField = $db->quote($field);
			foreach ($countArray as $term => $count) {
				$values[] = $quotedDocumentPath . ', ' . $db->quote($term) . ', ' . $db->quote($count) . ', ' . $quotedField;
			}
		}
		$sql .= '(' . implode('),' . PHP_EOL . '(', $values) . ');';

		$stmt = $db->prepare($sql);
		if (!$stmt->execute()) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
	}
}