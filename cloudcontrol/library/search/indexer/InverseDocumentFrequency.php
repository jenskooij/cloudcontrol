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
		$documentCount = $this->documentCount;
		$this->storeInverseTermFrequency($documentCount);
	}

	private function storeInverseTermFrequency($documentCount)
	{
		$db = $this->dbHandle;
		$db->sqliteCreateFunction('log', 'log', 1);
		$sql = '
		INSERT INTO inverse_document_frequency (term, inverseDocumentFrequency)
		SELECT DISTINCT term_count.term, (1 + log(:documentCount / ((
					SELECT COUNT(documentPath)
					  FROM term_count as tc
					 WHERE tc.term = term_count.term
				  GROUP BY documentPath, term
				) + 1))) as inverseDocumentFrequency
		   FROM term_count
		';
		if (!$stmt = $db->prepare($sql)) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':documentCount', $documentCount);
		if (!$stmt->execute()) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
	}
}