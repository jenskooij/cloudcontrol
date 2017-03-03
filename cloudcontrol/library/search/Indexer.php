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
	protected $loggingStart;
	protected $log;
	protected $lastLog;

	public function updateIndex()
	{
		$this->startLogging();
		$this->addLog('Indexing start. Clearing index.');
		$this->resetIndex();
		$this->addLog('Index cleared. Start Document Term Count.');
		$this->createDocumentTermCount();
		$this->addLog('Start Document Term Count done. Start Document Term Frequency.');
		$this->createDocumentTermFrequency();
		$this->addLog('Document Term Frequency done. Start Term Field Length Norm.');
		$this->createTermFieldLengthNorm();
		$this->addLog('Term Field Length Norm done. Start Inverse Document Frequency.');
		$this->createInverseDocumentFrequency();
		$this->addLog('Inverse Document Frequency done. Indexing complete.');
		dump(PHP_EOL . $this->log);
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

	private function startLogging()
	{
		$this->loggingStart = time();
	}

	private function addLog($string)
	{
		$currentTime = time();
		$this->log .= date('d-m-Y H:i:s - ') . str_pad($string, 75, " ", STR_PAD_RIGHT) . "\t" . ($currentTime - $this->lastLog) . 's since last log. ' . "\t" . ($currentTime - $this->loggingStart) . 's since start.' . PHP_EOL;
		$this->lastLog = time();
	}
}