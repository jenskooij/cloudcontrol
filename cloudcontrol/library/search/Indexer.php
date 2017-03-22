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

/**
 * Class Indexer
 * Responsible for creating the search index based on the
 * existing documents
 *
 * @package library\search
 */
class Indexer extends SearchDbConnected
{
	const SQLITE_MAX_COMPOUND_SELECT = 100;
	protected $filters = array(
		'DutchStopWords',
		'EnglishStopWords'
	);
	protected $storageDir;
	/**
	 * @var double
	 */
	protected $loggingStart;
	/**
	 * @var string
	 */
	protected $log;
	/**
	 * @var double
	 */
	protected $lastLog;

	const SEARCH_TEMP_DB = 'search_tmp.db';

	/**
	 * Creates a new temporary search db, cleans it if it exists
	 * then calculates and stores the search index in this db
	 * and finally if indexing completed replaces the current search
	 * db with the temporary one. Returns the log in string format.
	 * @return string
	 */
	public function updateIndex()
	{
		$this->startLogging();
		$this->addLog('Indexing start.');
		$this->addLog('Clearing index.');
		$this->resetIndex();
		$this->addLog('Retrieving documents to be indexed.');
		$documents = $this->storage->getDocuments()->getPublishedDocumentsNoFolders();
		$this->addLog('Start Document Term Count for ' . count($documents) . ' documents');
		$this->createDocumentTermCount($documents);
		$this->addLog('Start Document Term Frequency.');
		$this->createDocumentTermFrequency();
		$this->addLog('Start Term Field Length Norm.');
		$this->createTermFieldLengthNorm();
		$this->addLog('Start Inverse Document Frequency.');
		$this->createInverseDocumentFrequency();
		$this->addLog('Replacing old index.');
		$this->replaceOldIndex();
		$this->addLog('Indexing complete.');
		return $this->log;
	}

	/**
	 * Count how often a term is used in a document
	 *
	 * @param $documents
	 */
	public function createDocumentTermCount($documents)
	{
		$termCount = new TermCount($this->getSearchDbHandle(), $documents, $this->filters, $this->storage);
		$termCount->execute();
	}

	/**
	 * Calculate the frequency index for a term with
	 * a field
	 */
	public function createDocumentTermFrequency()
	{
		$termFrequency = new TermFrequency($this->getSearchDbHandle());
		$termFrequency->execute();
	}


	/**
	 * Resets the entire index
	 */
	public function resetIndex()
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

	/**
	 * Calculates the inverse document frequency for each
	 * term. This is a representation of how often a certain
	 * term is used in comparison to all terms.
	 */
	public function createInverseDocumentFrequency()
	{
		$documentCount = $this->getTotalDocumentCount();
		$inverseDocumentFrequency = new InverseDocumentFrequency($this->getSearchDbHandle(), $documentCount);
		$inverseDocumentFrequency->execute();
	}

	/**
	 * @return int|mixed
	 */
	private function getTotalDocumentCount()
	{
		return $this->storage->getDocuments()->getTotalDocumentCount();
	}

	/**
	 * Calculates the Term Field Length Norm.
	 * This is an index determining how important a
	 * term is, based on the total length of the field
	 * it comes from.
	 */
	public function createTermFieldLengthNorm()
	{
		$termFieldLengthNorm = new TermFieldLengthNorm($this->getSearchDbHandle());
		$termFieldLengthNorm->execute();
	}

	/**
	 * Stores the time the indexing started in memory
	 */
	private function startLogging()
	{
		$this->loggingStart = round(microtime(true) * 1000);
		$this->lastLog = $this->loggingStart;
	}

	/**
	 * Adds a logline with the time since last log
	 * @param $string
	 */
	private function addLog($string)
	{
		$currentTime = round(microtime(true) * 1000);
		$this->log .= date('d-m-Y H:i:s - ') . str_pad($string, 50, " ", STR_PAD_RIGHT) . "\t" . ($currentTime - $this->lastLog) . 'ms since last log. ' . "\t" . ($currentTime - $this->loggingStart) . 'ms since start.' . PHP_EOL;
		$this->lastLog = round(microtime(true) * 1000);
	}

	/**
	 * Creates the SQLite \PDO object if it doesnt
	 * exist and returns it.
	 * @return \PDO
	 */
	protected function getSearchDbHandle()
	{
		if ($this->searchDbHandle === null) {
			$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR;
			$this->searchDbHandle = new \PDO('sqlite:' . $path . self::SEARCH_TEMP_DB);
		}
		return $this->searchDbHandle;
	}

	/**
	 * Replaces the old search index database with the new one.
	 */
	public function replaceOldIndex()
	{
		$this->searchDbHandle = null;
		$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR;
		rename($path . self::SEARCH_TEMP_DB, $path . 'search.db');
	}
}