<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 13:21
 */

namespace CloudControl\Cms\search\indexer;

use CloudControl\Cms\search\Indexer;


/**
 * Class TermFieldLengthNorm
 * Formula = norm(d) = 1 / √numTerms
 *
 * @package CloudControl\Cms\search\indexer
 */
class TermFieldLengthNorm
{
    /**
     * @var \PDO
     */
    protected $dbHandle;

    /**
     * TermFieldLengthNorm constructor.
     *
     * @param \PDO $dbHandle
     */
    public function __construct($dbHandle)
    {
        $this->dbHandle = $dbHandle;
    }

    public function execute()
    {
        $db = $this->dbHandle;
        $db->/** @scrutinizer ignore-call */
        sqliteCreateFunction('sqrt', 'sqrt', 1);
        $stmt = $this->getStatement($db);
        $uniqueFieldsPerDocument = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $values = array();
        $i = 0;
        foreach ($uniqueFieldsPerDocument as $fieldRow) {
            $values[] = 'UPDATE term_frequency SET termNorm = 1/sqrt(' . (int)$fieldRow->termCount . ') WHERE documentPath = ' . $db->quote($fieldRow->documentPath) . ' AND field = ' . $db->quote($fieldRow->field) . ';';
            $i += 1;
            if ($i >= Indexer::SQLITE_MAX_COMPOUND_SELECT) {
                $this->executeUpdateTermNorm($values, $db);
                $values = array();
                $i = 0;
            }
        }
        if (count($values) !== 0) {
            $this->executeUpdateTermNorm($values, $db);
        }
    }

    /**
     * @param array $values
     * @param \PDO $db
     * @throws \Exception
     */
    private function executeUpdateTermNorm($values, $db)
    {
        $sql = 'BEGIN TRANSACTION;' . PHP_EOL;
        $sql .= implode(PHP_EOL, $values) . PHP_EOL;
        $sql .= 'COMMIT;';
        if (($db->exec($sql)) === false) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
    }

    /**
     * @param \PDO $db
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function getStatement($db)
    {
        $sql = '
		SELECT documentPath, field, COUNT(`count`) AS termCount
		  FROM term_count
	  GROUP BY documentPath, field
		';
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
        if (($stmt->execute()) === false) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
        return $stmt;
    }
}