<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 16:55
 */

namespace library\components;

use library\cc\Request;
use library\search\CharacterFilter;
use library\search\Search;
use library\search\Tokenizer;
use library\storage\JsonStorage;

class SearchComponent extends BaseComponent
{
	protected $searchParameterName = 'q';
	protected $searchResultsParameterName = 'searchResults';

	/**
	 * @param \library\storage\JsonStorage $storage
	 */
	public function run(JsonStorage $storage)
	{
		parent::run($storage);

		$this->checkParameters();

		$request = $this->request;
		if (isset($request::$get[$this->searchParameterName])) {
			$query = $request::$get[$this->searchParameterName];
			$filteredQuery = new CharacterFilter($query);
			$tokenizer = new Tokenizer($filteredQuery);
			$search = new Search($storage);
			$results = $search->getDocumentsForTokenizer($tokenizer);
			$this->parameters[$this->searchResultsParameterName] = $results;
		}
	}

	/**
	 * Checks to see if any parameters were defined in the cms and acts according
	 */
	private function checkParameters()
	{
		if (isset($this->parameters['searchParameterName'])) {
			$this->searchParameterName = $this->parameters['searchParameterName'];
		}

		if (isset($this->parameters['searchResultsParameterName'])) {
			$this->searchParameterName = $this->parameters['searchResultsParameterName'];
		}
	}

}