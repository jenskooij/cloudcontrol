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
			$results = $this->getResultsForQuery($token);
			dump($results);
			dump('TODO implement search based on : score = tfForDoc("query") * idf("query")');
		}
	}

	public function getResultsForQuery($query) {
		$db = $this->getSearchDbHandle();
		$sql = '
			SELECT `tf`.`documentPath`, 
				   `tf`.`frequency`,
				   `idf`.`inverseDocumentFrequency`,
				   (`tf`.`frequency` * `idf`.`inverseDocumentFrequency`) as `tfidfRank`
			  FROM `term_frequency` as `tf`
		 LEFT JOIN `inverse_document_frequency` as `idf`
		 		ON `idf`.`term` = `tf`.`term`
			 WHERE `tf`.`term` = :query
		  ORDER BY `tfidfRank` DESC
		';
		if(!$stmt = $db->prepare($sql)) {
			throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':query', $query);
		if (!$stmt->execute()) {
			throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
		}
		return $stmt->fetchAll(\PDO::FETCH_CLASS, 'stdClass');
	}
}