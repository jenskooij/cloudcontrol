<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 16:24
 */

namespace library\storage;


class DocumentFactory
{
	/**
	 * @param array   $postValues
	 * @param Storage $jsonStorage
	 *
	 * @return \library\storage\Document
	 */
	public static function createDocumentFromPostValues($postValues, $jsonStorage)
	{
		$postValues = utf8Convert($postValues);
		$documentType = $jsonStorage->getDocumentTypeBySlug($postValues['documentType']);

		$staticBricks = $documentType->bricks;

		$documentObj = self::createInitialDocumentObject($postValues, $documentType);

		$documentObj->fields = isset($postValues['fields']) ? $postValues['fields'] : array();
		$documentObj->bricks = array();

		$documentObj = self::createBrickArrayForDocument($postValues, $documentObj, $staticBricks);
		$documentObj = self::createDynamicBrickArrayForDocument($postValues, $documentObj);

		return $documentObj;
	}

	/**
	 * @param array $postValues
	 * @param \stdClass $documentType
	 *
	 * @return Document
	 */
	private static function createInitialDocumentObject($postValues, $documentType)
	{
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

		return $documentObj;
	}

	/**
	 * @param array $postValues
	 * @param Document $documentObj
	 * @param array $staticBricks
	 *
	 * @return Document
	 */
	private static function createBrickArrayForDocument($postValues, $documentObj, $staticBricks)
	{
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
		return $documentObj;
	}

	/**
	 * @param array $postValues
	 * @param Document $documentObj
	 *
	 * @return Document
	 */
	private static function createDynamicBrickArrayForDocument($postValues, $documentObj)
	{
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
}