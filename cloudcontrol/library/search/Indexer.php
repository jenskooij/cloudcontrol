<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:29
 */

namespace library\search;


use library\storage\JsonStorage;

class Indexer extends SearchDbConnected
{
	/**
	 * @var \library\storage\JsonStorage
	 */
	protected $storage;

	protected $filters = array(
		'DutchStopWords',
		'EnglishStopWords'
	);
	protected $storageDir;

	public function updateIndex()
	{
		$this->resetIndex();
		$this->createDocumentTermCount();
		$this->createDocumentTermFrequency();
		$this->createInverseDocumentFrequency();
		dump('Continue here: https://en.wikipedia.org/wiki/Tf%E2%80%93idf#Example_of_tf.E2.80.93idf', $this->getSearchDbHandle()->errorInfo());
	}

	private function applyFilters($tokens)
	{
		foreach ($this->filters as $filterName) {
			$filterClassName = '\library\search\filters\\' . $filterName;
			$filter = new $filterClassName($tokens);
			$tokens = $filter->getFilterResults();
		}
		return $tokens;
	}

	private function storeDocumentTermCount($document, $documentTermCount)
	{
		$db = $this->getSearchDbHandle();
		$sql = '
			INSERT INTO `term_count` (`documentPath`, `term`, `count`)
				 VALUES (:documentPath, :term, :count);
		';
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':documentPath', $document->path);
		foreach ($documentTermCount as $term => $count) {
			$stmt->bindValue(':term', $term);
			$stmt->bindValue(':count', $count);
			if (!$stmt->execute()) {
				$errorInfo = $db->errorInfo();
				$errorMsg = $errorInfo[2];
				throw new \Exception('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
			}
		}
	}

	/**
	 * Count how often a term is used in a document
	 */
	private function createDocumentTermCount()
	{
		$documents = $this->storage->getDocuments();
		foreach ($documents as $document) {
			$tokenizer = new DocumentTokenizer($document);
			$tokens = $tokenizer->getTokens();
			$documentTermCount = $this->applyFilters($tokens);
			$this->storeDocumentTermCount($document, $documentTermCount);
		}
	}

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
	private function createDocumentTermFrequency()
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->prepare('
			SELECT documentPath, SUM(count) as totalTermCount
			  FROM term_count
		  GROUP BY documentPath
		');
		$stmt->execute();
		$totalTermCountPerDocument = $stmt->fetchAll(\PDO::FETCH_CLASS);
		foreach ($totalTermCountPerDocument as $document) {
			$termsForDocument = $this->getTermsForDocument($document->documentPath);
			foreach ($termsForDocument as $term) {
				$frequency = intval($term->count) / $document->totalTermCount;
				$this->storeDocumentTermFrequency($document->documentPath, $term->term, $frequency);
			}
		}
	}

	private function getTermsForDocument($documentPath)
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->prepare('
			SELECT `term`, `count`
			  FROM `term_count`
			 WHERE `documentPath` = :documentPath
		');
		$stmt->bindValue(':documentPath', $documentPath);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_CLASS);
	}

	/**
	 * Resets the entire index
	 */
	private function resetIndex()
	{
		$db = $this->getSearchDbHandle();
		$sql = '
			DELETE FROM term_count;
			DELETE FROM term_frequency;
			DELETE FROM inverse_document_frequency;
			UPDATE `sqlite_sequence` SET `seq`= 0 WHERE `name`=\'term_count\';
			UPDATE `sqlite_sequence` SET `seq`= 0 WHERE `name`=\'term_frequency\';
			UPDATE `sqlite_sequence` SET `seq`= 0 WHERE `name`=\'inverse_document_frequency\';
		';
		$db->exec($sql);
	}

	private function storeDocumentTermFrequency($documentPath, $term, $frequency)
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->prepare('
			INSERT INTO `term_frequency` (documentPath, term, frequency)
				 VALUES(:documentPath, :term, :frequency);
		');
		$stmt->bindValue(':documentPath', $documentPath);
		$stmt->bindValue(':term', $term);
		$stmt->bindValue(':frequency', $frequency);
		$stmt->execute();
	}

	private function createInverseDocumentFrequency()
	{
		/**
		 * Formula to calculate:
		 * IDF = log(totalDocuments / documentsThatContainTheTerm)
		 */
		$totalDocuments = $this->getTotalDocumentCount();
		$allTerms = $this->getAllUniqueTerms();

		foreach ($allTerms as $term) {
			$documentsThatContainTheTerm = $this->getDocumentsThatContainTheTerm($term->term);
			$inverseDocumentFrequency = log($totalDocuments / $documentsThatContainTheTerm->totalCount);
			$this->storeInverseTermFrequency($term->term, $inverseDocumentFrequency);
		}
	}

	private function getTotalDocumentCount()
	{
		return $this->storage->getTotalDocumentCount();
	}

	private function getAllUniqueTerms()
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->prepare('
			SELECT `term`
			  FROM `term_count`
		  GROUP BY `term`
		');
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_CLASS);
	}

	private function getDocumentsThatContainTheTerm($term)
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->prepare('
			SELECT COUNT(`documentPath`) as totalCount
			  FROM `term_count`
			 WHERE `term` = :term
		');
		$stmt->bindValue(':term', $term);
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	private function storeInverseTermFrequency($term, $inverseDocumentFrequency)
	{
		$db = $this->getSearchDbHandle();
		$stmt = $db->prepare('
			INSERT INTO `inverse_document_frequency` (term, inverseDocumentFrequency)
				 VALUES(:term, :inverseDocumentFrequency);
		');
		$stmt->bindValue(':term', $term);
		$stmt->bindValue(':inverseDocumentFrequency', $inverseDocumentFrequency);
		$stmt->execute();
	}
}