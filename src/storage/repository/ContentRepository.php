<?php

namespace CloudControl\Cms\storage\repository {

    use CloudControl\Cms\storage\Document;
    use CloudControl\Cms\storage\Repository;
    use CloudControl\Cms\storage\storage\DocumentStorage;

    /**
     * Created by jensk on 4-9-2017.
     */
    class ContentRepository
    {
        protected $storagePath;
        protected $contentDbHandle;

        /**
         * ContentRepository constructor.
         * @param $storagePath
         */
        public function __construct($storagePath)
        {
            $this->storagePath = $storagePath;
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
         * @param Repository $repository
         * @param $document
         * @param $db
         * @param $documents
         * @param $key
         * @return mixed
         */
        private function setAssetsToDocumentFolders(Repository $repository, $document, $db, $documents, $key)
        {
            if ($document->type === 'folder') {
                $document->dbHandle = $db;
                $document->documentStorage = new DocumentStorage($repository);
                $documents[$key] = $document;
            }

            return $documents;
        }

        /**
         * @param Repository $repository
         * @param string $folderPath
         * @return array
         */
        public function getDocumentsWithState(Repository $repository, $folderPath = '/')
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


            $documents = $stmt->fetchAll(\PDO::FETCH_CLASS, '\CloudControl\Cms\storage\Document');
            foreach ($documents as $key => $document) {
                $documents = $this->setAssetsToDocumentFolders($repository, $document, $db, $documents, $key);
            }
            //dump($documents);
            return $documents;
        }

        /**
         * Get all documents and folders in a certain path
         *
         * @param Repository $repository
         * @param        $folderPath
         * @param string $state
         * @return array
         * @throws \Exception
         */
        public function getDocumentsByPath(Repository $repository, $folderPath, $state = 'published')
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

            $documents = $stmt->fetchAll(\PDO::FETCH_CLASS, '\CloudControl\Cms\storage\Document');
            foreach ($documents as $key => $document) {
                $documents = $this->setAssetsToDocumentFolders($repository, $document, $db, $documents, $key);
            }
            return $documents;
        }

        /**
         * @param \CloudControl\Cms\storage\Repository $repository
         * @param        $path
         * @param string $state
         * @return bool|Document
         * @throws \Exception
         */
        public function getDocumentByPath(Repository $repository, $path, $state = 'published')
        {
            if (!in_array($state, Document::$DOCUMENT_STATES)) {
                throw new \Exception('Unsupported document state: ' . $state);
            }
            $db = $this->getContentDbHandle();
            $document = $this->fetchDocument('
            SELECT *
              FROM documents_' . $state . '
             WHERE path = ' . $db->quote($path) . '
        ');
            if ($document instanceof Document && $document->type === 'folder') {
                $document->dbHandle = $db;
                $document->documentStorage = new DocumentStorage($repository);
            }
            return $document;
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
            return $stmt->fetchObject('\CloudControl\Cms\storage\Document');
        }

        /**
         * @param Repository $repository
         * @param $path
         * @return bool|Document
         */
        public function getDocumentContainerByPath(Repository $repository, $path)
        {
            $document = $this->getDocumentByPath($repository, $path, 'unpublished');
            if ($document === false) {
                return false;
            }
            $slugLength = strlen($document->slug);
            $containerPath = substr($path, 0, -$slugLength);
            if ($containerPath === '/') {
                return $this->getRootFolder();
            }
            if (substr($containerPath, -1) === '/') {
                $containerPath = substr($containerPath, 0, -1);
            }
            $containerFolder = $this->getDocumentByPath($repository, $containerPath, 'unpublished');
            return $containerFolder;
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
            if (!is_array($result)) {
                return 0;
            }
            return intval(current($result));
        }

        /**
         * @return array
         * @throws \Exception
         */
        public function getPublishedDocumentsNoFolders()
        {
            $db = $this->getContentDbHandle();
            $sql = '
			SELECT *
			  FROM documents_published
			 WHERE `type` != "folder"
		';
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(\PDO::FETCH_CLASS, '\CloudControl\Cms\storage\Document');
            if ($stmt === false || !$stmt->execute()) {
                $errorInfo = $db->errorInfo();
                $errorMsg = $errorInfo[2];
                throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
            }
            return $result;
        }

        /**
         * @param $path
         */
        public function publishDocumentByPath($path)
        {
            $this->publishOrUnpublishDocumentByPath($path);
        }

        /**
         * @param $path
         */
        public function unpublishDocumentByPath($path)
        {
            $this->publishOrUnpublishDocumentByPath($path, false);
        }

        /**
         * @param $path
         * @param bool $publish
         * @throws \Exception
         */
        public function publishOrUnpublishDocumentByPath($path, $publish = true)
        {
            if ($publish) {
                $sql = '
				INSERT OR REPLACE INTO documents_published 
					  (`id`,`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,`state`,`lastModificationDate`,`creationDate`,`publicationDate`,`lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`)
				SELECT `id`,`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,"published" as state,`lastModificationDate`,`creationDate`,' . time() . ' as publicationDate, `lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`
				  FROM documents_unpublished
				 WHERE `path` = :path
			';
            } else {
                $sql = 'DELETE FROM documents_published
					  WHERE `path` = :path';
            }
            $db = $this->getContentDbHandle();
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
            $sql = '   DELETE FROM documents_published
						 WHERE documents_published.path IN (
						SELECT documents_published.path
						  FROM documents_published
					 LEFT JOIN documents_unpublished
							ON documents_unpublished.path = documents_published.path
						 WHERE documents_unpublished.path IS NULL
		)';
            $stmt = $this->getDbStatement($sql);
            $stmt->execute();
        }

        /**
         * Save the document to the database
         *
         * @param Document $documentObject
         * @param string $state
         *
         * @return bool
         * @throws \Exception
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
         * @param Repository $repository
         * @param        $path
         */
        public function deleteDocumentByPath(Repository $repository, $path)
        {
            $db = $this->getContentDbHandle();
            $documentToDelete = $this->getDocumentByPath($repository, $path, 'unpublished');
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


}