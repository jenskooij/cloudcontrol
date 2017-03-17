<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 17:05
 */

namespace library\search;


use library\storage\Storage;

/**
 * Abstract Class SearchDbConnected
 * Handles connection with the search index database
 * @package library\search
 */
abstract class SearchDbConnected
{
	/**
	 * @var \PDO
	 */
	protected $searchDbHandle;

	/**
	 * @var \library\storage\Storage
	 */
	protected $storage;
	/**
	 * @var string
	 */
	protected $storageDir;

	/**
	 * Indexer constructor.
	 *
	 * @param \library\storage\Storage $storage
	 */
	public function __construct(Storage $storage)
	{
		$this->storageDir = $storage->getStorageDir();
		$this->storage = $storage;
		$this->initializeDb();
	}

	protected function initializeDb()
	{
		if (!$this->isDatabaseConfigured()) {
			$this->configureDatabase();
		}
	}

	protected function configureDatabase()
	{
		$db = $this->getSearchDbHandle();
		$sqlPath = __DIR__ . DIRECTORY_SEPARATOR . '../cc/install/search.sql';
		$searchSql = file_get_contents($sqlPath);
		$db->exec($searchSql);
	}

	protected function isDatabaseConfigured()
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
}