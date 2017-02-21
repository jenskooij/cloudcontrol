<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:38
 */

namespace library\search;


use library\storage\Document;

class Tokenizer
{
	/**
	 * @var Document
	 */
	protected $document;

	private static $ignoreTokens = array('en');

	protected $tokenVector = array();

	/**
	 * Tokenizer constructor.
	 *
	 * @param \library\storage\Document $document
	 */
	public function __construct(Document $document)
	{
		$this->document = $document;
		$this->tokenize();
	}

	private function tokenize()
	{
		// TODO tokenize title
		$this->tokenizeTitle();
		// TODO tokenize fields
		// TODO tokenize bricks
		// TODO tokenize dynamicBricks
	}

	private function tokenizeTitle()
	{
		$this->tokenizeString($this->document->title);
	}

	private function tokenizeString($string)
	{
		$string = new CharacterFilter($string);
		$tokens = explode(' ', $string);
		foreach ($tokens as $token) {
			if (isset($this->tokenVector[$token])) {
				$this->tokenVector[$token] += 1;
			} else {
				$this->tokenVector[$token] = 1;
			}
		}
	}
}