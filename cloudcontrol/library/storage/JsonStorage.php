<?php
namespace library\storage
{

    use library\crypt\Crypt;
    use library\images\ImageResizer;

    /**
	 * Class JsonStorage
	 * @package library\storage
	 */
	class JsonStorage implements Storage
	{
		private $storageDir;
        /**
         * @var Repository
         */
		private $repository;

		/**
		 * JsonStorage constructor.
		 *
		 * @param $storageDir
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
         * @throws \Exception
         */
		public function saveUser($slug, $postValues)
		{
			$userObj = $this->createUserFromPostValues($postValues);
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
         * @throws \Exception
         */
		public function addUser($postValues)
		{
			$userObj = $this->createUserFromPostValues($postValues);

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
         * @param $slug
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

        /**
         * Create user from POST values
         * @param $postValues
         * @return \stdClass
         * @throws \Exception
         */
		private function createUserFromPostValues($postValues)
		{
			if (isset($postValues['username'])) {
				$user = new \stdClass();
				$user->username = $postValues['username'];
				$user->slug = slugify($postValues['username']);
				$user->rights = array();
				if (isset($postValues['rights'])) {
					$user->rights = $postValues['rights'];
				}

				if (isset($postValues['password']) && empty($postValues['password']) === false) {
					$crypt = new Crypt();
					$user->password = $crypt->encrypt($postValues['password'], 16);
					$user->salt = $crypt->getLastSalt();
				} else {
					$user->password = $postValues['passHash'];
					$user->salt = $postValues['salt'];
				}

				return $user;
			} else {
				throw new \Exception('Trying to create user with invalid data.');
			}
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
		 * @return mixed
		 * @throws \Exception
		 */
		public function getDocumentBySlug($slug)
		{
            $path = '/' . $slug;
			return $this->repository->getDocumentByPath($path);
		}

		public function saveDocument($postValues)
		{
            $oldPath = '/' . $postValues['path'];

            $container = $this->getDocumentContainerByPath($oldPath);
            $documentObject = $this->createDocumentFromPostValues($postValues);
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
			$documentObject = $this->createDocumentFromPostValues($postValues);
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

		private function createDocumentFromPostValues($postValues)
		{
			$postValues = utf8Convert($postValues);
			$documentType = $this->getDocumentTypeBySlug($postValues['documentType']);

			$staticBricks = $documentType->bricks;

			$documentObj = new Document();
			$documentObj->title = $postValues['title'];
			$documentObj->slug = slugify($postValues['title']);
			$documentObj->type = 'document';
			$documentObj->documentType = $documentType->title;
			$documentObj->documentTypeSlug = $documentType->slug;
			$documentObj->state = isset($postValues['state']) ? 'published' : 'unpublished';
			$documentObj->lastModificationDate = time();
			$documentObj->creationDate = isset($postValues['creationDate']) ? intval($postValues['creationDate']) : time();
			$documentObj->lastModifiedBy = $_SESSION['cloudcontrol']->username;

			$documentObj->fields = isset($postValues['fields']) ? $postValues['fields'] : array();

			$documentObj->bricks = array();
			if (isset($postValues['bricks'])) {
				foreach ($postValues['bricks'] as $brickSlug => $brick) {
					// Check if its multiple
					$multiple = false;
					$staticBrick = null;
					foreach ($staticBricks as $staticBrick) {
						if ($staticBrick->slug === $brickSlug) {
							$multiple = $staticBrick->multiple;
							break;
						}
					}

					if ($multiple) {
						$brickArray = array();
						foreach ($brick as $brickInstance) {
							$brickObj = new \stdClass();
							$brickObj->fields = new \stdClass();
							$brickObj->type = $staticBrick->brickSlug;

							foreach ($brickInstance['fields'] as $fieldName => $fieldValues) {
								$brickObj->fields->$fieldName = $fieldValues;
							}

							$brickArray[] = $brickObj;
						}

                        $bricks = $documentObj->bricks;
						$bricks[$brickSlug] = $brickArray;
                        $documentObj->bricks = $bricks;
					} else {
                        $bricks = $documentObj->bricks;
						$bricks[$brickSlug] = $brick;
                        $documentObj->bricks = $bricks;
					}
				}
			}
			$documentObj->dynamicBricks = array();
			if (isset($postValues['dynamicBricks'])) {
				foreach ($postValues['dynamicBricks'] as $brickTypeSlug => $brick) {
					foreach ($brick as $brickContent) {
						$brickObj = new \stdClass();
						$brickObj->type = $brickTypeSlug;
						$brickObj->fields = $brickContent;
                        $dynamicBricks = $documentObj->dynamicBricks;
						$dynamicBricks[] = $brickObj;
                        $documentObj->dynamicBricks = $dynamicBricks;
					}
				}
			}
			return $documentObj;
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
            $documentFolderObject = $this->createDocumentFolderFromPostValues($postValues);
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
		 * Create folder from post values
		 *
		 * @param $postValues
		 *
		 * @return \stdClass
		 * @throws \Exception
		 */
		private function createDocumentFolderFromPostValues($postValues)
		{
			if (isset($postValues['title'], $postValues['path'], $postValues['content'])) {
				$documentFolderObject = new Document();
				$documentFolderObject->title = $postValues['title'];
				$documentFolderObject->slug = slugify($postValues['title']);
				$documentFolderObject->type = 'folder';
				$documentFolderObject->content = json_decode($postValues['content']);

				return $documentFolderObject;
			} else {
				throw new \Exception('Trying to create document folder with invalid data.');
			}
		}

		/*
		 * 
		 * Sitemap
		 *
		 */
		/**
		 * @return array
		 */
		public function getSitemap()
		{
			return $this->repository->sitemap;
		}

		/**
		 * Add a sitemap item
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function addSitemapItem($postValues) 
		{
			$sitemapObject = $this->createSitemapItemFromPostValues($postValues);
			$sitemap = $this->repository->sitemap;
			$sitemap[] = $sitemapObject;
			$this->repository->sitemap = $sitemap;
			$this->save();
		}

		/**
		 * Save changes to a sitemap item
		 *
		 * @param $slug
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveSitemapItem($slug, $postValues)
		{
			$sitemapObject = $this->createSitemapItemFromPostValues($postValues);
			
			$sitemap = $this->repository->sitemap;
			foreach ($sitemap as $key => $sitemapItem) {
				if ($sitemapItem->slug == $slug) {
					$sitemap[$key] = $sitemapObject;
				}
			}
			$this->repository->sitemap = $sitemap;
			$this->save();
		}

		/**
		 * Delete a sitemap item by its slug
		 *
		 * @param $slug
		 *
		 * @throws \Exception
		 */
		public function deleteSitemapItemBySlug($slug)
		{
			$sitemap = $this->repository->sitemap;
			foreach ($sitemap as $key => $sitemapItem) {
				if ($sitemapItem->slug == $slug) {
					unset($sitemap[$key]);
				}
			}
			$sitemap = array_values($sitemap);
			$this->repository->sitemap = $sitemap;
			$this->save();
		}

		/**
		 * Create a sitemap item from post values
		 *
		 * @param $postValues
		 *
		 * @return \stdClass
		 * @throws \Exception
		 */
		private function createSitemapItemFromPostValues($postValues)
		{
			if (isset($postValues['title'], $postValues['url'], $postValues['component'], $postValues['template'])) {
				$sitemapObject = new \stdClass();
				$sitemapObject->title = $postValues['title'];
				$sitemapObject->slug = slugify($postValues['title']);
				$sitemapObject->url = $postValues['url'];
				$sitemapObject->component = $postValues['component'];
				$sitemapObject->template = $postValues['template'];
				$sitemapObject->regex = isset($postValues['regex']);
				$sitemapObject->parameters = new \stdClass();
				if (isset($postValues['parameterNames'], $postValues['parameterValues'])) {
					foreach ($postValues['parameterNames'] as $key => $value) {
						$sitemapObject->parameters->$value = $postValues['parameterValues'][$key];
					}
				}
				return $sitemapObject;
			} else {
				throw new \Exception('Trying to create sitemap item with invalid data.');
			}
		}

		/**
		 * Save changes to a sitemap item
		 *
		 * @param $postValues
		 *
		 * @throws \Exception
		 */
		public function saveSitemap($postValues)
		{
			if (isset($postValues['sitemapitem']) && is_array($postValues['sitemapitem'])) {
				$sitemap = array();
				foreach ($postValues['sitemapitem'] as $sitemapItem) {
					$sitemapItemObject = json_decode($sitemapItem);
					if (isset($sitemapItemObject->object)) {
						unset($sitemapItemObject->object);
					}
					$sitemap[] = $sitemapItemObject;
				}
				$this->repository->sitemap = $sitemap;
				$this->save();
			}
		}

		/**
		 * Get a sitemap item by its slug
		 *
		 * @param $slug
		 *
		 * @return mixed
		 */
		public function getSitemapItemBySlug($slug)
		{
			$sitemap = $this->repository->sitemap;
			foreach ($sitemap as $sitemapItem) {
				if ($sitemapItem->slug == $slug) {
					return $sitemapItem;
				}
			}
			return null;
		}

		/*
		 *
		 * Images
		 *
		 */
		/**
		 * Get all images
		 *
		 * @return array
		 */
		public function getImages()
		{
			return $this->repository->images;
		}

		public function addImage($postValues)
		{
			$destinationPath = realpath(__DIR__ . '/../../www/images/');

			$filename = $this->validateFilename($postValues['name'], $destinationPath);
			$destination = $destinationPath . '/' . $filename;

			if ($postValues['error'] != '0') {
				throw new \Exception('Error uploading file. Error code: ' . $postValues['error']);
			}

			if (move_uploaded_file($postValues['tmp_name'], $destination)) {
				$imageResizer = new ImageResizer($this->getImageSet());
				$fileNames = $imageResizer->applyImageSetToImage($destination);
				$fileNames['original'] = $filename;
				$imageObject = new \stdClass();
				$imageObject->file = $filename;
				$imageObject->type = $postValues['type'];
				$imageObject->size = $postValues['size'];
				$imageObject->set = $fileNames;

                $images = $this->repository->images;
				$images[] = $imageObject;
                $this->repository->images = $images;

				$this->save();
			} else {
				throw new \Exception('Error moving uploaded file');
			}
		}

		public function deleteImageByName($filename)
		{
			$destinationPath = realpath(__DIR__ . '/../../www/images/');

			$images = $this->getImages();

			foreach ($images as $key => $image) {
				if ($image->file == $filename) {
					foreach ($image->set as $imageSetFilename) {
						$destination = $destinationPath . '/' . $imageSetFilename;
						if (file_exists($destination)) {
							unlink($destination);
						} else {
							dump($destination);
						}
					}
					unset($images[$key]);
				}
			}

			$this->repository->images = $images;
			$this->save();
		}

		/**
		 * @param $filename
		 * @return null
         */
		public function getImageByName($filename)
		{
			$images = $this->getImages();
			foreach ($images as $image) {
				if ($image->file == $filename) {
					return $image;
				}
			}
			return null;
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
			$files =  $this->repository->files;
			usort($files, array($this, 'compareFiles'));
			return $files;
		}

		/**
		 * @return mixed
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

		private function validateFilename($filename, $path)
		{
			$fileParts = explode('.', $filename);
			if (count($fileParts) > 1) {
				$extension = end($fileParts);
				array_pop($fileParts);
				$fileNameWithoutExtension = implode('-', $fileParts);
				$fileNameWithoutExtension = slugify($fileNameWithoutExtension);
				$filename = $fileNameWithoutExtension . '.' . $extension;
			} else {
				$filename = slugify($filename);
			}

			if (file_exists($path . '/' . $filename)) {
				$fileParts = explode('.', $filename);
				if (count($fileParts) > 1) {
					$extension = end($fileParts);
					array_pop($fileParts);
					$fileNameWithoutExtension = implode('-', $fileParts);
					$fileNameWithoutExtension .= '-copy';
					$filename = $fileNameWithoutExtension . '.' . $extension;
				} else {
					$filename .= '-copy';
				}
				return $this->validateFilename($filename,$path);
			}
			return $filename;
		}

		/**
		 * @param $filename
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
			$documentTypeObject = $this->createDocumentTypeFromPostValues($postValues);

            $documentTypes = $this->repository->documentTypes;
            $documentTypes[] = $documentTypeObject;
            $this->repository->documentTypes = $documentTypes;

			$this->save();
		}

		/**
		 * Create a document type from post values
		 *
		 * @param $postValues
		 *
		 * @return \stdClass
		 * @throws \Exception
		 */
		public function createDocumentTypeFromPostValues($postValues)
		{
			if (isset($postValues['title'])) {
				$documentTypeObject = new \stdClass();
				$documentTypeObject->title = $postValues['title'];
				$documentTypeObject->slug = slugify($postValues['title']);
				$documentTypeObject->fields = array();
				$documentTypeObject->bricks = array();
				$documentTypeObject->dynamicBricks = isset($postValues['dynamicBricks']) ? $postValues['dynamicBricks'] : array();
				if (isset($postValues['fieldTitles'], $postValues['fieldTypes'], $postValues['fieldRequired'], $postValues['fieldMultiple'])) {
					foreach ($postValues['fieldTitles'] as $key => $value) {
						$fieldObject = new \stdClass();
						$fieldObject->title = $value;
						$fieldObject->slug = slugify($value);
						$fieldObject->type = $postValues['fieldTypes'][$key];
						$fieldObject->required = ($postValues['fieldRequired'][$key] === 'true');
						$fieldObject->multiple = ($postValues['fieldMultiple'][$key] === 'true');
						
						$documentTypeObject->fields[] = $fieldObject;
					}
				}
				if (isset($postValues['brickTitles'], $postValues['brickBricks'])) {
					foreach ($postValues['brickTitles'] as $key => $value) {
						$brickObject = new \stdClass();
						$brickObject->title = $value;
						$brickObject->slug = slugify($value);
						$brickObject->brickSlug = $postValues['brickBricks'][$key];
						$brickObject->multiple = ($postValues['brickMultiples'][$key] === 'true');

						$documentTypeObject->bricks[] = $brickObject;
					}
				}
				return $documentTypeObject;
			} else {
				throw new \Exception('Trying to create document type with invalid data.');
			}
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
			$documentTypeObject = $this->createDocumentTypeFromPostValues($postValues);
			
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
			$brickObject = $this->createBrickFromPostValues($postValues);

            $bricks = $this->repository->bricks;
            $bricks[] = $brickObject;
            $this->repository->bricks = $bricks;

			$this->save();
		}

		/**
		 * Create a brick from post values
		 *
		 * @param $postValues
		 *
		 * @return \stdClass
		 * @throws \Exception
		 */
		public function createBrickFromPostValues($postValues)
		{
			if (isset($postValues['title'])) {
				$brickObject = new \stdClass();
				$brickObject->title = $postValues['title'];
				$brickObject->slug = slugify($postValues['title']);
				$brickObject->fields = array();
				if (isset($postValues['fieldTitles'], $postValues['fieldTypes'], $postValues['fieldRequired'], $postValues['fieldMultiple'])) {
					foreach ($postValues['fieldTitles'] as $key => $value) {
						$fieldObject = new \stdClass();
						$fieldObject->title = $value;
						$fieldObject->slug = slugify($value);
						$fieldObject->type = $postValues['fieldTypes'][$key];
						$fieldObject->required = ($postValues['fieldRequired'][$key] === 'true');
						$fieldObject->multiple = ($postValues['fieldMultiple'][$key] === 'true');
						
						$brickObject->fields[] = $fieldObject;
					}
				}
				return $brickObject;
			} else {
				throw new \Exception('Trying to create document type with invalid data.');
			}
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
			$brickObject = $this->createBrickFromPostValues($postValues);
			
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
		private function save() {
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
			$imageSetObject = $this->createImageSetFromPostValues($postValues);

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
		 * Ceate image set from post values
		 *
		 * @param $postValues
		 *
		 * @return \stdClass
		 * @throws \Exception
		 */
		private function createImageSetFromPostValues($postValues)
		{
			if (isset($postValues['title'], $postValues['width'], $postValues['height'], $postValues['method'])) {
				$imageSetObject = new \stdClass();

				$imageSetObject->title = $postValues['title'];
				$imageSetObject->slug = slugify($postValues['title']);
				$imageSetObject->width = $postValues['width'];
				$imageSetObject->height = $postValues['height'];
				$imageSetObject->method = $postValues['method'];

				return $imageSetObject;
			} else {
				throw new \Exception('Trying to create image set with invalid data.');
			}
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
			$imageSetObject = $this->createImageSetFromPostValues($postValues);

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
			$applicationComponent = $this->createApplicationComponentFromPostValues($postValues);
			$applicationComponents = $this->repository->applicationComponents;
			$applicationComponents[] = $applicationComponent;
			$this->repository->applicationComponents = $applicationComponents;

			$this->save();
		}

		private function createApplicationComponentFromPostValues($postValues)
		{
			if (isset($postValues['title'], $postValues['component'])) {
				$applicationComponent = new \stdClass();
				$applicationComponent->title = $postValues['title'];
				$applicationComponent->slug = slugify($postValues['title']);
				$applicationComponent->component = $postValues['component'];
				$applicationComponent->parameters = new \stdClass();
				if (isset($postValues['parameterNames'], $postValues['parameterValues'])) {
					foreach ($postValues['parameterNames'] as $key => $value) {
						$applicationComponent->parameters->$value = $postValues['parameterValues'][$key];
					}
				}
				return $applicationComponent;
			} else {
				throw new \Exception('Trying to create application component with invalid data.');
			}
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
			$newApplicationComponent = $this->createApplicationComponentFromPostValues($postValues);

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