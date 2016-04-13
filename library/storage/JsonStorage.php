<?php
namespace library\storage
{
	/**
	 * Class JsonStorage
	 * @package library\storage
	 */
	class JsonStorage implements Storage
	{
		private $storagePath;
		private $repository;

		/**
		 * JsonStorage constructor.
		 *
		 * @param $storagePath
		 */
		public function __construct($storagePath)
		{
			$this->storagePath = $storagePath;
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
			$storagePath = __DIR__ . $this->storagePath;
			if (realpath($storagePath) !== false) {
				$jsonString = file_get_contents($storagePath);
				$this->repository = json_decode($jsonString);
			} else {
				throw new \Exception('Couldnt find storagePath ' . $storagePath);
			}
		}

		/**
		 * @return array
		 */
		public function getApplicationComponents()
		{
			return $this->repository->applicationComponents;
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
			return $this->repository->documents;
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
			if ($postValues['path'] == '' || $postValues['path'] == '/') {
				// Check folder duplicate child
				foreach ($this->repository->documents as $document) {
					if ($document->slug == $documentFolderObject->slug && $document->type == 'folder') {
						// TODO make it so it doesnt throw an exception, but instead shows a warning
						throw new \Exception('Duplicate slug: ' . $document->slug . ' in folder ' . $postValues['path']);
					}
				}
				$this->repository->documents[] = $documentFolderObject;
			} else {
				$documentContainer = $this->getDocumentContainerByPath($postValues['path']);
				$documentContainerArray = $documentContainer['indices'];
				$containerFolder = $documentContainer['containerFolder'];
				$folder = $this->repository->documents;
				foreach ($documentContainerArray as $index) {
					if ($folder === $this->repository->documents) {
						$folder = $folder[$index];
					} else {
						$folder = $folder->content[$index];
					}

				}
				// Check folder duplicate child
				foreach ($containerFolder->content as $document) {
					if ($document->slug == $documentFolderObject->slug && $document->type == 'folder') {
						// TODO make it so it doesnt throw an exception, but instead shows a warning
						throw new \Exception('Duplicate slug: ' . $document->slug . ' in folder ' . $postValues['path']);
					}
				}
				$folder->content[] = $documentFolderObject;
			}
			$this->save();
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
			$documentContainer = $this->getDocumentContainerByPath($slug);
			$indices = $documentContainer['indices'];

			$folder = $this->repository->documents;
			$previousFolder = $this->repository->documents;
			foreach ($indices as $index) {
				if ($folder === $this->repository->documents) {
					$folder = $folder[$index];
				} else {
					$previousFolder = $folder;
					$folder = $folder->content[$index];
				}
			}

			if ($previousFolder === $this->repository->documents) {
				unset($this->repository->documents[end($indices)]);
				$this->repository->documents = array_values($this->repository->documents);
			} else {
				unset($previousFolder->content[end($indices)]);
				$previousFolder->content = array_values($previousFolder->content);
			}

			$this->save();
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
			$documentContainer = $this->getDocumentContainerByPath('/' . $slug);
			$indices = $documentContainer['indices'];

			$folder = $this->repository->documents;
			$previousFolder = $this->repository->documents;
			foreach ($indices as $index) {
				if ($folder === $this->repository->documents) {
					$folder = $folder[$index];
				} else {
					$previousFolder = $folder;
					$folder = $folder->content[$index];
				}
			}

			return $folder;
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
			$documentFolderObject = $this->createDocumentFolderFromPostValues($postValues);

			$documentContainer = $this->getDocumentContainerByPath($_GET['slug']);
			$indices = $documentContainer['indices'];

			$folder = $this->repository->documents;
			$previousFolder = $this->repository->documents;
			foreach ($indices as $index) {
				if ($folder === $this->repository->documents) {
					$folder = $folder[$index];
				} else {
					$previousFolder = $folder;
					$folder = $folder->content[$index];
				}
			}

			if ($previousFolder === $this->repository->documents) {
				// Check for duplicates
				foreach ($this->repository->documents as $index => $document) {
					if (end($indices) !== $index && $document->slug == $documentFolderObject->slug && $document->type == 'folder') {
						throw new \Exception('Duplicate slug: ' . $document->slug . ' in folder ' . $postValues['path']);
					}
				}
				$this->repository->documents[end($indices)] = $documentFolderObject;
			} else {
				// Check for duplicates
				foreach ($previousFolder->content as $index => $document) {
					if (end($indices) !== $index && $document->slug == $documentFolderObject->slug && $document->type == 'folder') {
						throw new \Exception('Duplicate slug: ' . $document->slug . ' in folder ' . $postValues['path']);
					}
				}
				$previousFolder->content[end($indices)] = $documentFolderObject ;
			}

			$this->save();
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
			$slugs = explode('/', $path);
			$slugs = array_filter($slugs);
			end($slugs);
			$lastKey = key($slugs);
			$root = $this->repository->documents;
			$i = 0;
			$returnArray = array();
			$noMatches = 0;
			$foundDocument = null;
			foreach ($slugs as $slug) {
				$matched = false;
				$previousDocument = null;
				end($root);
				$lastSubKey = key($root);
				foreach ($root as $index => $document) {
					if ($slug == $document->slug) {
						if ($i != $lastKey && $document->type == 'folder') {
							$returnArray[] = $index;
							$root = $root[$index]->content;
							$matched = true;
						} else {
							$foundDocument = $document;
							$returnArray[] = $index;
							$matched = true;
						}
					}

					if ($lastSubKey != $index) {
						$previousDocument = $document;
					}
				}
				if ($matched == true) {
					$noMatches += 1;
				} else {
					throw new \Exception('Unknown folder "' . $slug . '" in path: ' . $path);
				}
				$i += 1;
			}
			if ($noMatches > 0) {
				return array(
					'containerFolder' => $document,
					'indices' => $returnArray,
					'document' => $foundDocument,
					'previousDocument' => $previousDocument
				);
			} else {
				throw new \Exception('Invalid path: ' . $path);
			}
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
				$documentFolderObject = new \stdClass();
				$documentFolderObject->title = $postValues['title'];
				$documentFolderObject->slug = $this->slugify($postValues['title']);
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
			
			$this->repository->sitemap[] = $sitemapObject;
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
				$sitemapObject->slug = $this->slugify($postValues['title']);
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
			
			$this->repository->documentTypes[] = $documentTypeObject;
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
				$documentTypeObject->slug = $this->slugify($postValues['title']);
				$documentTypeObject->fields = array();
				$documentTypeObject->bricks = array();
				$documentTypeObject->dynamicBricks = isset($postValues['dynamicBricks']) ? $postValues['dynamicBricks'] : array();
				if (isset($postValues['fieldTitles'], $postValues['fieldTypes'], $postValues['fieldRequired'], $postValues['fieldMultiple'])) {
					foreach ($postValues['fieldTitles'] as $key => $value) {
						$fieldObject = new \stdClass();
						$fieldObject->title = $value;
						$fieldObject->slug = $this->slugify($value);
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
						$brickObject->brickSlug = $postValues['brickBricks'][$key];
						
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
		 * @param $slug
		 *
		 * @return mixed
		 */
		public function getDocumentTypeBySlug($slug)
		{
			$documentTypes = $this->repository->documentTypes;
			foreach ($documentTypes as $documentType) {
				if ($documentType->slug == $slug) {
					return $documentType;
				}
			}
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
			
			$this->repository->bricks[] = $brickObject;
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
				$brickObject->slug = $this->slugify($postValues['title']);
				$brickObject->fields = array();
				if (isset($postValues['fieldTitles'], $postValues['fieldTypes'], $postValues['fieldRequired'], $postValues['fieldMultiple'])) {
					foreach ($postValues['fieldTitles'] as $key => $value) {
						$fieldObject = new \stdClass();
						$fieldObject->title = $value;
						$fieldObject->slug = $this->slugify($value);
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
		 * in the storagepath
		 *
		 * @throws \Exception
		 */
		private function save() {
			$storagePath = __DIR__ . $this->storagePath;
			if (realpath($storagePath) !== false) {
				file_put_contents($storagePath, json_encode($this->repository));
			} else {
				throw new \Exception('Couldnt find storagePath ' . $storagePath);
			}
		}

		/**
		 * Convert a string to url friendly slug
		 *
		 * @param string $str
		 * @param array  $replace
		 * @param string $delimiter
		 *
		 * @return mixed|string
		 */
		private function slugify($str, $replace=array(), $delimiter='-') {
			if( !empty($replace) ) {
				$str = str_replace((array)$replace, ' ', $str);
			}

			$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
			$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

			return $clean;
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
				$imageSetObject->slug = $this->slugify($postValues['title']);
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

			$this->repository->imageSet[] = $imageSetObject;

			$this->save();
		}

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
	}
}