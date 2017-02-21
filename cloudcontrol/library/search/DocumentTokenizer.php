<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:38
 */

namespace library\search;


use library\storage\Document;

class DocumentTokenizer
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
		$this->tokenVector = array_filter($this->tokenVector);
		arsort($this->tokenVector);
	}

	private function tokenizeTitle()
	{
		$filteredString = new CharacterFilter($this->document->title);
		$tokenizer = new Tokenizer($filteredString);
		$this->addTokenVectorToVector($tokenizer->getTokenVector());
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
			$filteredString = new CharacterFilter($value);
			$tokenizer = new Tokenizer($filteredString);
			$this->addTokenVectorToVector($tokenizer->getTokenVector());
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

	/**
	 * @param     $token
	 * @param int $count
	 */
	private function addTokenToVector($token, $count = 1)
	{
		if (!empty($token)) {
			if (isset($this->tokenVector[$token])) {
				$this->tokenVector[$token] += $count;
			} else {
				$this->tokenVector[$token] = $count;
			}
		}
	}

	private function addTokenVectorToVector($tokenVector)
	{
		foreach ($tokenVector as $token => $count) {
			$this->addTokenToVector($token, $count);
		}
	}
}