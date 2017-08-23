<?php
/**
 * User: Jens
 * Date: 12-3-2017
 * Time: 23:12
 */

namespace CloudControl\Cms\search\filters;

use CloudControl\Cms\search\Filter;

abstract class StopWordsFilter implements Filter
{
    protected $tokens;
    protected $stopWords = array();

    /**
     * StopWordsFilter constructor.
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
        foreach ($this->stopWords as $stopWord) {
            foreach ($this->tokens as $field => $tokens) {
                if (isset($tokens[$stopWord])) {
                    $tokens[$stopWord] = null;
                    unset($tokens[$stopWord]);
                    $tokens = array_filter($tokens);
                    asort($tokens);
                }

                $this->tokens[$field] = $tokens;
            }
        }
        $this->tokens = array_filter($this->tokens);
        asort($this->tokens);
        return $this->tokens;
    }
}