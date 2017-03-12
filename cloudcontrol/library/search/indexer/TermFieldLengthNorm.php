<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 13:21
 */

namespace library\search\indexer;
use library\search\Indexer;


/**
 * Class TermFieldLengthNorm
 * Formula = norm(d) = 1 / √numTerms
 *
 * @package library\search\indexer
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
		$db->sqliteCreateFunction('sqrt', 'sqrt', 1);
		$sql = '
		SELECT documentPath, field, COUNT(`count`) as termCount
		  FROM term_count
	  GROUP BY documentPath, field
		';
		$stmt = $db->prepare($sql);
		if ($stmt === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		if (($stmt->execute()) === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$uniqueFieldsPerDocument = $stmt->fetchAll(\PDO::FETCH_OBJ);
		$values = array();
		$i = 0;
		foreach ($uniqueFieldsPerDocument as $fieldRow) {
			$values[] = 'UPDATE term_frequency SET termNorm = 1/sqrt(' . intval($fieldRow->termCount) . ') WHERE documentPath = ' . $db->quote($fieldRow->documentPath) . ' AND field = ' . $db->quote($fieldRow->field) . ';';
			$i += 1;
			if ($i >= Indexer::SQLITE_MAX_COMPOUND_SELECT) {
				$this->executeUpdateTermNorm($values, $db);
				$values = array();
				$i = 0;
			}
		}
		if (count($values) != 0) {
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
		$sql  = 'BEGIN TRANSACTION;' . PHP_EOL;
		$sql .= implode(PHP_EOL, $values) . PHP_EOL;
		$sql .= 'COMMIT;';
		if (($db->exec($sql)) === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
	}
}