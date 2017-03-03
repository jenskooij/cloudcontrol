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
	const PARAMETER_QUERY = "q";

	/**
	 * @param \library\storage\JsonStorage $storage
	 */
	public function run(JsonStorage $storage)
	{
		parent::run($storage);
		$request = $this->request;
		if (isset($request::$get[self::PARAMETER_QUERY])) {
			$query = $request::$get[self::PARAMETER_QUERY];
			$filteredQuery = new CharacterFilter($query);
			$tokenizer = new Tokenizer($filteredQuery);
			$search = new Search($storage);
			$startTime = microtime();
			$results = $search->getDocumentsForTokenizer($tokenizer);
			dump(microtime() - $startTime, $results);
		}
	}

}