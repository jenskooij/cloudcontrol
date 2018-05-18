<?php
/**
 * User: jensk
 * Date: 21-2-2017
 * Time: 16:55
 */

namespace CloudControl\Cms\components;

use CloudControl\Cms\search\CharacterFilter;
use CloudControl\Cms\search\Search;
use CloudControl\Cms\search\SearchAnalyzer;
use CloudControl\Cms\search\Tokenizer;
use CloudControl\Cms\storage\Storage;

class SearchComponent extends CachableBaseComponent
{
    protected $searchParameterName = 'q';
    protected $searchResultsParameterName = 'searchResults';
    protected $searchAnalyzerEnabled = true;

    const PARAMETER_SEARCH_PARAMETER_NAME = 'searchParameterName';
    const PARAMETER_SEARCH_RESULTS_PARAMETER_NAME = 'searchResultsParameterName';
    const PARAMETER_SEARCH_ANALYZER_ENABLED = 'searchAnalyzerEnabled';

    /**
     * @param \CloudControl\Cms\storage\Storage $storage
     * @throws \Exception
     */
    public function run(Storage $storage)
    {
        parent::run($storage);

        $this->checkParameters();

        $request = $this->request;
        if (isset($request::$get[$this->searchParameterName])) {
            $query = $request::$get[$this->searchParameterName];
            $filteredQuery = new CharacterFilter($query);
            $tokenizer = new Tokenizer($filteredQuery);
            $search = new Search($storage);
            $results = $search->getDocumentsForTokenizer($tokenizer);
            $this->searchAnalyzer($storage, $search, $query);
            $this->parameters[$this->searchResultsParameterName] = $results;
        }
    }

    /**
     * Checks to see if any parameters were defined in the cms and acts according
     */
    protected function checkParameters()
    {
        if (isset($this->parameters[self::PARAMETER_SEARCH_PARAMETER_NAME])) {
            $this->searchParameterName = $this->parameters[self::PARAMETER_SEARCH_PARAMETER_NAME];
        }

        if (isset($this->parameters[self::PARAMETER_SEARCH_RESULTS_PARAMETER_NAME])) {
            $this->searchParameterName = $this->parameters[self::PARAMETER_SEARCH_RESULTS_PARAMETER_NAME];
        }

        if (isset($this->parameters[self::PARAMETER_SEARCH_ANALYZER_ENABLED])) {
            $this->searchAnalyzerEnabled = $this->parameters[self::PARAMETER_SEARCH_ANALYZER_ENABLED] !== 'false';
        }
    }

    /**
     * @param Storage $storage
     * @param Search $search
     */
    private function searchAnalyzer(Storage $storage, Search $search, $query)
    {
        if ($this->searchAnalyzerEnabled) {
            $searchAnalyzer = new SearchAnalyzer($storage);
            $searchAnalyzer->analyze($query, $search);
        }
    }

}