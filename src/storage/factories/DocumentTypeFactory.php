<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 16:54
 */

namespace CloudControl\Cms\storage\factories;


use CloudControl\Cms\cc\StringUtil;

class DocumentTypeFactory extends AbstractBricksFactory
{
    public static function createDocumentTypeFromPostValues($postValues)
    {
        if (isset($postValues['title'])) {
            $documentTypeObject = self::createInitialDocoumentTypeObject($postValues);
            if (isset($postValues['fieldTitles'], $postValues['fieldTypes'], $postValues['fieldRequired'], $postValues['fieldMultiple'])) {
                foreach ($postValues['fieldTitles'] as $key => $value) {
                    $fieldObject = self::createFieldObject($postValues, $value, $key);

                    $documentTypeObject->fields[] = $fieldObject;
                }
            }
            if (isset($postValues['brickTitles'], $postValues['brickBricks'])) {
                foreach ($postValues['brickTitles'] as $key => $value) {
                    $brickObject = self::createBrickObject($postValues, $value, $key);

                    $documentTypeObject->bricks[] = $brickObject;
                }
            }

            return $documentTypeObject;
        } else {
            throw new \Exception('Trying to create document type with invalid data.');
        }
    }

    /**
     * @param $postValues
     * @param $title
     * @param $slug
     *
     * @return \stdClass
     */
    private static function createBrickObject($postValues, $title, $slug)
    {
        $brickObject = new \stdClass();
        $brickObject->title = $title;
        $brickObject->slug = StringUtil::slugify($title);
        $brickObject->brickSlug = $postValues['brickBricks'][$slug];
        $brickObject->multiple = ($postValues['brickMultiples'][$slug] === 'true');

        return $brickObject;
    }

    private static function createInitialDocoumentTypeObject($postValues)
    {
        $documentTypeObject = new \stdClass();
        $documentTypeObject->title = $postValues['title'];
        $documentTypeObject->slug = StringUtil::slugify($postValues['title']);
        $documentTypeObject->fields = array();
        $documentTypeObject->bricks = array();
        $documentTypeObject->dynamicBricks = isset($postValues['dynamicBricks']) ? $postValues['dynamicBricks'] : array();

        return $documentTypeObject;
    }
}