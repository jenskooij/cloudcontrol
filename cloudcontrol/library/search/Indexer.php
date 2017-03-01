<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:29
 */

namespace library\search;


use library\search\indexer\InverseDocumentFrequency;
use library\search\indexer\TermCount;
use library\search\indexer\TermFieldLengthNorm;
use library\search\indexer\TermFrequency;

class Indexer extends SearchDbConnected
{
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
		$this->createTermFieldLengthNorm();
		$this->createInverseDocumentFrequency();
		dump('Continue here: https://en.wikipedia.org/wiki/Tf%E2%80%93idf#Example_of_tf.E2.80.93idf', $this->getSearchDbHandle()->errorInfo());
	}

	/**
	 * Count how often a term is used in a document
	 */
	private function createDocumentTermCount()
	{
		$documents = $this->storage->getDocuments();
		$termCount = new TermCount($this->getSearchDbHandle(), $documents, $this->filters);
		$termCount->execute();
	}


	private function createDocumentTermFrequency()
	{
		$termFrequency = new TermFrequency($this->getSearchDbHandle());
		$termFrequency->execute();
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

	private function createInverseDocumentFrequency()
	{
		$documentCount = $this->getTotalDocumentCount();
		$inverseDocumentFrequency = new InverseDocumentFrequency($this->getSearchDbHandle(), $documentCount);
		$inverseDocumentFrequency->execute();


	}

	private function getTotalDocumentCount()
	{
		return $this->storage->getTotalDocumentCount();
	}

	private function createTermFieldLengthNorm()
	{
		$termFieldLengthNorm = new TermFieldLengthNorm($this->getSearchDbHandle());
		$termFieldLengthNorm->execute();
	}
}