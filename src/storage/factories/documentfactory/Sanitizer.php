<?php
/**
 * Created by: Jens
 * Date: 3-4-2018
 */

namespace CloudControl\Cms\storage\factories\documentfactory;


use HTMLPurifier;
use HTMLPurifier_Config;

class Sanitizer
{
    private static $purifier;

    /**
     * @param $postValues
     * @param $documentType
     * @return array
     */
    public static function sanitizeFields($postValues, $documentType)
    {
        $fields = array();
        $purifier = self::getPurifier();

        if (isset($postValues['fields'])) {
            foreach ($postValues['fields'] as $fieldNameSlug => $field) {
                $postValues = self::sanitizeField($postValues, $documentType, $fieldNameSlug, $field, $purifier);
            }
            $fields = $postValues['fields'];
        }
        return $fields;
    }

    /**
     * @return HTMLPurifier
     */
    public static function getPurifier()
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }
        $config = HTMLPurifier_Config::createDefault();
        $config->set('URI.DisableExternalResources', false);
        $config->set('URI.DisableResources', false);
        $config->set('HTML.Allowed',
            'u,p,b,i,a,p,strong,em,li,ul,ol,div[align],br,img,table,tr,td,th,tbody,thead,strike,sub,sup,iframe');
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp',
            '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo
        $config->set('Attr.AllowedFrameTargets', array('_blank'));
        $config->set('HTML.AllowedAttributes', 'src, alt, href, target, frameborder, data-original');
        $config->set('URI.AllowedSchemes', array('data' => true, 'http' => true, 'https' => true));
        $config->set('Cache.DefinitionImpl', null); // remove this later!
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('img', 'data-original', 'Text');
        self::$purifier = new HTMLPurifier($config);
        return self::$purifier;
    }

    /**
     * @param $brickContent
     * @return mixed
     */
    public static function sanitizeBrickContent($brickContent)
    {
        $purifier = self::getPurifier();
        foreach ($brickContent as $fieldKey => $fieldValues) {
            foreach ($fieldValues as $valueKey => $value) {
                $fieldValues[$valueKey] = $purifier->purify($value);
            }
            $brickContent[$fieldKey] = $fieldValues;
        }
        return $brickContent;
    }

    /**
     * @param $fieldNameSlug
     * @param $documentType
     * @return bool
     */
    private static function isRichTextField($fieldNameSlug, $documentType)
    {
        foreach ($documentType->fields as $fieldObj) {
            if ($fieldObj->slug === $fieldNameSlug && $fieldObj->type === 'Rich Text') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $postValues
     * @param \stdClass $documentType
     * @param string $fieldNameSlug
     * @param array $field
     * @param HTMLPurifier $purifier
     * @return array
     */
    protected static function sanitizeField($postValues, $documentType, $fieldNameSlug, $field, $purifier)
    {
        if (self::isRichTextField($fieldNameSlug, $documentType)) {
            foreach ($field as $fieldKey => $value) {
                $newValue = $purifier->purify($value);
                $field[$fieldKey] = $newValue;
            }
            $postValues['fields'][$fieldNameSlug] = $field;
        }
        return $postValues;
    }
}