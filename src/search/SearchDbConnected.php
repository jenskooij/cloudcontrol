<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 17:05
 */

namespace CloudControl\Cms\search;


use CloudControl\Cms\storage\Storage;

/**
 * Abstract Class SearchDbConnected
 * Handles connection with the search index database
 * @package CloudControl\Cms\search
 */
abstract class SearchDbConnected
{
    /**
     * @var \PDO
     */
    protected $searchDbHandle;

    /**
     * @var \CloudControl\Cms\storage\Storage
     */
    protected $storage;
    /**
     * @var string
     */
    protected $storageDir;

    /**
     * Indexer constructor.
     *
     * @param \CloudControl\Cms\storage\Storage $storage
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
            $path = $this->storageDir . DIRECTORY_SEPARATOR;
            $this->searchDbHandle = new \PDO('sqlite:' . $path . 'search.db');
        }
        return $this->searchDbHandle;
    }
}