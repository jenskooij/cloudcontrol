<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:44
 */

namespace CloudControl\Cms\search;

class CharacterFilter
{
    protected $originalString;
    protected $filteredString = '';

    /**
     * CharacterFilter constructor.
     *
     * @param $string
     */
    public function __construct($string)
    {
        $this->originalString = $string;
        $string = $this->convertToUTF8($string);
        $string = mb_strtolower($string);
        $string = $this->filterSpecialCharacters($string);
        $this->filteredString = $string;
    }

    /**
     * Returns the filtered string
     * @return string|void
     */
    public function __toString()
    {
        return $this->filteredString;
    }

    /**
     * Filter out all special characters, like punctuation and characters with accents
     *
     * @param $string
     *
     * @return mixed|string
     */
    private function filterSpecialCharacters($string)
    {
        $string = str_replace('<', ' <', $string); // This is need, otherwise this: <h1>something</h1><h2>something</h2> will result in somethingsomething
        $string = strip_tags($string);
        $string = trim($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string); // Remove special alphanumeric characters
        $string = str_replace(array('+', '=', '!', ',', '.', ';', ':', '?'), ' ', $string); // Replace sentence breaking charaters with spaces
        $string = preg_replace("/[\r\n]+/", " ", $string); // Replace multiple newlines with a single space.
        $string = preg_replace("/[\t]+/", " ", $string); // Replace multiple tabs with a single space.
        $string = preg_replace("/[^a-zA-Z0-9 ]/", '', $string); // Filter out everything that is not alphanumeric or a space
        $string = preg_replace('!\s+!', ' ', $string); // Replace multiple spaces with a single space
        return $string;
    }

    /**
     * Convert the string to UTF-8 encoding
     * @param $string
     *
     * @return string
     */
    private function convertToUTF8($string)
    {
        $encoding = mb_detect_encoding($string, mb_detect_order(), false);

        if ($encoding == "UTF-8") {
            $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        }

        $out = iconv(mb_detect_encoding($string, mb_detect_order(), false), "UTF-8//IGNORE", $string);
        return $out;
    }

    /**
     * @return mixed|string
     */
    public function getFilteredString()
    {
        return $this->filteredString;
    }
}