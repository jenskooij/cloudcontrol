<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 1-3-2017
 * Time: 11:41
 */

namespace library\search\indexer;


class InverseDocumentFrequency
{
	protected $dbHandle;
	protected $documentCount;

	/**
	 * InverseDocumentFrequency constructor.
	 *
	 * @param resource $dbHandle
	 * @param int      $documentCount
	 */
	public function __construct($dbHandle, $documentCount)
	{
		$this->dbHandle = $dbHandle;
		$this->documentCount = $documentCount;
	}

	/**
	 * Formula to calculate:
	 * idf(t) = 1 + log ( totalDocuments / (documentsThatContainTheTerm + 1))
	 */
	public function execute()
	{
		$totalDocuments = $this->documentCount;
		$allTerms = $this->getAllUniqueTerms();

		foreach ($allTerms as $term) {
			$documentsThatContainTheTerm = $this->getDocumentsThatContainTheTerm($term->term);
			$inverseDocumentFrequency = 1 + log($totalDocuments / ($documentsThatContainTheTerm->totalCount + 1));
			$this->storeInverseTermFrequency($term->term, $inverseDocumentFrequency);
		}
	}

	protected function getAllUniqueTerms()
	{
		$db = $this->dbHandle;
		$stmt = $db->prepare('
			SELECT `term`
			  FROM `term_count`
		  GROUP BY `term`
		');
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_CLASS);
	}

	protected function getDocumentsThatContainTheTerm($term)
	{
		$db = $this->dbHandle;
		$sql = '
			SELECT COUNT(documentPath) as totalCount
			  FROM (SELECT documentPath, term FROM term_count GROUP BY documentPath, term) as `term_count`
			 WHERE `term` = :term
		';
		$stmt = $db->prepare($sql);
		if ($stmt === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':term', $term);
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	protected function storeInverseTermFrequency($term, $inverseDocumentFrequency)
	{
		$db = $this->dbHandle;
		$stmt = $db->prepare('
			INSERT INTO `inverse_document_frequency` (term, inverseDocumentFrequency)
				 VALUES(:term, :inverseDocumentFrequency);
		');
		$stmt->bindValue(':term', $term);
		$stmt->bindValue(':inverseDocumentFrequency', $inverseDocumentFrequency);
		$stmt->execute();
	}
}