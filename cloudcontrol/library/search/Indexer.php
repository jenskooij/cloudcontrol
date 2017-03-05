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
		$this->addLog('Indexing start.');
		$this->addLog('Clearing index.');
		$this->resetIndex();
		$this->addLog('Retrieving documents to be indexed.');
		$documents = $this->storage->getDocuments();
		$this->addLog('Start Document Term Count for ' . count($documents) . ' documents');
		$this->createDocumentTermCount($documents);
		$this->addLog('Start Document Term Frequency.');
		$this->createDocumentTermFrequency();
		$this->addLog('Start Term Field Length Norm.');
		$this->createTermFieldLengthNorm();
		$this->addLog('Start Inverse Document Frequency.');
		$this->createInverseDocumentFrequency();
		$this->addLog('Indexing complete.');
		return $this->log;
	}

	/**
	 * Count how often a term is used in a document
	 *
	 * @param $documents
	 */
	private function createDocumentTermCount($documents)
	{
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
		$this->loggingStart = round(microtime(true) * 1000);
		$this->lastLog = $this->loggingStart;
	}

	private function addLog($string)
	{
		$currentTime = round(microtime(true) * 1000);
		$this->log .= date('d-m-Y H:i:s - ') . str_pad($string, 50, " ", STR_PAD_RIGHT) . "\t" . ($currentTime - $this->lastLog) . 'ms since last log. ' . "\t" . ($currentTime - $this->loggingStart) . 'ms since start.' . PHP_EOL;
		$this->lastLog = round(microtime(true) * 1000);
	}

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
		return $result;
	}
}