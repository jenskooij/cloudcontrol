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

    /**
     * @return Cache
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof Cache) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    /**
     * @param $path
     * @return \stdClass
     * @throws \RuntimeException
     */
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

    /**
     * Clears all cache
     */
    public function clearCache()
    {
        $dbInstace = $this->getDbInstance();
        $sql = '
            DELETE FROM `cache`;
            VACUUM;
        ';
        $stmt = $dbInstace->prepare($sql);
        if ($stmt->execute()) {
            return;
        } else {
            $error = $stmt->errorInfo();
            $errorMsg = $error[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
    }

    /**
     * @param $storagePath
     */
    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * @return \PDO
     */
    private function getDbInstance()
    {
        if ($this->dbHandle === null) {
            $this->dbHandle = new \PDO('sqlite:' . $this->storagePath . DIRECTORY_SEPARATOR . 'cache.db');
        }
        return $this->dbHandle;
    }

    /**
     * @param $baseCacheSqlPath
     */
    public function init($baseCacheSqlPath)
    {
        $realBaseCacheSqlPath = realpath($baseCacheSqlPath);

        $db = $this->getDbInstance();
        $sql = file_get_contents($realBaseCacheSqlPath);
        $db->exec($sql);
    }

    /**
     * @param string $requestUri
     * @param string $renderedContent
     * @param string $headers
     * @throws \RuntimeException
     */
    public function setCacheForPath($requestUri, $renderedContent, $headers)
    {
        $dbInstace = $this->getDbInstance();
        $sql = '
            INSERT OR REPLACE INTO `cache` (path, creationStamp, headers, contents)
                 VALUES (:path, :creationStamp, :headers, :contents);
        ';
        $contents = \sanitize_output($renderedContent);
        $creationStamp = time();
        $stmt = $dbInstace->prepare($sql);
        $stmt->bindParam(':path', $requestUri);
        $stmt->bindParam(':creationStamp', $creationStamp);
        $stmt->bindParam(':contents', $contents);
        $stmt->bindParam(':headers', $headers);
        if ($stmt->execute()) {
            return;
        } else {
            $error = $stmt->errorInfo();
            $errorMsg = $error[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
    }

}