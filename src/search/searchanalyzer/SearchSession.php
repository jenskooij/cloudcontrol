<?php
/**
 * Created by jensk on 18-5-2018.
 */

namespace CloudControl\Cms\search\searchanalyzer;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\util\StringUtil;

class SearchSession
{
    protected $sessionId;
    protected $sessionStartTime;
    protected $sessionDuration = 0;
    protected $sessionDurationHuman;
    protected $history = array();
    protected $query = '';
    protected $searchResultPage = '';
    protected $tokens = array();
    protected $resultCount;

    /**
     * SearchSession constructor.
     */
    public function __construct()
    {
        $this->sessionStartTime = time();
        $this->sessionId = md5(session_id() . $this->sessionStartTime);
    }

    public function addToHistory($searchHistoryItem)
    {
        $this->history[] = $searchHistoryItem;
        $this->sessionDuration = time() - $this->sessionStartTime;
        $this->sessionDurationHuman = StringUtil::timeElapsedString($this->sessionStartTime);
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @param string $searchResultPage
     */
    public function setSearchResultPage($searchResultPage)
    {
        $this->searchResultPage = $searchResultPage;
    }

    /**
     * @param array $tokens
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
    }



    /**
     * @return SearchHistoryItem|null
     */
    public function getPreviousHistoryItem()
    {
        $historyCount = count($this->history);
        if ($historyCount < 0) {
            return null;
        }
        if (isset($this->history[$historyCount - 1])) {
            return $this->history[$historyCount - 1];
        }
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param int $resultCount
     */
    public function setResultCount($resultCount)
    {
        $this->resultCount = $resultCount;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return int
     */
    public function getSessionStartTime()
    {
        return $this->sessionStartTime;
    }

    /**
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @return string
     */
    public function getSearchResultPage()
    {
        return $this->searchResultPage;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return mixed
     */
    public function getResultCount()
    {
        return $this->resultCount;
    }


}