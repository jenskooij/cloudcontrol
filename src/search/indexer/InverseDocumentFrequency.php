<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 11:41
 */

namespace CloudControl\Cms\search\indexer;


class InverseDocumentFrequency
{
	/**
	 * @var \PDO
	 */
	protected $dbHandle;
	protected $documentCount;

	/**
	 * InverseDocumentFrequency constructor.
	 *
	 * @param \PDO 	$dbHandle
	 * @param int   $documentCount
	 */
	public function __construct($dbHandle, $documentCount)
	{
		$this->dbHandle = $dbHandle;
		$this->documentCount = $documentCount;
	}

	/**
	 * Formula to calculate:
	 * 		idf(t) = 1 + log ( totalDocuments / (documentsThatContainTheTerm + 1))
	 * @throws \Exception
	 */
	public function execute()
	{
		$db = $this->dbHandle;
		$db->sqliteCreateFunction('log', 'log', 1);
		$sql = '
		INSERT INTO inverse_document_frequency (term, inverseDocumentFrequency)
		SELECT DISTINCT term, (1+(log(:documentCount / COUNT(documentPath) + 1))) as inverseDocumentFrequency
					  FROM term_count
				  GROUP BY term
		';

		if (!$stmt = $db->prepare($sql)) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':documentCount', $this->documentCount);
		$result = $stmt->execute();
		if ($result === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
	}
}