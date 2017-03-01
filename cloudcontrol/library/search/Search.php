<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 17:05
 */

namespace library\search;

/**
 * Class Search
 * Formula:
 * score(q,d)  =
		queryNorm(q)
		· coord(q,d)
		· ∑ (
			tf(t in d)
			· idf(t)²
			· t.getBoost()
			· norm(t,d)
		) (t in q)
 *
 *
 * @package library\search
 */
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
		$resultsPerTokens = $this->queryTokens();

		$flatResults = $this->flattenResults($resultsPerTokens);
		usort($flatResults, array($this, "scoreCompare"));

		dump($flatResults);
	}

	private function queryTokens()
	{
		$tokenVector = $this->tokenizer->getTokenVector();
		$tokens = array_keys($tokenVector);
		$results = array();
		foreach ($tokens as $token) {
			$results[$token] = $this->getResultsForToken($token);
		}
		return $results;
	}

	public function getResultsForToken($token) {
		$db = $this->getSearchDbHandle();
		$sql = '
			SELECT (SUM(term_frequency.frequency) --TF
				    * inverse_document_frequency.inverseDocumentFrequency -- IDF
				    * SUM(term_frequency.termNorm) -- norm
				    ) as score,
				   SUM(term_frequency.frequency) as TF,
				   inverse_document_frequency.inverseDocumentFrequency as IDF,
				   SUM(term_frequency.termNorm) as norm,
				   term_frequency.documentPath
			  FROM term_frequency
		 LEFT JOIN inverse_document_frequency
		 		ON inverse_document_frequency.term = term_frequency.term
			 WHERE term_frequency.term = :query
		  GROUP BY term_frequency.documentPath, term_frequency.term
		  ORDER BY score DESC
		';
		if(!$stmt = $db->prepare($sql)) {
			throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
		}
		$stmt->bindValue(':query', $token);
		if (!$stmt->execute()) {
			throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
		}
		return $stmt->fetchAll(\PDO::FETCH_CLASS, 'stdClass');
	}

	/**
	 * @param $resultsPerTokens
	 *
	 * @return array
	 */
	private function flattenResults($resultsPerTokens)
	{
		$finalResults = array();
		foreach ($resultsPerTokens as $token => $resultPerToken) {
			foreach ($resultPerToken as $result) {
				if (isset($finalResults[$result->documentPath])) {
					$finalResults[$result->documentPath]->score += $result->score;
					$finalResults[$result->documentPath]->matchingTokens[] = $token;
				} else {
					$resultObj = new \stdClass();
					$resultObj->documentPath = $result->documentPath;
					$resultObj->matchingTokens = array($token);
					$resultObj->score = floatval($result->score);
					$finalResults[$result->documentPath] = $resultObj;
				}
			}
		}
		return $finalResults;
	}

	private function scoreCompare($a, $b) {
		if ($a->score == $b->score) {
			return 0;
		}
		return ($a->score > $b->score) ? -1 : 1;
	}
}