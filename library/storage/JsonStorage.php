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
		
		public function __construct($storagePath)
		{
			$this->storagePath = $storagePath;
			$this->config();
		}
		
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
		
		public function getApplicationComponents()
		{
			return $this->repository->applicationComponents;
		}
		
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
		 */
		function getDocuments()
		{
			return $this->repository->documents;
		}


		/*
		 * 
		 * Sitemap
		 *
		 */
		
		public function getSitemap()
		{
			return $this->repository->sitemap;
		}
		
		public function addSitemapItem($postValues) 
		{
			$sitemapObject = $this->createSitemapItemFromPostValues($postValues);
			
			$this->repository->sitemap[] = $sitemapObject;
			$this->save();
		}
		
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
		public function getDocumentTypes()
		{
			return $this->repository->documentTypes;
		}
		
		public function addDocumentType($postValues)
		{
			$documentTypeObject = $this->createDocumentTypeFromPostValues($postValues);
			
			$this->repository->documentTypes[] = $documentTypeObject;
			$this->save();
		}
		
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
		
		
		public function getDocumentTypeBySlug($slug)
		{
			$documentTypes = $this->repository->documentTypes;
			foreach ($documentTypes as $documentType) {
				if ($documentType->slug == $slug) {
					return $documentType;
				}
			}
		}
		
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
		public function getBricks()
		{
			return $this->repository->bricks;
		}
		
		public function addBrick($postValues)
		{
			$brickObject = $this->createBrickFromPostValues($postValues);
			
			$this->repository->bricks[] = $brickObject;
			$this->save();
		}
		
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
		
		public function getBrickBySlug($slug)
		{
			$bricks = $this->repository->bricks;
			foreach ($bricks as $brick) {
				if ($brick->slug == $slug) {
					return $brick;
				}
			}
		}
		
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
		private function save() {
			$storagePath = __DIR__ . $this->storagePath;
			if (realpath($storagePath) !== false) {
				file_put_contents($storagePath, json_encode($this->repository));
			} else {
				throw new \Exception('Couldnt find storagePath ' . $storagePath);
			}
		}
		
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
	}
}