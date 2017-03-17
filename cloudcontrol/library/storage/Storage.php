<?php
namespace library\storage {

	use library\storage\factories\ApplicationComponentFactory;
	use library\storage\factories\DocumentFolderFactory;
	use library\storage\storage\ApplicationComponentsStorage;
	use library\storage\storage\BricksStorage;
	use library\storage\storage\DocumentTypesStorage;
	use library\storage\storage\FilesStorage;
	use library\storage\storage\ImageSetStorage;
	use library\storage\storage\ImagesStorage;
	use library\storage\storage\SitemapStorage;
	use library\storage\storage\UsersStorage;

	/**
	 * Class JsonStorage
	 * @package library\storage
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
		 */
		public function __construct($storageDir)
		{
			$this->storageDir = $storageDir;
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
			$storagePath = __DIR__ . '/../../' . $this->storageDir;
			if (realpath($storagePath) === false) {
				initFramework();
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
		 * @return \library\storage\storage\UsersStorage
		 */
		public function getUsers()
		{
			if (!$this->users instanceof UsersStorage) {
				$this->users = new UsersStorage($this->repository);
			}
			return $this->users;
		}

		/*
		 *
		 * Documents
		 *
		 */
		/**
		 * Get documents
		 *
		 * @return array
		 */
		public function getDocuments()
		{
			return $this->repository->getDocuments();
		}

		public function getTotalDocumentCount()
		{
			return $this->repository->getTotalDocumentCount();
		}

		/**
		 * @param string $slug
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		public function getDocumentBySlug($slug)
		{
			$path = '/' . $slug;

			return $this->repository->getDocumentByPath($path);
		}

		/**
		 * @param $postValues
		 */
		public function saveDocument($postValues)
		{
			$oldPath = '/' . $postValues['path'];

			$container = $this->getDocumentContainerByPath($oldPath);
			$documentObject = DocumentFactory::createDocumentFromPostValues($postValues, $this);
			if ($container->path === '/') {
				$newPath = $container->path . $documentObject->slug;
			} else {
				$newPath = $container->path . '/' . $documentObject->slug;
			}
			$documentObject->path = $newPath;
			$this->repository->saveDocument($documentObject);
		}

		public function addDocument($postValues)
		{
			$documentObject = DocumentFactory::createDocumentFromPostValues($postValues, $this);
			if ($postValues['path'] === '/') {
				$documentObject->path = $postValues['path'] . $documentObject->slug;
			} else {
				$documentObject->path = $postValues['path'] . '/' . $documentObject->slug;
			}

			$this->repository->saveDocument($documentObject);
		}

		public function deleteDocumentBySlug($slug)
		{
			$path = '/' . $slug;
			$this->repository->deleteDocumentByPath($path);
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
			$this->repository->saveDocument($documentFolderObject);
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
			$this->repository->deleteDocumentByPath($path);
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
		 * Convert path to indeces
		 *
		 * @param $path
		 *
		 * @return bool|\library\storage\Document
		 * @throws \Exception
		 */
		private function getDocumentContainerByPath($path)
		{
			return $this->repository->getDocumentContainerByPath($path);
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
				$this->images = new ImagesStorage($this->repository);
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
				$this->files = new FilesStorage($this->repository);
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

		/*
		 *
		 * Bricks
		 *
		 */
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
		 * Save changes made to the repository
		 *
		 * @throws \Exception
		 */
		private function save()
		{
			$this->repository->save();
		}

		/*
		 *
		 * Image Set
		 *
		 */

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
	}
}