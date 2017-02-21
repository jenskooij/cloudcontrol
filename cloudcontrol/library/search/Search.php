<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 17:05
 */

namespace library\search;


class Search extends SearchDbConnected
{
	/**
	 * @var Tokenizer
	 */
	protected $tokenizer;
	protected $results = array();

	/**
	 * @param Tokenizer $tokenizer
	 */
	public function getDocumentsForTokenizer(Tokenizer $tokenizer)
	{
		$this->tokenizer = $tokenizer;
		$this->queryTokens();
	}

	private function queryTokens()
	{
		$tokenVector = $this->tokenizer->getTokenVector();
		$tokens = array_keys($tokenVector);
		foreach ($tokens as $token) {
			dump('TODO implement search based on : pageRank = tfForDoc("query") * idf("query")');
		}
	}
}