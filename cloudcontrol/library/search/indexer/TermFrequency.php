<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 10:34
 */

namespace library\search\indexer;

use library\search\Indexer;

/**
 * Calculate, relatively how often a term is used in a document
 * Where relativly means compared to the total of terms, how often is term X
 * used. For example:
 * doc1 has the following terms:
 * - term1 (count 2)
 * - term2 (count 1)
 * The total count of terms = 3
 * The frequency of term1 in doc1 is:
 * count of term 1 / total count of terms
 * =
 * 2 / 3 = 0.66666666667
 */
class TermFrequency
{
	/**
	 * @var \PDO
	 */
	protected $dbHandle;

	/**
	 * TermFrequency constructor.
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
		$totalTermCountPerDocument = $this->getTotalTermCountPerDocument($db);
		foreach ($totalTermCountPerDocument as $documentField) {
			$termsForDocumentField = $this->getTermsForDocumentField($documentField->documentPath, $documentField->field);
			$sql = '
				INSERT INTO term_frequency (documentPath, field, term, frequency)
					 VALUES 
			';
			$quotedDocumentPath = $db->quote($documentField->documentPath);
			$quotedField = $db->quote($documentField->field);
			$values = array();
			$i = 0;
			foreach ($termsForDocumentField as $term) {
				$frequency = intval($term->count) / $documentField->totalTermCount;
				$values[] = $quotedDocumentPath . ','  . $quotedField . ', ' . $db->quote($term->term) . ', ' . $db->quote($frequency);
				$i += 1;
				if ($i >= Indexer::SQLITE_MAX_COMPOUND_SELECT) {
					$this->executeStore($sql, $values, $db);
					$i = 0;
					$values = array();
				}
			}
			if (count($values) != 0) {
				$this->executeStore($sql, $values, $db);
			}
		}
	}

	private function getTermsForDocumentField($documentPath, $field)
	{
		$db = $this->dbHandle;
		$stmt = $db->prepare('
			SELECT `term`, `count`
			  FROM `term_count`
			 WHERE `documentPath` = :documentPath
			   AND `field` = :field
		');
		$stmt->bindValue(':documentPath', $documentPath);
		$stmt->bindValue(':field', $field);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_CLASS);
	}

	/**
	 * @param $db
	 *
	 * @return mixed
	 */
	private function getTotalTermCountPerDocument($db)
	{
		$stmt = $db->prepare('
			SELECT documentPath, field, SUM(count) as totalTermCount
			  FROM term_count
		  GROUP BY documentPath, field
		');
		$stmt->execute();
		$totalTermCountPerDocument = $stmt->fetchAll(\PDO::FETCH_CLASS);

		return $totalTermCountPerDocument;
	}

	private function executeStore($sql, $values, $db)
	{
		$sql .= '(' . implode('),' . PHP_EOL . '(', $values) . ');';
		if (!$db->query($sql)) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
	}

}