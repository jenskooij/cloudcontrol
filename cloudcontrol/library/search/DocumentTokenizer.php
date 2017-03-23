<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 10:38
 */

namespace library\search;


use library\storage\Document;
use library\storage\Storage;

class DocumentTokenizer
{
	/**
	 * @var Document
	 */
	protected $document;

	/**
	 * @var array
	 */
	protected $tokenVector = array();
	protected $storage;

	/**
	 * Tokenizer constructor.
	 *
	 * @param \library\storage\Document $document
	 * @param Storage                   $storage
	 */
	public function __construct(Document $document, Storage $storage)
	{
		$this->document = $document;
		$this->storage = $storage;
		$this->tokenize();
	}

	/**
	 * Execute tokenization of all document fields
	 */
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
		$documentDefinition = $this->storage->getDocumentTypes()->getDocumentTypeBySlug($this->document->documentTypeSlug);
		foreach ($fields as $fieldName => $field) {
			$fieldType = $this->getFieldType($fieldName, $documentDefinition);
			$this->tokenizeField($field, $fieldName, $fieldType);
		}
	}

	private function tokenizeField($field, $fieldName, $fieldType)
	{
		foreach ($field as $value) {
			// Only index fields that contain text
			if (in_array($fieldType, array('String', 'Text', 'Rich Text'))) {
				$filteredString = new CharacterFilter($value);
				$tokenizer = new Tokenizer($filteredString);
				$this->addTokenVectorToVector($tokenizer->getTokenVector(), $fieldName);
			}
		}
	}

	private function tokenizeBricks()
	{
		$bricks = $this->document->bricks;
		foreach ($bricks as $brickSlug => $bricks) {
			foreach ($bricks as $brick) {
				$this->tokenizeBrick($brick, $brickSlug);
			}
		}
	}

	private function tokenizeBrick($brick, $brickSlug)
	{
		$fields  = $brick->fields;
		$brickDefinition = $this->storage->getBricks()->getBrickBySlug($brick->type);
		foreach ($fields as $fieldName => $field) {
			$fieldType = $this->getFieldType($fieldName, $brickDefinition);
			$this->tokenizeField($field, $brickSlug . '__' . $fieldName, $fieldType);
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
	 * Add a token to the existing tokenvector
	 * @param     		$token
	 * @param string    $field
	 * @param int 		$count
	 */
	private function addTokenToVector($token, $field, $count = 1)
	{
		if (!empty($token)) {
			if (isset($this->tokenVector[$field][$token])) {
				$this->tokenVector[$field][$token] += $count;
			} else {
				$this->tokenVector[$field][$token] = $count;
			}
		}
	}

	/**
	 * Add a complete token vector to the existing one.
	 * @param $tokenVector
	 * @param $field
	 */
	private function addTokenVectorToVector($tokenVector, $field)
	{
		foreach ($tokenVector as $token => $count) {
			$this->addTokenToVector($token, $field, $count);
		}
	}

	/**
	 * Get the type for a field
	 * @param $fieldName
	 * @param $documentDefinition
	 * @return mixed
	 * @throws \Exception
	 */
	private function getFieldType($fieldName, $documentDefinition)
	{
		foreach ($documentDefinition->fields as $fieldTypeDefinition) {
			if ($fieldTypeDefinition->slug === $fieldName) {
				return $fieldTypeDefinition->type;
			}
		}

		throw new \Exception('Unknown field type for field' . $fieldName . ' in document ' . $this->document->path);
	}
}