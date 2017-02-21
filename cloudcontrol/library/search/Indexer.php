<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:29
 */

namespace library\search;


use library\storage\JsonStorage;

class Indexer
{
	/**
	 * @var \library\storage\JsonStorage
	 */
	protected $storage;

	protected $filters = array(
		'DutchStopWords',
		'EnglishStopWords'
	);

	/**
	 * Indexer constructor.
	 *
	 * @param \library\storage\JsonStorage $storage
	 */
	public function __construct(JsonStorage $storage)
	{
		$this->storage = $storage;
		// TODO initialize the search database if it doesnt exist. IE create table if not exists
	}

	public function updateIndex()
	{
		$documents = $this->storage->getDocuments();
		foreach ($documents as $document) {
			$tokenizer = new Tokenizer($document);
			$tokens = $tokenizer->getTokens();
			$tokens = $this->applyFilters($tokens);
			dump($tokens);
			dump($tokenizer);
		}
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

}