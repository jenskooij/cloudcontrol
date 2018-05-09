<?php
/**
 * Created by jensk on 23-4-2018.
 */

namespace CloudControl\Cms\util;


use CloudControl\Cms\storage\entities\Document;

class DocumentSorter
{
    protected static $orderByField;
    protected static $order = 'ASC';

    /**
     * Sorts an array of Document instances
     * @param array $documents
     * @param string $field
     * @param string $order
     * @return array
     */
    public static function sortDocumentsByField($documents, $field, $order = 'ASC')
    {
        self::$orderByField = $field;
        self::$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        usort($documents, '\CloudControl\Cms\util\DocumentSorter::fieldCompare');
        if ($order === 'DESC') {
            return array_reverse($documents);
        }
        return $documents;
    }

    /**
     * Compares two documents
     * @param Document $a
     * @param Document $b
     * @return int
     */
    protected static function fieldCompare(Document $a, Document $b) {
        $field = self::$orderByField;
        if (property_exists('\CloudControl\Cms\storage\entities\Document', $field)) {
            return strcasecmp($a->{$field}, $b->{$field});
        }

        if (!isset($a->fields->{$field}[0])) {
            return -3;
        }

        if (!isset($b->fields->{$field}[0])) {
            return 3;
        }

        return strcasecmp($a->fields->{$field}[0], $b->fields->{$field}[0]);
    }
}