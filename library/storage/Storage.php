<?php
namespace library\storage
{
	/**
	 * Interface Storage
	 * @package library\storage
	 */
	interface Storage
	{
		function getApplicationComponents();
		
		function getUserByUsername($username);

		function getDocuments();
		function getDocumentBySlug($slug);
		function saveDocument($postValues);
		function addDocument($postValues);
		function deleteDocumentBySlug($slug);

		function addDocumentFolder($postValues);
		function deleteDocumentFolderBySlug($slug);
		function getDocumentFolderBySlug($slug);
		function saveDocumentFolder($postValues);

		function getSitemap();
		function addSitemapItem($postValues);
		function saveSitemapItem($slug, $postValues);
		function saveSitemap($postValues);
		function getSitemapItemBySlug($slug);
		function deleteSitemapItemBySlug($slug);

		function getImages();
		function addImage($postValues);
		function deleteImageByName($filename);
		function getImageByName($filename);

		function getFiles();
		function addFile($postValues);
		function getFileByName($filename);
		function deleteFileByName($filename);

		function getDocumentTypes();
		function addDocumentType($postValues);
		function deleteDocumentTypeBySlug($slug);
		function getDocumentTypeBySlug($slug, $getBricks);
		function saveDocumentType($slug, $postValues);
		
		function getBricks();
		function addBrick($postValues);
		function getBrickBySlug($slug);
		function saveBrick($slug, $postValues);
		function deleteBrickBySlug($slug);

		function getImageSet();
		function getImageSetBySlug($slug);
		function saveImageSet($slug, $postValues);
		function addImageSet($postValues);
		function deleteImageSetBySlug($slug);
		function getSmallestImageSet();
	}
}