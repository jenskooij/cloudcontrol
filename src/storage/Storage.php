<?php
namespace CloudControl\Cms\storage {

    use CloudControl\Cms\storage\factories\DocumentFolderFactory;
    use CloudControl\Cms\storage\storage\ApplicationComponentsStorage;
    use CloudControl\Cms\storage\storage\BricksStorage;
    use CloudControl\Cms\storage\storage\DocumentStorage;
    use CloudControl\Cms\storage\storage\DocumentTypesStorage;
    use CloudControl\Cms\storage\storage\FilesStorage;
    use CloudControl\Cms\storage\storage\ImageSetStorage;
    use CloudControl\Cms\storage\storage\ImagesStorage;
    use CloudControl\Cms\storage\storage\RedirectsStorage;
    use CloudControl\Cms\storage\storage\SitemapStorage;
    use CloudControl\Cms\storage\storage\UsersStorage;
    use CloudControl\Cms\storage\storage\ValuelistsStorage;

    /**
	 * Class JsonStorage
     * @package CloudControl\Cms\storage
	 */
	class Storage
	{
		/**
		 * @var SitemapStorage
		 */
		protected $sitemap;
		/**
		 * @var ImagesStorage
		 */
		protected $images;
		/**
		 * @var ImageSetStorage
		 */
		protected $imageSet;
		/**
		 * @var FilesStorage
		 */
		protected $files;
		/**
		 * @var UsersStorage
		 */
		protected $users;
		/**
		 * @var DocumentTypesStorage
		 */
		protected $documentTypes;
		/**
		 * @var BricksStorage
		 */
		protected $bricks;
		/**
		 * @var ApplicationComponentsStorage
		 */
		protected $applicationComponents;

		/**
		 * @var ValuelistsStorage
		 */
		protected $valuelists;
		/**
		 * @var DocumentStorage
		 */
		protected $documents;
        /**
         * @var RedirectsStorage
         */
        protected $redirects;
        /**
         * @var String
         */
        protected $imagesDir;
        /**
         * @var String
         */
        protected $filesDir;

        /**
		 * @var String
		 */
		private $storageDir;
		/**
		 * @var Repository
		 */
		private $repository;

        /**
         * JsonStorage constructor.
         *
         * @param string $storageDir
         * @param $imagesDir
         * @param $filesDir
         */
        public function __construct($storageDir, $imagesDir, $filesDir)
		{
			$this->storageDir = $storageDir;
            $this->imagesDir = $imagesDir;
            $this->filesDir = $filesDir;
			$this->config();
		}

		/**
		 * Retrieve the data from the storagepath
		 * so it can be interacted with
		 *
		 * @throws \Exception
		 */
		private function config()
		{
			$storagePath = $this->storageDir;
			if (realpath($storagePath) === false) {
				if (Repository::create($storagePath)) {
					$repository = new Repository($storagePath);
					$repository->init();
					$this->repository = $repository;
				} else {
					throw new \Exception('Could not create repository directory: ' . $storagePath);
				}
			} else {
				$this->repository = new Repository($storagePath);
			}

		}

		/**
		 * @return \CloudControl\Cms\storage\storage\UsersStorage
		 */
		public function getUsers()
		{
			if (!$this->users instanceof UsersStorage) {
				$this->users = new UsersStorage($this->repository);
			}
			return $this->users;
		}

		/**
		 * Get documents
		 *
		 * @return DocumentStorage
		 */
		public function getDocuments()
		{
			if (!$this->documents instanceof DocumentStorage) {
				$this->documents = new DocumentStorage($this->repository);
			}
			return $this->documents;
		}

		/**
		 * Add new document in given path
		 *
		 * @param array $postValues
		 *
		 * @throws \Exception
		 */
		public function addDocumentFolder($postValues)
		{
			$documentFolderObject = DocumentFolderFactory::createDocumentFolderFromPostValues($postValues);
			if ($postValues['path'] === '/') {
				$documentFolderObject->path = $postValues['path'] . $documentFolderObject->slug;
			} else {
				$documentFolderObject->path = $postValues['path'] . '/' . $documentFolderObject->slug;
			}
			$this->repository->saveDocument($documentFolderObject, 'published');
			$this->repository->saveDocument($documentFolderObject, 'unpublished');
		}

