<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 10:34
 */

namespace library\search\indexer;

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
	protected $dbHandle;

	/**
	 * TermFrequency constructor.
	 */
	public function __construct($dbHandle)
	{
		$this->dbHandle = $dbHandle;
	}

	public function execute()
	{
		$db = $this->dbHandle;
		$stmt = $db->prepare('
			SELECT documentPath, field, SUM(count) as totalTermCount
			  FROM term_count
		  GROUP BY documentPath, field
		');
		$stmt->execute();
		$totalTermCountPerDocument = $stmt->fetchAll(\PDO::FETCH_CLASS);
		foreach ($totalTermCountPerDocument as $documentField) {
			$termsForDocumentField = $this->getTermsForDocumentField($documentField->documentPath, $documentField->field);
			foreach ($termsForDocumentField as $term) {
				$frequency = intval($term->count) / $documentField->totalTermCount;
				$this->storeDocumentTermFrequency($documentField->documentPath, $term->term, $frequency, $documentField->field);
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

	private function storeDocumentTermFrequency($documentPath, $term, $frequency, $field)
	{
		$db = $this->dbHandle;
		$stmt = $db->prepare('
			INSERT INTO `term_frequency` (documentPath, field, term, frequency)
				 VALUES(:documentPath, :field, :term, :frequency);
		');
		$stmt->bindValue(':documentPath', $documentPath);
		$stmt->bindValue(':field', $field);
		$stmt->bindValue(':term', $term);
		$stmt->bindValue(':frequency', $frequency);
		$stmt->execute();
	}

}