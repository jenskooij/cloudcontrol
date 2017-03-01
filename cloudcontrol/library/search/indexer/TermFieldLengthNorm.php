<?php
/**
 * User: jensk
 * Date: 1-3-2017
 * Time: 13:21
 */

namespace library\search\indexer;


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
			UPDATE term_frequency
			   SET termNorm = (
				   SELECT 1/sqrt(SUM(count)) as calcNorm
					  FROM term_count
				     WHERE term_count.documentPath = term_frequency.documentPath
				       AND term_count.field = term_frequency.field
				       AND term_count.term = term_frequency.term
			      GROUP BY documentPath, field
			  )
			  
		';
		$stmt = $db->prepare($sql);
		if ($stmt === false) {
			$errorInfo = $db->errorInfo();
			$errorMsg = $errorInfo[2];
			throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
		}
		$stmt->execute();
	}
}