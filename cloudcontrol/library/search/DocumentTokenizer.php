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
		$this->addTokenVectorToVector($tokenizer->getTokenVector(), 'title');
	}

	private function tokenizeFields()
	{
		$fields = $this->document->fields;
		foreach ($fields as $fieldName => $field) {
			// TODO determine fieldType and take action according. For example should handle documents, images or files differently.
			$this->tokenizeField($field, $fieldName);
		}
	}

	private function tokenizeField($field, $fieldName)
	{
		foreach ($field as $value) {
			$filteredString = new CharacterFilter($value);
			$tokenizer = new Tokenizer($filteredString);
			$this->addTokenVectorToVector($tokenizer->getTokenVector(), $fieldName);
		}
	}

	private function tokenizeBricks()
	{
		$bricks = $this->document->bricks;
		foreach ($bricks as $brickSlug => $brick) {
			$this->tokenizeBrick($brick, $brickSlug);
		}
	}

	private function tokenizeBrick($brick, $brickSlug)
	{
		$fields  = $brick->fields;
		foreach ($fields as $fieldName => $field) {
			// TODO determine fieldType and take action according. For example should handle documents, images or files differently.
			$this->tokenizeField($field, $brickSlug . '__' . $fieldName);
		}
	}

	private function tokenizeDynamicBricks()
	{
		$dynamicBricks = $this->document->dynamicBricks;
		foreach ($dynamicBricks as $key => $brick) {
			$this->tokenizeBrick($brick, 'dynamicBricks__' . $brick->type . $key);
		}
	}

	public function getTokens()
	{
		return $this->tokenVector;
	}

	/**
	 * @param     		$token
	 * @param string    $field
	 * @param int 		$count
	 */
	private function addTokenToVector($token, $field, $count = 1)
	{
		if (!empty($token)) {
			if (isset($this->tokenVector[$token])) {
				$this->tokenVector[$field][$token] += $count;
			} else {
				$this->tokenVector[$field][$token] = $count;
			}
		}
	}

	private function addTokenVectorToVector($tokenVector, $field)
	{
		foreach ($tokenVector as $token => $count) {
			$this->addTokenToVector($token, $field, $count);
		}
	}
}