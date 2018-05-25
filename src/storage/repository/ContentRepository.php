<?php

namespace CloudControl\Cms\storage\repository {

    use CloudControl\Cms\storage\entities\Document;
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
                throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
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
            if ($this->isFolder($document)) {
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
         * @throws \Exception
         */
        public function getDocumentsWithState(Repository $repository, $folderPath = '/')
        {
            $db = $this->getContentDbHandle();
            $folderPathWithWildcard = $folderPath . '%';

            $ifRootIndex = 1;
            if ($folderPath === '/') {
                $ifRootIndex = 0;
            }

            $sql = $this->getSqlForDocumentsWithSate($folderPath, $db, $folderPathWithWildcard, $ifRootIndex);
            $stmt = $this->getDbStatement($sql);


            $documents = $stmt->fetchAll(\PDO::FETCH_CLASS, Document::class);
            foreach ($documents as $key => $document) {
                $documents = $this->setAssetsToDocumentFolders($repository, $document, $db, $documents, $key);
            }
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
            if (!in_array($state, Document::$DOCUMENT_STATES, true)) {
                throw new \RuntimeException('Unsupported document state: ' . $state);
            }
            $db = $this->getContentDbHandle();
            $folderPathWithWildcard = $folderPath . '%';

            $sql = 'SELECT *
              FROM documents_' . $state . '
             WHERE `path` LIKE ' . $db->quote($folderPathWithWildcard) . '
               AND instr(substr(`path`, ' . (strlen($folderPath) + 2) . '), \'/\') = 0
               AND path != ' . $db->quote($folderPath) . '
               AND publicationDate <= ' . time() . '
          ORDER BY `type` DESC, `path` ASC';
            $stmt = $this->getDbStatement($sql);

            $documents = $stmt->fetchAll(\PDO::FETCH_CLASS, Document::class);
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
            if (!in_array($state, Document::$DOCUMENT_STATES, true)) {
                throw new \RuntimeException('Unsupported document state: ' . $state);
            }
            if ($path === '/') {
                return $this->getRootFolder($repository);
            }
            $db = $this->getContentDbHandle();
            $document = $this->fetchDocumentForDocumentByPath($path, $state, $db);
            if ($this->isFolder($document)) {
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
            return $stmt->fetchObject(Document::class);
        }

        /**
         * @param Repository $repository
         * @param $path
         * @return bool|Document
         * @throws \Exception
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
                return $this->getRootFolder($repository);
            }
            if (substr($containerPath, -1) === '/') {
                $containerPath = substr($containerPath, 0, -1);
            }
            $containerFolder = $this->getDocumentByPath($repository, $containerPath, 'unpublished');
            return $containerFolder;
        }

        /**
         * Create a (non-existent) root folder Document and return it
         * @param Repository $repository
         * @return Document
         */
        protected function getRootFolder(Repository $repository)
        {
            $rootFolder = new Document();
            $rootFolder->path = '/';
            $rootFolder->type = 'folder';
            $rootFolder->dbHandle = $this->getContentDbHandle();
            $rootFolder->documentStorage = new DocumentStorage($repository);
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
            if (!in_array($state, Document::$DOCUMENT_STATES, true)) {
                throw new \RuntimeException('Unsupported document state: ' . $state);
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
            return (int) current($result);
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
            $result = $stmt->fetchAll(\PDO::FETCH_CLASS, Document::class);
            if ($stmt === false || !$stmt->execute()) {
                $errorInfo = $db->errorInfo();
                $errorMsg = $errorInfo[2];
                throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
            }
            return $result;
        }

        /**
         * @param $path
         * @param int $publicationDate
         */
        public function publishDocumentByPath($path, $publicationDate = null)
        {
            $this->publishOrUnpublishDocumentByPath($path, true, $publicationDate);
        }

        /**
         * @param $path
         * @throws \Exception
         */
        public function unpublishDocumentByPath($path)
        {
            $this->publishOrUnpublishDocumentByPath($path, false);
        }

        /**
         * @param $path
         * @param bool $publish
         * @param int $publicationDate
         */
        public function publishOrUnpublishDocumentByPath($path, $publish = true, $publicationDate = null)
        {
            $publicationDate = $publicationDate !== null ? intval($publicationDate) : time();
            $sql = 'DELETE FROM documents_published
					  WHERE `path` = :path';
            if ($publish) {
                $sql = '
				INSERT OR REPLACE INTO documents_published 
					  (`id`,`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,`state`,`lastModificationDate`,`creationDate`,`publicationDate`,`lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`)
				SELECT `id`,`path`,`title`,`slug`,`type`,`documentType`,`documentTypeSlug`,"published" AS state,`lastModificationDate`,`creationDate`,' . $publicationDate . ' AS publicationDate, `lastModifiedBy`,`fields`,`bricks`,`dynamicBricks`
				  FROM documents_unpublished
				 WHERE `path` = :path
			';
            }
            $db = $this->getContentDbHandle();
            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                $errorInfo = $db->errorInfo();
                $errorMsg = $errorInfo[2];
                throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
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
            if (!in_array($state, Document::$DOCUMENT_STATES, true)) {
                throw new \RuntimeException('Unsupported document state: ' . $state);
            }
            $db = $this->getContentDbHandle();
            $stmt = $this->getStatementForSaveDocument($documentObject, $state, $db);
            return $stmt->execute();
        }

        /**
         * Delete the document from the database
         * If it's a folder, also delete it's contents
         *
         * @param Repository $repository
         * @param        $path
         * @throws \Exception
         */
        public function deleteDocumentByPath(Repository $repository, $path)
        {
            $db = $this->getContentDbHandle();
            $documentToDelete = $this->getDocumentByPath($repository, $path, 'unpublished');
            if ($documentToDelete instanceof Document) {
                if ($documentToDelete->type === 'document') {
                    $stmt = $this->getDbStatement('
                    DELETE FROM documents_unpublished
                          WHERE path = ' . $db->quote($path) . '
                ');
                    $stmt->execute();
                } elseif ($documentToDelete->type === 'folder') {
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

        /**
         * @param $folderPath
         * @param $db
         * @param $folderPathWithWildcard
         * @param $ifRootIndex
         * @return string
         */
        private function getSqlForDocumentsWithSate($folderPath, $db, $folderPathWithWildcard, $ifRootIndex)
        {
            $sql = '
            SELECT documents_unpublished.*,
            	   IFNULL(documents_published.state,"unpublished") AS state,
            	   IFNULL(documents_published.publicationDate,NULL) AS publicationDate,
            	   (documents_published.lastModificationDate != documents_unpublished.lastModificationDate) AS unpublishedChanges 
              FROM documents_unpublished
		 LEFT JOIN documents_published
         		ON documents_published.path = documents_unpublished.path
             WHERE documents_unpublished.`path` LIKE ' . $db->quote($folderPathWithWildcard) . '
               AND substr(documents_unpublished.`path`, ' . (strlen($folderPath) + $ifRootIndex + 1) . ') NOT LIKE "%/%"
               AND length(documents_unpublished.`path`) > ' . (strlen($folderPath) + $ifRootIndex) . '
               AND documents_unpublished.path != ' . $db->quote($folderPath) . '
          ORDER BY documents_unpublished.`type` DESC, documents_unpublished.`path` ASC
        ';
            return $sql;
        }

        /**
         * @param $documentObject
         * @param $state
         * @param $db
         * @return \PDOStatement
         * @throws \Exception
         */
        private function getStatementForSaveDocument($documentObject, $state, $db)
        {
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
            return $stmt;
        }

        /**
         * @param Document $document
         * @return bool
         */
        private function isFolder($document)
        {
            return $document instanceof Document && $document->type === 'folder';
        }

        /**
         * @param string $path
         * @param string $state
         * @param \PDO $db
         * @return Document
         * @throws \Exception
         */
        private function fetchDocumentForDocumentByPath($path, $state, $db)
        {
            $sql = '
                SELECT *
                  FROM documents_unpublished
                 WHERE path = ' . $db->quote($path) . '
            ';
            if ($state === 'published') {
                $sql = '
                SELECT *
                  FROM documents_published
                 WHERE path = ' . $db->quote($path) . '
                   AND (`publicationDate` <= ' . time() . ' OR `type` = "folder")
            ';
            }

            $document = $this->fetchDocument($sql);
            return $document;
        }
    }


}