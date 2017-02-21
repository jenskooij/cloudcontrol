<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:29
 */

namespace library\search;


use library\storage\JsonStorage;

class Indexer extends SearchDbConnected
{
	/**
	 * @var \library\storage\JsonStorage
	 */
	protected $storage;

	protected $filters = array(
		'DutchStopWords',
		'EnglishStopWords'
	);
	protected $storageDir;

	public function updateIndex()
	{
		$this->createDocumentTermCount();
		dump('Continue here: https://en.wikipedia.org/wiki/Tf%E2%80%93idf#Example_of_tf.E2.80.93idf', $this->getSearchDbHandle()->errorInfo());
	}

	private function applyFilters($tokens)
	{
		foreach ($this->filters as $filterName) {
			$filterClassName = '\library\search\filters\\' . $filterName;
			$filter = new $filterClassName($tokens);
			$tokens = $filter->getFilterResults();
		}
		return $tokens;
	}

	private function storeDocumentTermCount($document, $documentTermCount)
	{
		$db = $this->getSearchDbHandle();
		$sql = '
			INSERT INTO `term_count` (`documentPath`, `term`, `count`)
				 VALUES (:documentPath, :term, :count);
		';
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':documentPath', $document->path);
		foreach ($documentTermCount as $term => $count) {
			$stmt->bindValue(':term', $term);
			$stmt->bindValue(':count', $count);
			if (!$stmt->execute()) {
				$errorInfo = $db->errorInfo();
				$errorMsg = $errorInfo[2];
				throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
			}
		}
	}

	private function createDocumentTermCount()
	{
		$documents = $this->storage->getDocuments();
		foreach ($documents as $document) {
			$tokenizer = new DocumentTokenizer($document);
			$tokens = $tokenizer->getTokens();
			$documentTermCount = $this->applyFilters($tokens);
			$this->storeDocumentTermCount($document, $documentTermCount);
		}
	}
}