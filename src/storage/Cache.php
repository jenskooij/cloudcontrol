<?php
/**
 * Created by jensk on 25-10-2017.
 */

namespace CloudControl\Cms\storage;


class Cache
{
    /**
     * @var Cache
     */
    private static $instance;

    /**
     * @var string
     */
    protected $storagePath;
    /**
     * @var \PDO
     */
    protected $dbHandle;

    private function __construct()
    {
        // Singleton, so private constructor
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof Cache) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    public function getCacheForPath($path)
    {
        $dbInstace = $this->getDbInstance();
        $sql = '
            SELECT *
              FROM `cache`
             WHERE `path` = :path
             LIMIT 1
        ';
        $stmt = $dbInstace->prepare($sql);
        $stmt->bindParam(':path', $path);
        if ($stmt->execute()) {
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } else {
            $error = $stmt->errorInfo();
            $errorMsg = $error[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
    }

    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    private function getDbInstance()
    {
        if ($this->dbHandle === null) {
            $this->dbHandle = new \PDO('sqlite:' . $this->storagePath . DIRECTORY_SEPARATOR . 'cache.db');
        }
        return $this->dbHandle;
    }

    public function init($baseCacheSqlPath)
    {
        $realBaseCacheSqlPath = realpath($baseCacheSqlPath);

        $db = $this->getDbInstance();
        $sql = file_get_contents($realBaseCacheSqlPath);
        $db->exec($sql);
    }

    public function setCacheForPath($requestUri, $renderedContent)
    {
        $dbInstace = $this->getDbInstance();
        $sql = '
            INSERT OR REPLACE INTO `cache` (path, creationStamp, contents)
                 VALUES (:path, :creationStamp, :contents);
        ';
        $contents = \sanitize_output($renderedContent);
        $creationStamp = time();
        $stmt = $dbInstace->prepare($sql);
        $stmt->bindParam(':path', $requestUri);
        $stmt->bindParam(':creationStamp', $creationStamp);
        $stmt->bindParam(':contents', $contents);
        if ($stmt->execute()) {
            return;
        } else {
            $error = $stmt->errorInfo();
            $errorMsg = $error[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
    }

}