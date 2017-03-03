<?php
/**
 * User: jensk
 * Date: 3-3-2017
 * Time: 16:56
 */

namespace library\search;


use library\storage\Document;
use library\storage\JsonStorage;
use library\storage\Repository;

class SearchResult
{
	/**
	 * @var string
	 */
	public $documentPath;
	/**
	 * @var array
	 */
	public $matchingTokens;
	/**
	 * @var float
	 */
	public $score;

	protected $document;
	/**
	 * @var JsonStorage
	 */
	protected $storage;

	/**
	 * @return Document
	 */
	public function getDocument()
	{
		if ($this->document instanceof Document) {
			return $this->document;
		} else {
			$this->document = $this->storage->getDocumentBySlug(substr($this->documentPath, 1));
			$this->document->dbHandle = $this->storage->getContentDbHandle();
			return $this->document;
		}
	}

	public function setStorage($storage)
	{
		$this->storage = $storage;
	}
}