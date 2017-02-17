<?php
/**
 * User: Jens
 * Date: 30-1-2017
 * Time: 20:15
 *
 * @property array sitemap
 * @property array applicationComponents
 * @property array documentTypes
 * @property array bricks
 * @property array imageSet
 * @property array images
 * @property array files
 * @property array users
 */

namespace library\storage;


class Repository
{
    protected $storagePath;

    protected $fileBasedSubsets = array('sitemap', 'applicationComponents', 'documentTypes', 'bricks', 'imageSet', 'images', 'files', 'users');

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

    protected $contentDbHandle;

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
        } else {
            throw new \Exception('Repository not yet initialized.');
        }
    }

    public static function create($storagePath)
    {
        return mkdir($storagePath);
    }

    /**
     * Initiates default storage
     * @throws \Exception
     */
    public function init()
    {
        $storageDefaultPath = realpath('../library/cc/install/_storage.json');
        $contentSqlPath = realpath('../library/cc/install/content.sql');

        $this->initConfigStorage($storageDefaultPath);
        $this->initContentDb($contentSqlPath);

        $this->save();
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            if (in_array($name, $this->fileBasedSubsets)) {
                return $this->$name;
            } else {
                dump();
            }
        } else {
            if (in_array($name, $this->fileBasedSubsets)) {
                return $this->loadSubset($name);
            } else {
                throw new \Exception('Trying to get undefined property from Repository: ' . $name);
            }
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->fileBasedSubsets)) {
            $this->$name = $value;
            $changes = $name . 'Changes';
            $this->$changes = true;
        } else {
            throw new \Exception('Trying to persist unknown subset in repository: ' . $name . ' <br /><pre>' . print_r($value, true) . '</pre>');
        }
    }

    public function save()
    {
        $this->sitemapChanges ? $this->saveSubset('sitemap') : null;
        $this->applicationComponentsChanges ? $this->saveSubset('applicationComponents') : null;
        $this->documentTypesChanges ? $this->saveSubset('documentTypes') : null;
        $this->bricksChanges ? $this->saveSubset('bricks') : null;
        $this->imageSetChanges ? $this->saveSubset('imageSet') : null;
        $this->imagesChanges ? $this->saveSubset('images') : null;
        $this->filesChanges ? $this->saveSubset('files') : null;
        $this->usersChanges ? $this->saveSubset('users') : null;
    }

    protected function saveSubset($subset)
    {
        $json = json_encode($this->$subset);
        $subsetStoragePath = $this->storagePath . DIRECTORY_SEPARATOR . $subset . '.json';
        file_put_contents($subsetStoragePath, $json);
        $changes = $subset . 'Changes';
        $this->$changes = false;
    }

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
        $this->sitemap = $json->sitemap;
        $this->sitemapChanges = true;
        $this->applicationComponents = $json->applicationComponents;
        $this->applicationComponentsChanges = true;
        $this->documentTypes = $json->documentTypes;
        $this->documentTypesChanges = true;
        $this->bricks = $json->bricks;
        $this->bricksChanges = true;
        $this->imageSet = $json->imageSet;
        $this->imageSetChanges = true;
        $this->images = $json->images;
        $this->imagesChanges = true;
        $this->files = $json->files;
        $this->filesChanges = true;
        $this->users = $json->users;
        $this->usersChanges = true;
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
}