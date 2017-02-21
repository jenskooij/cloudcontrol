<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:29
 */

namespace library\search;


use library\storage\JsonStorage;

class Indexer
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
	/**
	 * @var resource
	 */
	protected $searchDbHandle;

	/**
	 * Indexer constructor.
	 *
	 * @param \library\storage\JsonStorage $storage
	 */
	public function __construct(JsonStorage $storage)
	{
		$this->storageDir = $storage->getStorageDir();
		$this->storage = $storage;
		$this->initializeDb();
	}

	private function initializeDb()
	{
		if (!$this->isDatabaseConfigured()) {
			$this->configureDatabase();
		}
	}

	private function configureDatabase()
	{
		$db = $this->getSearchDbHandle();
		$sqlPath = __DIR__ . DIRECTORY_SEPARATOR . '../cc/install/search.sql';
		$searchSql = file_get_contents($sqlPath);
		$db->exec($searchSql);
	}

	private function isDatabaseConfigured()
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->query('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'term_count\';');
		$result = $stmt->fetchAll();
		return !empty($result);
	}

	protected function getSearchDbHandle()
	{
		if ($this->searchDbHandle === null) {
			$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR;
			$this->searchDbHandle = new \PDO('sqlite:' . $path . 'search.db');
		}
		return $this->searchDbHandle;
	}

	public function updateIndex()
	{
		$this->createDocumentTermCount();
		dump($this->getSearchDbHandle()->errorInfo());
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
			$tokenizer = new Tokenizer($document);
			$tokens = $tokenizer->getTokens();
			$documentTermCount = $this->applyFilters($tokens);
			$this->storeDocumentTermCount($document, $documentTermCount);
		}
	}
}