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

		function getSitemap();
		function addSitemapItem($postValues);
		function saveSitemapItem($slug, $postValues);
		function saveSitemap($postValues);
		function getSitemapItemBySlug($slug);
		function deleteSitemapItemBySlug($slug);
		
		function getDocumentTypes();
		function addDocumentType($postValues);
		function deleteDocumentTypeBySlug($slug);
		function getDocumentTypeBySlug($slug);
		function saveDocumentType($slug, $postValues);
		
		function getBricks();
		function addBrick($postValues);
		function getBrickBySlug($slug);
		function saveBrick($slug, $postValues);
		function deleteBrickBySlug($slug);
	}
}