<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 16:24
 */

namespace CloudControl\Cms\storage\factories;

use CloudControl\Cms\cc\StringUtil;
use CloudControl\Cms\storage\entities\Document;
use CloudControl\Cms\storage\storage\DocumentTypesStorage;

class DocumentFactory
{
    /**
     * @param array $postValues
     * @param DocumentTypesStorage $documentTypesStorage
     *
     * @return \CloudControl\Cms\storage\entities\Document
     */
    public static function createDocumentFromPostValues($postValues, DocumentTypesStorage $documentTypesStorage)
    {
        $postValues = utf8Convert($postValues);
        $documentType = $documentTypesStorage->getDocumentTypeBySlug($postValues['documentType']);

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
        $documentObj->slug = StringUtil::slugify($postValues['title']);
        $documentObj->type = 'document';
        $documentObj->documentType = $documentType->title;
        $documentObj->documentTypeSlug = $documentType->slug;
        $documentObj->state = isset($postValues['state']) ? 'published' : 'unpublished';
        $documentObj->lastModificationDate = time();
        $documentObj->creationDate = isset($postValues['creationDate']) ? (int)$postValues['creationDate'] : time();
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
                // Find the current bricktype and check if its multiple
                list($staticBrick, $multiple) = self::getStaticBrickAndSetMultiple($staticBricks, $brickSlug);

                if ($multiple) {
                    $documentObj = self::addMultipleBricks($documentObj, $brick, $staticBrick, $brickSlug);
                } else {
                    $documentObj = self::addSingleBrick($documentObj, $brick, $brickSlug);
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

    /**
     * @param $staticBricks
     * @param $brickSlug
     *
     * @return array
     */
    private static function getStaticBrickAndSetMultiple($staticBricks, $brickSlug)
    {
        $staticBrick = null;
        $multiple = false;
        foreach ($staticBricks as $staticBrick) {
            if ($staticBrick->slug === $brickSlug) {
                $multiple = $staticBrick->multiple;
                break;
            }
        }

        return array($staticBrick, $multiple);
    }

    /**
     * @param $staticBrick
     * @param $brickInstance
     *
     * @return \stdClass
     */
    private static function createBrick($staticBrick, $brickInstance)
    {
        $brickObj = new \stdClass();
        $brickObj->fields = new \stdClass();
        $brickObj->type = $staticBrick->brickSlug;

        foreach ($brickInstance['fields'] as $fieldName => $fieldValues) {
            $brickObj->fields->$fieldName = $fieldValues;
        }

        return $brickObj;
    }

    /**
     * @param $documentObj
     * @param $brick
     * @param $staticBrick
     * @param $brickSlug
     *
     * @return mixed
     */
    private static function addMultipleBricks($documentObj, $brick, $staticBrick, $brickSlug)
    {
        $brickArray = array();
        foreach ($brick as $brickInstance) {
            $brickObj = self::createBrick($staticBrick, $brickInstance);
            $brickArray[] = $brickObj;
        }

        $bricks = $documentObj->bricks;
        $bricks[$brickSlug] = $brickArray;
        $documentObj->bricks = $bricks;

        return $documentObj;
    }

    /**
     * @param $documentObj
     * @param $brick
     * @param $brickSlug
     *
     * @return mixed
     */
    private static function addSingleBrick($documentObj, $brick, $brickSlug)
    {
        $bricks = $documentObj->bricks;
        $bricks[$brickSlug] = $brick;
        $documentObj->bricks = $bricks;
        return $documentObj;
    }
}