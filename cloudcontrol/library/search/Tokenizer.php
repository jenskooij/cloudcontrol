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
		$this->tokenizeTitle();
		$this->tokenizeFields();
		$this->tokenizeBricks();
		$this->tokenizeDynamicBricks();
		arsort($this->tokenVector);
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

	private function tokenizeFields()
	{
		$fields = $this->document->fields;
		foreach ($fields as $field) {
			// TODO determine fieldType and take action according. For example should handle documents, images or files differently.
			$this->tokenizeField($field);
		}
	}

	private function tokenizeField($field)
	{
		foreach ($field as $value) {
			$this->tokenizeString($value);
		}
	}

	private function tokenizeBricks()
	{
		$bricks = $this->document->bricks;
		foreach ($bricks as $brickSlug => $brick) {
			$this->tokenizeBrick($brick);
		}
	}

	private function tokenizeBrick($brick)
	{
		$fields  = $brick->fields;
		foreach ($fields as $field) {
			// TODO determine fieldType and take action according. For example should handle documents, images or files differently.
			$this->tokenizeField($field);
		}
	}

	private function tokenizeDynamicBricks()
	{
		$dynamicBricks = $this->document->dynamicBricks;
		foreach ($dynamicBricks as $brick) {
			$this->tokenizeBrick($brick);
		}
	}

	public function getTokens()
	{
		return $this->tokenVector;
	}
}