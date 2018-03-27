<?php
/**
 * User: Jens
 * Date: 30-1-2017
 * Time: 20:15
 */

namespace CloudControl\Cms\storage;

use CloudControl\Cms\storage\repository\ContentRepository;

/**
 * Class Repository
 * @package CloudControl\Cms\storage
 * @property array sitemap
 * @property array applicationComponents
 * @property array documentTypes
 * @property array bricks
 * @property array imageSet
 * @property array images
 * @property array files
 * @property array users
 * @property array valuelists
 * @property array redirects
 * @property array activityLog
 */
class Repository
{
    protected $storagePath;

    protected $fileBasedSubsets = array(
        'sitemap',
        'applicationComponents',
        'documentTypes',
        'bricks',
        'imageSet',
        'images',
        'files',
        'users',
        'valuelists',
        'redirects',
        'activityLog'
    );

    protected $sitemap;
    protected $sitemapChanges = false;

    protected $applicationComponents;
    protected $applicationComponentsChanges = false;

    protected $documentTypes;
    protected $documentTypesChanges = false;

    protected $bricks;
    protected $bricksChanges = false;

    protected $imageSet;
    protected $imageSetChanges = false;

    protected $images;
    protected $imagesChanges = false;

    protected $files;
    protected $filesChanges = false;

    protected $users;
    protected $usersChanges = false;

    protected $valuelists;
    protected $valuelistsChanges = false;

    protected $redirects;
    protected $redirectsChanges = false;

    protected $activityLog;
    protected $activityLogChanges = false;

    protected $contentDbHandle;

    /**
     * @var ContentRepository
     */
    protected $contentRepository;


    /**
     * Repository constructor.
     * @param $storagePath
     * @throws \Exception
     */
    public function __construct($storagePath)
    {
        $storagePath = realpath($storagePath);
        if (is_dir($storagePath) && $storagePath !== false) {
            $this->storagePath = $storagePath;
            $this->contentRepository = new ContentRepository($storagePath);
        } else {
            throw new \Exception('Repository not yet initialized.');
        }
    }

    /**
     * Initiates default storage
     * @param $baseStorageDefaultPath
     * @param $baseStorageSqlPath
     */
    public function init($baseStorageDefaultPath, $baseStorageSqlPath)
    {
        $storageDefaultPath = realpath($baseStorageDefaultPath);
        $contentSqlPath = realpath($baseStorageSqlPath);

        $this->initConfigStorage($storageDefaultPath);
        $this->initContentDb($contentSqlPath);

        $this->save();
    }

    /**
     * Load filebased subset and return it's contents
     *
     * @param $name
     * @return mixed|string
     * @throws \Exception
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            if (in_array($name, $this->fileBasedSubsets)) {
                return $this->$name;
            } else {
                throw new \Exception('Trying to get undefined property from Repository: ' . $name);
            }
        } else {
            if (in_array($name, $this->fileBasedSubsets)) {
                return $this->loadSubset($name);
            } else {
                throw new \Exception('Trying to get undefined property from Repository: ' . $name);
            }
        }
    }

    /**
     * Set filebased subset contents
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->fileBasedSubsets)) {
            $this->$name = $value;
            $changes = $name . 'Changes';
            $this->$changes = true;
        } else {
            throw new \Exception('Trying to persist unknown subset in repository: ' . $name . ' <br /><pre>' . print_r($value,
                    true) . '</pre>');
        }
    }

    /**
     * Persist all subsets
     */
    public function save()
    {
        $host = $this;
        array_map(function ($value) use ($host) {
            $host->saveSubset($value);
        }, $this->fileBasedSubsets);
    }

    /**
     * Persist subset to disk
     * @param $subset
     */
    public function saveSubset($subset)
    {
        $changes = $subset . 'Changes';
        if ($this->$changes === true) {
            if (!defined('JSON_PRETTY_PRINT')) {
                $json = json_encode($this->$subset);
            } else {
                $json = json_encode($this->$subset, JSON_PRETTY_PRINT);
            }
            $subsetStoragePath = $this->storagePath . DIRECTORY_SEPARATOR . $subset . '.json';
            file_put_contents($subsetStoragePath, $json);

            $this->$changes = false;
        }
    }

    /**
     * Load subset from disk
     * @param $subset
     * @return mixed|string
     */
    protected function loadSubset($subset)
    {
        $subsetStoragePath = $this->storagePath . DIRECTORY_SEPARATOR . $subset . '.json';
        $json = file_get_contents($subsetStoragePath);
        $json = json_decode($json);
        $this->$subset = $json;
        return $json;
    }

    /**
     * @param $contentSqlPath
     */
    protected function initContentDb($contentSqlPath)
    {
        $db = $this->getContentDbHandle();
        $sql = file_get_contents($contentSqlPath);
        $db->exec($sql);
    }

    /**
     * @param $storageDefaultPath
     */
    protected function initConfigStorage($storageDefaultPath)
    {
        $json = file_get_contents($storageDefaultPath);
        $json = json_decode($json);
        $this->initConfigIfNotExists($json, 'sitemap');
        $this->initConfigIfNotExists($json, 'applicationComponents');
        $this->initConfigIfNotExists($json, 'documentTypes');
        $this->initConfigIfNotExists($json, 'bricks');
        $this->initConfigIfNotExists($json, 'imageSet');
        $this->initConfigIfNotExists($json, 'images');
        $this->initConfigIfNotExists($json, 'files');
        $this->initConfigIfNotExists($json, 'users');
        $this->initConfigIfNotExists($json, 'valuelists');
        $this->initConfigIfNotExists($json, 'redirects');
        $this->initConfigIfNotExists($json, 'activityLog');
    }

    /**
     * @return \PDO
     */
    protected function getContentDbHandle()
    {
        if ($this->contentDbHandle === null) {
            $this->contentDbHandle = new \PDO('sqlite:' . $this->storagePath . DIRECTORY_SEPARATOR . 'content.db');
        }
        return $this->contentDbHandle;
    }

    private function initConfigIfNotExists($json, $subsetName)
    {
        $subsetFileName = $this->storagePath . DIRECTORY_SEPARATOR . $subsetName . '.json';
        if (file_exists($subsetFileName)) {
            $this->loadSubset($subsetName);
        } else {
            $changes = $subsetName . 'Changes';
            $this->$subsetName = $json->$subsetName;
            $this->$changes = true;
        }
    }

    /**
     * @return ContentRepository
     */
    public function getContentRepository()
    {
        return $this->contentRepository;
    }
}