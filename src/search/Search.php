<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 17:05
 */

namespace CloudControl\Cms\search;
use CloudControl\Cms\search\results\SearchResult;

/**
 * Class Search
 * Formula:
 * score(q,d)  =
 *        queryNorm(q)
 *        · coord(q,d)
 *        · ∑ (
 *            tf(t in d)
 *            · idf(t)²
 *            · t.getBoost()
 *            · norm(t,d)
 *        ) (t in q)
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/practical-scoring-function.html
 * @package CloudControl\Cms\search
 */
class Search extends SearchDbConnected
{
    /**
     * @var Tokenizer
     */
    protected $tokenizer;
    protected $results = array();

    /**
     * An array containing classes implementing \CloudControl\Cms\search\Filters
     * These will be applied to all tokenizers
     * @var array
     */
    protected $filters = array(
        'DutchStopWords',
        'EnglishStopWords'
    );

    /**
     * Returns an array of SeachResult and / or SearchSuggestion objects,
     * based on the tokens in the Tokenizer
     * @param Tokenizer $tokenizer
     *
     * @return array
     * @throws \Exception
     */
    public function getDocumentsForTokenizer(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
        $resultsPerTokens = $this->queryTokens();

        $flatResults = $this->flattenResults($resultsPerTokens);
        $flatResults = $this->applyQueryCoordination($flatResults);
        usort($flatResults, array($this, "scoreCompare"));

        $flatResults = array_merge($this->getSearchSuggestions(), $flatResults);

        return $flatResults;
    }

    /**
     * Returns the amount of distinct documents
     * that are currently in the search index.
     * @return int
     * @throws \Exception
     */
    public function getIndexedDocuments()
    {
        $db = $this->getSearchDbHandle();
        $sql = '
			SELECT count(DISTINCT documentPath) as indexedDocuments
			  FROM term_frequency
		';
        if (!$stmt = $db->query($sql)) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
        $result = $stmt->fetch(\PDO::FETCH_COLUMN);
        if (false === $result) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
        return (int)$result;
    }

    /**
     * Queries each token present in the Tokenizer
     * and returns SearchResult objects for the found
     * documents
     * @return array
     * @throws \Exception
     */
    private function queryTokens()
    {
        $tokens = $this->getTokens();

        $queryNorm = $this->getQueryNorm($tokens);
        $results = array();
        foreach ($tokens as $token) {
            $results[$token] = $this->getResultsForToken($token, $queryNorm);
        }
        return $results;
    }

    /**
     * Applies the Filter objects in the the filter array to the
     * tokens in the Tokenizer
     * @param $tokens
     *
     * @return mixed
     */
    protected function applyFilters($tokens)
    {
        foreach ($this->filters as $filterName) {
            $filterClassName = '\CloudControl\Cms\search\filters\\' . $filterName;
            $filter = new $filterClassName($tokens);
            $tokens = $filter->getFilterResults();
        }
        return $tokens;
    }

    /**
     * Queries the search index for a given token
     * and the query norm.
     * @param $token
     * @param $queryNorm
     *
     * @return array
     * @throws \Exception
     */
    public function getResultsForToken($token, $queryNorm)
    {
        $db = $this->getSearchDbHandle();
        $sql = '
			SELECT (:queryNorm * 
						(SUM(term_frequency.frequency) --TF
						* inverse_document_frequency.inverseDocumentFrequency -- IDF
						* SUM(term_frequency.termNorm) -- norm
						) 
				    )as score,
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
        if (!$stmt = $db->prepare($sql)) {
            throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
        }
        $stmt->bindValue(':query', $token);
        $stmt->bindValue(':queryNorm', $queryNorm);
        if (!$stmt->execute()) {
            throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
        }
        return $stmt->fetchAll(\PDO::FETCH_CLASS, SearchResult::class);
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
                    $resultObj = new SearchResult();
                    $resultObj->documentPath = $result->documentPath;
                    $resultObj->matchingTokens = array($token);
                    $resultObj->score = (float)$result->score;
                    $resultObj->setStorage($this->storage);
                    $finalResults[$result->documentPath] = $resultObj;
                }
            }
        }
        return $finalResults;
    }

    private function scoreCompare($a, $b)
    {
        if ($a->score == $b->score) {
            return 0;
        }
        return ($a->score > $b->score) ? -1 : 1;
    }

    /**
     * Calculates the query norm for all tokens in the Tokenizer
     * @param $tokens
     *
     * @return int
     * @throws \Exception
     */
    private function getQueryNorm($tokens)
    {
        $db = $this->getSearchDbHandle();
        $db->/** @scrutinizer ignore-call */sqliteCreateFunction('sqrt', 'sqrt', 1);
        foreach ($tokens as $key => $token) {
            $tokens[$key] = $db->quote($token);
        }
        $terms = implode(',', $tokens);
        $sql = '
			SELECT (1 / sqrt(SUM(inverseDocumentFrequency))) as queryNorm
			  FROM inverse_document_frequency
			 WHERE term IN (' . $terms . ') 
		';
        if (!$stmt = $db->prepare($sql)) {
            throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
        }
        if (!$stmt->execute()) {
            throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
        }
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result->queryNorm == null ? 1 : $result->queryNorm;
    }

    /**
     * Applies query coordination to all results
     * @param $flatResults
     *
     * @return mixed
     */
    private function applyQueryCoordination($flatResults)
    {
        $tokenVector = $this->tokenizer->getTokenVector();
        $tokens = array_keys($tokenVector);
        $tokenCount = count($tokens);
        foreach ($flatResults as $key => $result) {
            $matchCount = count($result->matchingTokens);
            $result->score = ($matchCount / $tokenCount) * $result->score;
            $flatResults[$key] = $result;
        }
        return $flatResults;
    }

    /**
     * Uses the levenshtein algorithm to determine the term that is
     * closest to the token that was input for the search
     * @return array
     * @throws \Exception
     */
    private function getSearchSuggestions()
    {
        $tokens = $this->getTokens();
        $allResults = array();
        foreach ($tokens as $token) {
            $db = $this->getSearchDbHandle();
            $db->/** @scrutinizer ignore-call */sqliteCreateFunction('levenshtein', 'levenshtein', 2);
            $sql = '
				SELECT *
				  FROM (
				  	SELECT :token as original, term, levenshtein(term, :token) as editDistance
				  	  FROM inverse_document_frequency
			  	  ORDER BY editDistance ASC
			  	     LIMIT 0, 1
			  	     )
			  	   WHERE editDistance > 0
			';
            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
            }
            $stmt->bindValue(':token', $token);
            if (($stmt === false) | (!$stmt->execute())) {
                throw new \Exception('SQLite exception: <pre>' . print_r($db->errorInfo(), true) . '</pre> for SQL:<pre>' . $sql . '</pre>');
            }
            $result = $stmt->fetchAll(\PDO::FETCH_CLASS, results\SearchSuggestion::class);
            $allResults = array_merge($result, $allResults);
        }
        return $allResults;
    }

    /**
     * Retrieves all tokens from the tokenizer
     * @return array
     */
    private function getTokens()
    {
        $tokenVector = array(
            'query' => array(),
        );
        $tokenVector['query'] = $this->tokenizer->getTokenVector();
        $tokens = $this->applyFilters($tokenVector);
        if (!empty($tokens)) {
            $tokens = array_keys($tokens['query']);
        }

        return $tokens;
    }
}