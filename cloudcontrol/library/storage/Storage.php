<?php
namespace library\storage {

	use library\images\ImageResizer;
	use library\storage\factories\ApplicationComponentFactory;
	use library\storage\factories\BrickFactory;
	use library\storage\factories\DocumentFolderFactory;
	use library\storage\factories\DocumentTypeFactory;
	use library\storage\factories\ImageSetFactory;
	use library\storage\factories\UserFactory;
	use library\storage\storage\ImagesStorage;
	use library\storage\storage\SitemapStorage;

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
		 * Get user by username
		 *
		 * @param $username
		 *
		 * @return array
		 */
		public function getUserByUsername($username)
		{
			$return = array();

			$users = $this->repository->users;
			foreach ($users as $user) {
				if ($user->username == $username) {
					$return = $user;
					break;
				}
			}

			return $return;
		}

		/**
		 * Get user by slug
		 *
		 * @param $slug
		 *
		 * @return array
		 */
		public function getUserBySlug($slug)
		{
			$return = array();

			$users = $this->repository->users;
			foreach ($users as $user) {
				if ($user->slug == $slug) {
					$return = $user;
					break;
				}
			}

			return $return;
		}

		/**
		 * Get all users
		 *
		 * @return mixed
		 */
		public function getUsers()
		{
			return $this->repository->users;
		}