		/**
		 * Delete a folder by its compound slug
		 *
		 * @param $slug
		 *
		 * @throws \Exception
		 */
		public function deleteDocumentFolderBySlug($slug)
		{
			$path = '/' . $slug;
			$this->repository->deleteDocumentByPath($path, 'published');
			$this->repository->deleteDocumentByPath($path, 'unpublished');
			$this->repository->cleanPublishedDeletedDocuments();
		}

		public function publishDocumentBySlug($slug)
		{
			$path = '/' . $slug;
			$this->repository->publishDocumentByPath($path);
		}

		public function unpublishDocumentBySlug($slug)
		{
			$path = '/' . $slug;
			$this->repository->unpublishDocumentByPath($path);
		}

		/**
		 * Retrieve a folder by its compound slug
		 *
		 * @param $slug
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		public function getDocumentFolderBySlug($slug)
		{
			$path = '/' . $slug;

			return $this->repository->getDocumentByPath($path);
		}

		/**
		 * Save changes to folder
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveDocumentFolder($postValues)
		{
			$this->addDocumentFolder($postValues);
		}

		/**
		 * @return SitemapStorage
		 */
		public function getSitemap()
		{
			if (!$this->sitemap instanceof SitemapStorage) {
				$this->sitemap = new SitemapStorage($this->repository);
			}
			return $this->sitemap;
		}

		/**
		 * Get all images
		 *
		 * @return ImagesStorage
		 */
		public function getImages()
		{
			if (!$this->images instanceof ImagesStorage) {

                $this->images = new ImagesStorage($this->repository, $this->imagesDir);
			}
			return $this->images;
		}

		/**
		 * Get all files
		 *
		 * @return FilesStorage
		 */
		public function getFiles()
		{
			if (!$this->files instanceof FilesStorage) {
				$this->files = new FilesStorage($this->repository, $this->filesDir);
			}
			return $this->files;
		}

		/**
		 * @return string
		 */
		public function getStorageDir()
		{
			return $this->storageDir;
		}

		/**
		 * @return \PDO
		 */
		public function getContentDbHandle()
		{
			return $this->repository->getContentDbHandle();
		}

		/**
		 * @return DocumentTypesStorage
		 */
		public function getDocumentTypes()
		{
			if (!$this->documentTypes instanceof DocumentTypesStorage) {
				$this->documentTypes = new DocumentTypesStorage($this->repository);
			}
			return $this->documentTypes;
		}

		/**
		 * @return BricksStorage
		 */
		public function getBricks()
		{
			if (!$this->bricks instanceof BricksStorage) {
				$this->bricks = new BricksStorage($this->repository);
			}
			return $this->bricks;
		}

		/**
		 * Get the image set
		 *
		 * @return ImageSetStorage
		 */
		public function getImageSet()
		{
			if (!$this->imageSet instanceof ImageSetStorage) {
				$this->imageSet = new ImageSetStorage($this->repository);
			}
			return $this->imageSet;
		}

		/**
		 * @return ApplicationComponentsStorage
		 */
		public function getApplicationComponents()
		{
			if (!$this->applicationComponents instanceof ApplicationComponentsStorage) {
				$this->applicationComponents = new ApplicationComponentsStorage($this->repository);
			}
			return $this->applicationComponents;
		}

		/**
		 * @return \CloudControl\Cms\storage\Repository
		 */
		public function getRepository()
		{
			return $this->repository;
		}

		/**
		 * @return \CloudControl\Cms\storage\storage\ValuelistsStorage
		 */
		public function getValuelists()
		{
			if (!$this->valuelists instanceof ValuelistsStorage) {
				$this->valuelists = new ValuelistsStorage($this->repository);
			}
			return $this->valuelists;
		}

        /**
         * @return \CloudControl\Cms\storage\storage\RedirectsStorage
         */
        public function getRedirects()
        {
            if (!$this->redirects instanceof RedirectsStorage) {
                $this->redirects = new RedirectsStorage($this->repository);
            }
            return $this->redirects;
        }

	}
}