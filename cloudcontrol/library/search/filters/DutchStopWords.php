<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 11:51
 */

namespace library\search\filters;


use library\search\Filter;

class DutchStopWords implements Filter
{
	protected $tokens;
	protected $dutchStopWords = array('aan','af','al','alles','als','altijd','andere','ben','bij','daar','dan','dat','de','der','deze','die','dit','doch','doen','door','doorgaans','dus','een','eens','en','er','ge','geen','geweest','haar','had','heb','hebben','heeft','hem','het','hier','hij','hoe','hun','iemand','iets','ik','in','is','ja','je','kan','kon','kunnen','maar','me','meer','men','met','mij','mijn','moet','na','naar','niet','niets','nog','nu','of','om','omdat','ons','ook','op','over','reeds','te','tegen','toch','toen','tot','u','uit','uw','van','veel','voor','want','waren','was','wat','we','wel','werd','wezen','wie','wij','wil','worden','zal','ze','zei','zelf','zich','zij','zijn','zo','zodat','zonder','zou');

	/**
	 * DutchStopWords constructor.
	 *
	 * @param array $tokens
	 */
	public function __construct($tokens)
	{
		$this->tokens = $tokens;
	}

	/**
	 * @return array
	 */
	public function getFilterResults()
	{
		foreach ($this->dutchStopWords as $dutchStopWord) {
			if (isset($this->tokens[$dutchStopWord])) {
				unset($this->tokens[$dutchStopWord]);
			}
		}
		return $this->tokens;
	}
}