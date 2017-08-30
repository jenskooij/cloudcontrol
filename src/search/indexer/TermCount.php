<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 10:22
 */

namespace CloudControl\Cms\search\indexer;


use CloudControl\Cms\search\DocumentTokenizer;
use CloudControl\Cms\search\Indexer;
use CloudControl\Cms\storage\Storage;

class TermCount
{
    /**
     * @var \PDO
     */
    protected $dbHandle;
    protected $documents;
    protected $filters;
    protected $storage;

    /**
     * TermCount constructor.
     *
     * @param \PDO $dbHandle
     * @param array $documents
     * @param array $filters
     * @param Storage $jsonStorage
     */
    public function __construct($dbHandle, $documents, $filters, $jsonStorage)
    {
        $this->dbHandle = $dbHandle;
        $this->documents = $documents;
        $this->filters = $filters;
        $this->storage = $jsonStorage;
    }

    public function execute()
    {
        $this->iterateDocumentsAndCreateTermCount($this->documents);
    }

    protected function applyFilters($tokens)
    {
        foreach ($this->filters as $filterName) {
            $filterClassName = '\CloudControl\Cms\search\filters\\' . $filterName;
            $filter = new $filterClassName($tokens);
            $tokens = $filter->getFilterResults();
        }
        return $tokens;
    }

    protected function storeDocumentTermCount($document, $documentTermCount)
    {
        $db = $this->dbHandle;
        $sqlStart = '
			INSERT INTO `term_count` (`documentPath`, `term`, `count`, `field`)
				 VALUES ';
        $sql = $sqlStart;
        $values = array();
        $quotedDocumentPath = $db->quote($document->path);
        $i = 0;
        foreach ($documentTermCount as $field => $countArray) {
            $quotedField = $db->quote($field);
            foreach ($countArray as $term => $count) {
                $values[] = $quotedDocumentPath . ', ' . $db->quote($term) . ', ' . $db->quote($count) . ', ' . $quotedField;
                $i += 1;
                if ($i >= Indexer::SQLITE_MAX_COMPOUND_SELECT) {
                    $this->executeStoreDocumentTermCount($values, $sql, $db);
                    $values = array();
                    $sql = $sqlStart;
                    $i = 0;
                }
            }
        }
        if (count($values) != 0) {
            $this->executeStoreDocumentTermCount($values, $sql, $db);
        }
    }

    /**
     * @param $values
     * @param $sql
     * @param $db
     *
     * @throws \Exception
     */
    protected function executeStoreDocumentTermCount($values, $sql, $db)
    {
        $sql .= '(' . implode('),' . PHP_EOL . '(', $values) . ');';
        $stmt = $db->prepare($sql);
        if ($stmt === false || !$stmt->execute()) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
    }

    /**
     * @param $document
     */
    private function createTermCountForDocument($document)
    {
        $tokenizer = new DocumentTokenizer($document, $this->storage);
        $tokens = $tokenizer->getTokens();
        $documentTermCount = $this->applyFilters($tokens);
        $this->storeDocumentTermCount($document, $documentTermCount);
    }

    /**
     * @param $documents
     */
    private function iterateDocumentsAndCreateTermCount($documents)
    {
        foreach ($documents as $document) {
            if ($document->type === 'folder') {
                $this->iterateDocumentsAndCreateTermCount($document->content);
            } else {
                $this->createTermCountForDocument($document);
            }
        }
    }
}