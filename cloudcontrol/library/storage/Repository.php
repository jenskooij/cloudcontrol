<?php
/**
 * User: Jens
 * Date: 30-1-2017
 * Time: 20:15
 */

namespace library\storage;
use library\storage\storage\DocumentStorage;

/**
 * Class Repository
 * @package library\storage
 * @property array sitemap
 * @property array applicationComponents
 * @property array documentTypes
 * @property array bricks
 * @property array imageSet
 * @property array images
 * @property array files
 * @property array users
 */
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

    /**
     * Creates the folder in which to create all storage related files
     *
     * @param $storagePath
     * @return bool
     */
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
            throw new \Exception('Trying to persist unknown subset in repository: ' . $name . ' <br /><pre>' . print_r($value, true) . '</pre>');
        }
    }

    /**
     * Persist all subsets
     */
    public function save()
    {
        array_map(function ($value) {
        	$this->saveSubset($value);
		}, $this->fileBasedSubsets);
    }

    /**
     * Persist subset to disk
     * @param $subset
     */
    protected function saveSubset($subset)
    {
		$changes = $subset . 'Changes';
		if ($this->$changes === true) {
			$json = json_encode($this->$subset, JSON_PRETTY_PRINT);
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
    public function getContentDbHandle()
    {
        if ($this->contentDbHandle === null) {
            $this->contentDbHandle = new \PDO('sqlite:' . $this->storagePath . DIRECTORY_SEPARATOR . 'content.db');
        }
        return $this->contentDbHandle;
    }

	/**
	 * Get all documents
	 *
	 * @param string $state
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function getDocuments($state = 'published')
    {
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
        return $this->getDocumentsByPath('/', $state);
    }

	public function getDocumentsWithState($folderPath = '/')
	{
		$db = $this->getContentDbHandle();
		$folderPathWithWildcard = $folderPath . '%';

		$ifRootIndex = 1;
		if ($folderPath == '/') {
			$ifRootIndex = 0;
		}

		$sql = '
            SELECT documents_unpublished.*,
            	   IFNULL(documents_published.state,"unpublished") as state,
            	   IFNULL(documents_published.publicationDate,NULL) as publicationDate,
            	   (documents_published.lastModificationDate != documents_unpublished.lastModificationDate) as unpublishedChanges 
              FROM documents_unpublished
		 LEFT JOIN documents_published
         		ON documents_published.path = documents_unpublished.path
             WHERE documents_unpublished.`path` LIKE ' . $db->quote($folderPathWithWildcard) . '
               AND substr(documents_unpublished.`path`, ' . (strlen($folderPath) + $ifRootIndex + 1) . ') NOT LIKE "%/%"
               AND length(documents_unpublished.`path`) > ' . (strlen($folderPath) + $ifRootIndex) . '
               AND documents_unpublished.path != ' . $db->quote($folderPath) . '
          ORDER BY documents_unpublished.`type` DESC, documents_unpublished.`path` ASC
        ';
		$stmt = $this->getDbStatement($sql);



		$documents = $stmt->fetchAll(\PDO::FETCH_CLASS, '\library\storage\Document');
		foreach ($documents as $key => $document) {
			if ($document->type === 'folder') {
				$document->dbHandle = $db;
				$document->documentStorage = new DocumentStorage($this);
				$documents[$key] = $document;
			}
		}
		//dump($documents);
		return $documents;
	}

	/**
	 * Get all documents and folders in a certain path
	 *
	 * @param        $folderPath
	 * @param string $state
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function getDocumentsByPath($folderPath, $state = 'published')
    {
    	if (!in_array($state, Document::$DOCUMENT_STATES)) {
    		throw new \Exception('Unsupported document state: ' . $state);
		}
        $db = $this->getContentDbHandle();
        $folderPathWithWildcard = $folderPath . '%';

        $sql = 'SELECT *
              FROM documents_' . $state . '
             WHERE `path` LIKE ' . $db->quote($folderPathWithWildcard) . '
               AND substr(`path`, ' . (strlen($folderPath) + 1) . ') NOT LIKE "%/%"
               AND path != ' . $db->quote($folderPath) . '
          ORDER BY `type` DESC, `path` ASC';
        $stmt = $this->getDbStatement($sql);

        $documents = $stmt->fetchAll(\PDO::FETCH_CLASS, '\library\storage\Document');
        foreach ($documents as $key => $document) {
            if ($document->type === 'folder') {
                $document->dbHandle = $db;
                $document->documentStorage = new DocumentStorage($this);
                $documents[$key] = $document;
            }
        }
        return $documents;
    }


    /**
     * @param $path
     * @return bool|Document
     */
    public function getDocumentContainerByPath($path)
    {
        $document = $this->getDocumentByPath($path, 'unpublished');
        if ($document === false) {
            return false;
        }
        $slugLength = strlen($document->slug);
        $containerPath = substr($path, 0, -$slugLength);
        if ($containerPath === '/') {
            return $this->getRootFolder();
        }
        if (substr($containerPath, -1) === '/'){
			$containerPath = substr($containerPath, 0, -1);
		}
        $containerFolder = $this->getDocumentByPath($containerPath, 'unpublished');
        return $containerFolder;
    }

	/**
	 * @param        $path
	 * @param string $state
	 *
	 * @return bool|\library\storage\Document
	 * @throws \Exception
	 */
    public function getDocumentByPath($path, $state = 'published')
    {
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
        $db = $this->getContentDbHandle();
        $document = $this->fetchDocument('
            SELECT *
              FROM documents_' .  $state . '
             WHERE path = ' . $db->quote($path) . '
        ');
        if ($document instanceof Document && $document->type === 'folder') {
            $document->dbHandle = $db;
            $document->documentStorage = new DocumentStorage($this);
        }
        return $document;
    }

	/**
	 * Returns the count of all documents stored in the db
	 *
	 * @param string $state
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function getTotalDocumentCount($state = 'published')
	{
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
		$db = $this->getContentDbHandle();
		$stmt = $db->query('
			SELECT count(*)
			  FROM documents_' . $state . '
			 WHERE `type` != "folder"
		');
		$result = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!is_array($result )) {
			return 0;
		}
		return intval(current($result));
	}

	public function getPublishedDocumentsNoFolders()
	{
		$db = $this->getContentDbHandle();
		$sql = '
			SELECT *
			  FROM documents_published
			 WHERE `type` != "folder"
		';
		$stmt = $db->query($sql);
		$result = $stmt->fetchAll(\PDO::FETCH_CLASS, '\library\storage\Document');
		if ($stmt === false || !$stmt->execute()) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		return $result;
	}

	public function publishDocumentByPath($path)
	{
		$db = $this->getContentDbHandle();
		$sql = '
			INSERT OR REPLACE INTO documents_published 
				  (`id`,`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,`state`,`lastModificationDate`,`creationDate`,`publicationDate`,`lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`)
		    SELECT `id`,`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,"published" as state,`lastModificationDate`,`creationDate`,' . time() . ' as publicationDate, `lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`
		      FROM documents_unpublished
		     WHERE `path` = :path
		';
		$stmt = $db->prepare($sql);
		if ($stmt === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':path', $path);
		$stmt->execute();
	}

	public function unpublishDocumentByPath($path)
	{
		$db = $this->getContentDbHandle();
		$sql = 'DELETE FROM documents_published
					  WHERE `path` = :path';
		$stmt = $db->prepare($sql);
		if ($stmt === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':path', $path);
		$stmt->execute();
	}

	public function cleanPublishedDeletedDocuments()
	{
		$db = $this->getContentDbHandle();
		$sql = '   DELETE FROM documents_published
						 WHERE documents_published.path IN (
						SELECT documents_published.path
						  FROM documents_published
					 LEFT JOIN documents_unpublished
							ON documents_unpublished.path = documents_published.path
						 WHERE documents_unpublished.path IS NULL
		)';
		$stmt = $db->query($sql);
		if ($stmt === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->execute();
	}

	/**
     * Return the results of the query as array of Documents
     * @param $sql
     * @return array
     * @throws \Exception
     */
    protected function fetchAllDocuments($sql)
    {
        $stmt = $this->getDbStatement($sql);
        return $stmt->fetchAll(\PDO::FETCH_CLASS, '\library\storage\Document');
    }

    /**
     * Return the result of the query as Document
     * @param $sql
     * @return mixed
     * @throws \Exception
     */
    protected function fetchDocument($sql)
    {
        $stmt = $this->getDbStatement($sql);
        return $stmt->fetchObject('\library\storage\Document');
    }

    /**
     * Prepare the sql statement
     * @param $sql
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function getDbStatement($sql)
    {
        $db = $this->getContentDbHandle();
        $stmt = $db->query($sql);
        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
        return $stmt;
    }

    /**
     * Create a (non-existent) root folder Document and return it
     * @return Document
     */
    protected function getRootFolder()
    {
        $rootFolder = new Document();
        $rootFolder->path = '/';
        $rootFolder->type = 'folder';
        return $rootFolder;
    }

	/**
	 * Save the document to the database
	 *
	 * @param Document $documentObject
	 * @param string   $state
	 *
	 * @return bool
	 * @throws \Exception
	 * @internal param $path
	 */
    public function saveDocument($documentObject, $state = 'published')
    {
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
        $db = $this->getContentDbHandle();
        $stmt = $this->getDbStatement('
            INSERT OR REPLACE INTO documents_' . $state . ' (`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,`state`,`lastModificationDate`,`creationDate`,`lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`)
            VALUES(
              ' . $db->quote($documentObject->path) . ',
              ' . $db->quote($documentObject->title) . ',
              ' . $db->quote($documentObject->slug) . ',
              ' . $db->quote($documentObject->type) . ',
              ' . $db->quote($documentObject->documentType) . ',
              ' . $db->quote($documentObject->documentTypeSlug) . ',
              ' . $db->quote($documentObject->state) . ',
              ' . $db->quote($documentObject->lastModificationDate) . ',
              ' . $db->quote($documentObject->creationDate) . ',
              ' . $db->quote($documentObject->lastModifiedBy) . ',
              ' . $db->quote(json_encode($documentObject->fields)) . ',
              ' . $db->quote(json_encode($documentObject->bricks)) . ',
              ' . $db->quote(json_encode($documentObject->dynamicBricks)) . '
            )
        ');
        $result = $stmt->execute();
        return $result;
    }

	/**
	 * Delete the document from the database
	 * If it's a folder, also delete it's contents
	 *
	 * @param        $path
	 *
	 * @internal param string $state
	 *
	 */
    public function deleteDocumentByPath($path)
    {
        $db = $this->getContentDbHandle();
        $documentToDelete = $this->getDocumentByPath($path, 'unpublished');
        if ($documentToDelete instanceof Document) {
            if ($documentToDelete->type == 'document') {
                $stmt = $this->getDbStatement('
                    DELETE FROM documents_unpublished
                          WHERE path = ' . $db->quote($path) . '
                ');
                $stmt->execute();
            } elseif ($documentToDelete->type == 'folder') {
                $folderPathWithWildcard = $path . '%';
                $stmt = $this->getDbStatement('
                    DELETE FROM documents_unpublished
                          WHERE (path LIKE ' . $db->quote($folderPathWithWildcard) . '
                            AND substr(`path`, ' . (strlen($path) + 1) . ', 1) = "/")
                            OR path = ' . $db->quote($path) . '
                ');
                $stmt->execute();
            }
        }
    }
}