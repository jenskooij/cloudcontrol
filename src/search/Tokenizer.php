<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 16:23
 */

namespace CloudControl\Cms\search;

/**
 * Class Tokenizer
 * @package CloudControl\Cms\search
 */
class Tokenizer
{
	protected $inputString;
	protected $tokenVector = array();

	/**
	 * Tokenizer constructor.
	 *
	 * @param string $string Should preferably be parsed wit \CloudControl\Cms\search\CharacterFilter
	 * @see \CloudControl\Cms\search\CharacterFilter
	 */
	public function __construct($string)
	{
		$this->inputString = $string;
		$this->tokenize();
	}

	protected function tokenize()
	{
		$tokens = explode(' ', $this->inputString);
		foreach ($tokens as $token) {
			$this->addTokenToVector($token);
		}
	}

	protected function addTokenToVector($token)
	{
		if (!empty($token)) {
			if (isset($this->tokenVector[$token])) {
				$this->tokenVector[$token] += 1;
			} else {
				$this->tokenVector[$token] = 1;
			}
		}
	}

	/**
	 * @return array
	 */
	public function getTokenVector()
	{
		return $this->tokenVector;
	}


}