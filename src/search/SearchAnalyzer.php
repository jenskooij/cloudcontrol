<?php
/**
 * Created by jensk on 18-5-2018.
 */

namespace CloudControl\Cms\search;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\search\searchanalyzer\SearchHistoryItem;
use CloudControl\Cms\search\searchanalyzer\SearchSession;

class SearchAnalyzer extends SearchDbConnected
{
    public function analyze($query, Search $search)
    {
        $this->endPreviousSearchSession($query);
        $searchSession = $this->createSearchSession($query, $search);

        $_SESSION[self::class] = $searchSession;
    }

    public function analyzeSearchJourney()
    {
        if (self::isSearchAnalysisInProgress()) {
            /** @var SearchSession $searchSession */
            $searchSession = $_SESSION[self::class];
            if ($searchSession instanceof SearchSession) {
                $searchHistoryItem = new SearchHistoryItem(Request::$requestUri);
                if ($this->backOnResults()) {
                    $this->previousRequestDidntConvert($searchSession);
                }
                $lastInsertId = $this->storeSearchHistoryItem($searchSession, $searchHistoryItem);
                $searchHistoryItem->setRowId($lastInsertId);
                $searchSession->addToHistory($searchHistoryItem);
            }
        }
    }

    /**
     * Wheter or not the user has returned to search results page
     *
     * @return bool
     */
    public function backOnResults()
    {
        /** @var SearchSession $searchSession */
        $searchSession = $_SESSION[self::class];
        return Request::$requestUri === $searchSession->getSearchResultPage();
    }

    /**
     * Set conversion of previous request to false
     * @param SearchSession $searchSession
     */
    private function previousRequestDidntConvert($searchSession)
    {
        $searchHistoryItem = $searchSession->getPreviousHistoryItem();
        if ($searchHistoryItem === null) {
            return;
        }

        $searchHistoryItem->setConversionFalse();
        $this->updateSearchHistoryItem($searchHistoryItem);
    }

    public static function isSearchAnalysisInProgress()
    {
        return isset($_SESSION[self::class]);
    }

    private function endPreviousSearchSession($query)
    {
        if (self::isSearchAnalysisInProgress()) {
            /** @var SearchSession $searchSession */
            $searchSession = $_SESSION[self::class];

            if ($searchSession->getQuery() !== $query) {
                $this->previousRequestDidntConvert($searchSession);
            }
        }
    }

    /**
     * @param $query
     * @param Search $search
     * @return SearchSession
     */
    protected function createSearchSession($query, $search)
    {
        $tokenizer = $search->getTokenizer();
        $tokenizer->getTokenVector();

        $active = new SearchSession();
        $active->setQuery($query);
        $active->setSearchResultPage(Request::$requestUri);
        $tokenVector = $tokenizer->getTokenVector();
        $active->setTokens(array_keys($tokenVector));
        $active->setResultCount($search->getResultCount());

        /** @var SearchSession $activeSearchSession */
        $activeSearchSession = isset($_SESSION[self::class]) ? $_SESSION[self::class] : null;

        if (self::isSearchAnalysisInProgress() && $activeSearchSession->getQuery() !== $query) {
            $this->storeSearchSession($active);
        }

        return $active;
    }

    /**
     * @param SearchSession $searchSession
     * @param SearchHistoryItem $searchHistoryItem
     * @return string
     */
    private function storeSearchHistoryItem($searchSession, $searchHistoryItem)
    {
        $sql = $this->getStoreSearchHistoryItemSql();

        $parameters = array(
            ':sessionId' => $searchSession->getSessionId(),
            ':timestamp' => time(),
            ':requestUri' => $searchHistoryItem->getRequestUri(),
            ':conversion' => $searchHistoryItem->getConversion()
        );

        return $this->executeInsertQuery($sql, $parameters);
    }

    private function getStoreSearchHistoryItemSql()
    {
        return '
        INSERT INTO search_analysis (`sessionId`, `timestamp`, `requestUri`, `conversion`) VALUES (
          :sessionId,
          :timestamp,
          :requestUri,
          :conversion
        );
        ';
    }

    /**
     * @param SearchSession $searchSession
     * @return string
     */
    private function storeSearchSession($searchSession)
    {
        $sql = $this->getStoreSearchSessionSql();

        $parameters = array(
            ':sessionId' => $searchSession->getSessionId(),
            ':timestamp' => time(),
            ':query' => $searchSession->getQuery(),
            ':resultCount' => $searchSession->getResultCount()
        );

        return $this->executeInsertQuery($sql, $parameters);
    }

    /**
     * @param $sql
     * @param $parameters
     * @return string
     */
    private function executeInsertQuery($sql, $parameters)
    {
        $db = $this->getSearchDbHandle();

        $stmt = $db->prepare($sql);

        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            throw new \RuntimeException($errorInfo[2]);
        }

        if ($stmt->execute($parameters) === false) {
            $errorInfo = $stmt->errorInfo();
            throw new \RuntimeException($errorInfo[2] . ' for sql: ' . $sql . ' with parameters: ' . print_r($parameters, true));
        }
        return $db->lastInsertId();
    }

    private function getStoreSearchSessionSql()
    {
        return '
        INSERT INTO search_analysis (`sessionId`, `timestamp`, `query`, `resultCount`) VALUES (
          :sessionId,
          :timestamp,
          :query,
          :resultCount
        );
        ';
    }

    /**
     * @param SearchHistoryItem $searchHistoryItem
     * @return string
     */
    private function updateSearchHistoryItem($searchHistoryItem)
    {
        $sql = 'UPDATE search_analysis SET `conversion` = :conversion WHERE `rowid` = :rowid';

        $parameters = array(
            ':conversion' => $searchHistoryItem->getConversion(),
            ':rowid' => $searchHistoryItem->getRowId()
        );

        return $this->executeInsertQuery($sql, $parameters);
    }
}