<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 17:03
 */

namespace library\storage\factories;


use library\cc\StringUtil;
use library\storage\Document;

class DocumentFolderFactory
{
	/**
	 * Create folder from post values
	 *
	 * @param $postValues
	 *
	 * @return Document
	 * @throws \Exception
	 */
	public static function createDocumentFolderFromPostValues($postValues)
	{
		if (isset($postValues['title'], $postValues['path'], $postValues['content'])) {
			$documentFolderObject = new Document();
			$documentFolderObject->title = $postValues['title'];
			$documentFolderObject->slug = StringUtil::slugify($postValues['title']);
			$documentFolderObject->type = 'folder';
			$documentFolderObject->content = json_decode($postValues['content']);

			return $documentFolderObject;
		} else {
			throw new \Exception('Trying to create document folder with invalid data.');
		}
	}
}