<?php
namespace library\storage
{
	interface Storage
	{
		function getApplicationComponents();
		
		function getUserByUsername($username);
		
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
	}
}