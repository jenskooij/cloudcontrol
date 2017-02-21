<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 17:05
 */

namespace library\search;


class Search extends SearchDbConnected
{
	/**
	 * @var Tokenizer
	 */
	protected $tokenizer;
	protected $results = array();

	/**
	 * @param Tokenizer $tokenizer
	 */
	public function getDocumentsForTokenizer(Tokenizer $tokenizer)
	{
		$this->tokenizer = $tokenizer;
		$this->queryTokens();
	}

	private function queryTokens()
	{
		$tokenVector = $this->tokenizer->getTokenVector();
		$tokens = array_keys($tokenVector);
		foreach ($tokens as $token) {
			$db = $this->getSearchDbHandle();
			$stmt = $db->prepare('
				SELECT *
				  FROM term_count
				 WHERE term = :term
			  ORDER BY count DESC
			');
			$stmt->bindValue(':term', $token);
			$stmt->execute();
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			dump('SHOULD CREATE TERM FREQUENCY TABLE');
			dump($result);
		}
	}
}