		/**
		 * Save user
		 *
		 * @param $slug
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveUser($slug, $postValues)
		{
			$userObj = UserFactory::createUserFromPostValues($postValues);
			if ($userObj->slug != $slug) {
				// If the username changed, check for duplicates
				$doesItExist = $this->getUserBySlug($userObj->slug);
				if (!empty($doesItExist)) {
					throw new \Exception('Trying to rename user to existing username');
				}
			}
			$users = $this->getUsers();
			foreach ($users as $key => $user) {
				if ($user->slug == $slug) {
					$users[$key] = $userObj;
				}
			}
			$this->repository->users = $users;
			$this->save();
		}

		/**
		 * Add user
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function addUser($postValues)
		{
			$userObj = UserFactory::createUserFromPostValues($postValues);

			$doesItExist = $this->getUserBySlug($userObj->slug);
			if (!empty($doesItExist)) {
				throw new \Exception('Trying to add username that already exists.');
			}
			$users = $this->repository->users;
			$users[] = $userObj;
			$this->repository->users = $users;
			$this->save();
		}

		/**
		 * Delete user by slug
		 *
		 * @param $slug
		 *
		 * @throws \Exception
		 */
		public function deleteUserBySlug($slug)
		{
			$userToDelete = $this->getUserBySlug($slug);
			if (empty($userToDelete)) {
				throw new \Exception('Trying to delete a user that doesn\'t exist.');
			}
			$users = $this->getUsers();
			foreach ($users as $key => $user) {
				if ($user->slug == $userToDelete->slug) {
					unset($users[$key]);
					$this->repository->users = array_values($users);
				}
			}
			$this->save();
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
		 * @return array
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

		/*
		 *
		 * Images
		 *
		 */
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

		/*
		 *
		 * Files
		 *
		 */
		/**
		 * Get all files
		 *
		 * @return array
		 */
		public function getFiles()
		{
			$files = $this->repository->files;
			usort($files, array($this, 'compareFiles'));

			return $files;
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

		private function compareFiles($a, $b)
		{
			return strcmp($a->file, $b->file);
		}

		public function addFile($postValues)
		{
			$destinationPath = realpath(__DIR__ . '/../../www/files/');

			$filename = $this->validateFilename($postValues['name'], $destinationPath);
			$destination = $destinationPath . '/' . $filename;

			if ($postValues['error'] != '0') {
				throw new \Exception('Error uploading file. Error code: ' . $postValues['error']);
			}

			if (move_uploaded_file($postValues['tmp_name'], $destination)) {
				$file = new \stdClass();
				$file->file = $filename;
				$file->type = $postValues['type'];
				$file->size = $postValues['size'];

				$files = $this->repository->files;
				$files[] = $file;
				$this->repository->files = $files;
				$this->save();
			} else {
				throw new \Exception('Error moving uploaded file');
			}
		}

		/**
		 * @param $filename
		 *
		 * @return null
		 */
		public function getFileByName($filename)
		{
			$files = $this->getFiles();
			foreach ($files as $file) {
				if ($filename == $file->file) {
					return $file;
				}
			}

			return null;
		}

		/**
		 * @param $filename
		 *
		 * @throws \Exception
		 */
		public function deleteFileByName($filename)
		{
			$destinationPath = realpath(__DIR__ . '/../../www/files/');
			$destination = $destinationPath . '/' . $filename;

			if (file_exists($destination)) {
				$files = $this->getFiles();
				foreach ($files as $key => $file) {
					if ($file->file == $filename) {
						unlink($destination);
						unset($files[$key]);
					}
				}

				$files = array_values($files);
				$this->repository->files = $files;
				$this->save();
			}
		}

		/*
		 * 
		 * Configuration
		 *
		 */
		/**
		 * @return array
		 */
		public function getDocumentTypes()
		{
			return $this->repository->documentTypes;
		}

		/**
		 * Add a document type from post values
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function addDocumentType($postValues)
		{
			$documentTypeObject = DocumentTypeFactory::createDocumentTypeFromPostValues($postValues);

			$documentTypes = $this->repository->documentTypes;
			$documentTypes[] = $documentTypeObject;
			$this->repository->documentTypes = $documentTypes;

			$this->save();
		}

		/**
		 * Delete document type
		 *
		 * @param $slug
		 *
		 * @throws \Exception
		 */
		public function deleteDocumentTypeBySlug($slug)
		{
			$documentTypes = $this->repository->documentTypes;
			foreach ($documentTypes as $key => $documentTypeObject) {
				if ($documentTypeObject->slug == $slug) {
					unset($documentTypes[$key]);
				}
			}
			$documentTypes = array_values($documentTypes);
			$this->repository->documentTypes = $documentTypes;
			$this->save();
		}

		/**
		 * Get document type by its slug
		 *
		 * @param      $slug
		 * @param bool $getBricks
		 *
		 * @return mixed
		 */
		public function getDocumentTypeBySlug($slug, $getBricks = false)
		{
			$documentTypes = $this->repository->documentTypes;
			foreach ($documentTypes as $documentType) {
				if ($documentType->slug == $slug) {
					if ($getBricks === true) {
						foreach ($documentType->bricks as $key => $brick) {
							$brickStructure = $this->getBrickBySlug($brick->brickSlug);
							$documentType->bricks[$key]->structure = $brickStructure;
						}
						foreach ($documentType->dynamicBricks as $key => $brickSlug) {
							$brickStructure = $this->getBrickBySlug($brickSlug);
							$documentType->dynamicBricks[$key] = $brickStructure;
						}
					}

					return $documentType;
				}
			}

			return null;
		}

		/**
		 * Save changes to a document type
		 *
		 * @param $slug
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveDocumentType($slug, $postValues)
		{
			$documentTypeObject = DocumentTypeFactory::createDocumentTypeFromPostValues($postValues);

			$documentTypes = $this->repository->documentTypes;
			foreach ($documentTypes as $key => $documentType) {
				if ($documentType->slug == $slug) {
					$documentTypes[$key] = $documentTypeObject;
				}
			}
			$this->repository->documentTypes = $documentTypes;
			$this->save();
		}

		/*
		 *
		 * Bricks
		 *
		 */
		/**
		 * @return array
		 */
		public function getBricks()
		{
			return $this->repository->bricks;
		}

		/**
		 * Add a brick
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function addBrick($postValues)
		{
			$brickObject = BrickFactory::createBrickFromPostValues($postValues);

			$bricks = $this->repository->bricks;
			$bricks[] = $brickObject;
			$this->repository->bricks = $bricks;

			$this->save();
		}

		/**
		 * Get a brick by its slug
		 *
		 * @param $slug
		 *
		 * @return \stdClass
		 */
		public function getBrickBySlug($slug)
		{
			$bricks = $this->repository->bricks;
			foreach ($bricks as $brick) {
				if ($brick->slug == $slug) {
					return $brick;
				}
			}

			return null;
		}

		/**
		 * Save changes to a brick
		 *
		 * @param $slug
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveBrick($slug, $postValues)
		{
			$brickObject = BrickFactory::createBrickFromPostValues($postValues);

			$bricks = $this->repository->bricks;
			foreach ($bricks as $key => $brick) {
				if ($brick->slug == $slug) {
					$bricks[$key] = $brickObject;
				}
			}
			$this->repository->bricks = $bricks;
			$this->save();
		}

		/**
		 * Delete a brick by its slug
		 *
		 * @param $slug
		 *
		 * @throws \Exception
		 */
		public function deleteBrickBySlug($slug)
		{
			$bricks = $this->repository->bricks;
			foreach ($bricks as $key => $brickObject) {
				if ($brickObject->slug == $slug) {
					unset($bricks[$key]);
				}
			}

			$bricks = array_values($bricks);
			$this->repository->bricks = $bricks;
			$this->save();
		}

		/*
		 * 
		 * Misc
		 *
		 */
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
		 * @return array
		 */
		public function getImageSet()
		{
			return $this->repository->imageSet;
		}

		/**
		 * Get Image by slug
		 *
		 * @param $slug
		 *
		 * @return \stdClass
		 */
		public function getImageSetBySlug($slug)
		{
			$imageSet = $this->getImageSet();
			foreach ($imageSet as $set) {
				if ($set->slug == $slug) {
					return $set;
				}
			}

			return null;
		}

		/**
		 * Save Image Set by it's slug
		 *
		 * @param $slug
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveImageSet($slug, $postValues)
		{
			$imageSetObject = ImageSetFactory::createImageSetFromPostValues($postValues);

			$imageSet = $this->repository->imageSet;
			foreach ($imageSet as $key => $set) {
				if ($set->slug == $slug) {
					$imageSet[$key] = $imageSetObject;
				}
			}
			$this->repository->imageSet = $imageSet;
			$this->save();
		}

		/**
		 * Add image set
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function addImageSet($postValues)
		{
			$imageSetObject = ImageSetFactory::createImageSetFromPostValues($postValues);

			$imageSet = $this->repository->imageSet;
			$imageSet[] = $imageSetObject;
			$this->repository->imageSet = $imageSet;

			$this->save();
		}

		/**
		 * Delete Image Set by its slug
		 *
		 * @param $slug
		 *
		 * @throws \Exception
		 */
		public function deleteImageSetBySlug($slug)
		{
			$imageSet = $this->getImageSet();

			foreach ($imageSet as $key => $set) {
				if ($set->slug == $slug) {
					unset($imageSet[$key]);
				}
			}
			$imageSet = array_values($imageSet);
			$this->repository->imageSet = $imageSet;
			$this->save();
		}

		/**
		 * Get the image set with the smallest size
		 *
		 * @return \stdClass
		 */
		public function getSmallestImageSet()
		{
			$imageSet = $this->getImageSet();

			$returnSize = PHP_INT_MAX;
			$returnSet = null;

			foreach ($imageSet as $set) {
				$size = $set->width * $set->height;
				if ($size < $returnSize) {
					$returnSize = $size;
					$returnSet = $set;
				}
			}

			if ($returnSet === null) {
				$returnSet = new \stdClass();
				$returnSet->slug = 'original';
			}

			return $returnSet;
		}

		/**
		 * @return array
		 */
		public function getApplicationComponents()
		{
			return $this->repository->applicationComponents;
		}

		public function addApplicationComponent($postValues)
		{
			$applicationComponent = ApplicationComponentFactory::createApplicationComponentFromPostValues($postValues);
			$applicationComponents = $this->repository->applicationComponents;
			$applicationComponents[] = $applicationComponent;
			$this->repository->applicationComponents = $applicationComponents;

			$this->save();
		}

		public function getApplicationComponentBySlug($slug)
		{
			$applicationComponents = $this->getApplicationComponents();
			foreach ($applicationComponents as $applicationComponent) {
				if ($applicationComponent->slug == $slug) {
					return $applicationComponent;
				}
			}

			return null;
		}

		public function saveApplicationComponent($slug, $postValues)
		{
			$newApplicationComponent = ApplicationComponentFactory::createApplicationComponentFromPostValues($postValues);

			$applicationComponents = $this->getApplicationComponents();
			foreach ($applicationComponents as $key => $applicationComponent) {
				if ($applicationComponent->slug == $slug) {
					$applicationComponents[$key] = $newApplicationComponent;
				}
			}
			$this->repository->applicationComponents = $applicationComponents;
			$this->save();
		}

		public function deleteApplicationComponentBySlug($slug)
		{
			$applicationComponents = $this->getApplicationComponents();
			foreach ($applicationComponents as $key => $applicationComponent) {
				if ($applicationComponent->slug == $slug) {
					unset($applicationComponents[$key]);
				}
			}
			$applicationComponents = array_values($applicationComponents);
			$this->repository->applicationComponents = $applicationComponents;
			$this->save();
		}

	}
}