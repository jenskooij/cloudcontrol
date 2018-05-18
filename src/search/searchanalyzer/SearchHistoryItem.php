<?php
/**
 * Created by jensk on 18-5-2018.
 */

namespace CloudControl\Cms\search\searchanalyzer;


class SearchHistoryItem
{
    protected $requestUri;
    protected $conversion = 'possibly';
    protected $rowId;

    /**
     * SearchHistoryItem constructor.
     * @param $requestUri
     */
    public function __construct($requestUri)
    {
        $this->requestUri = $requestUri;
    }


    public function setConversionPossibly()
    {
        $this->conversion = 'possibly';
    }

    public function setConversionFalse()
    {
        $this->conversion = 'false';
    }

    public function setConversionTrue()
    {
        $this->conversion = 'true';
    }

    /**
     * @return mixed
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @return string
     */
    public function getConversion()
    {
        return $this->conversion;
    }

    public function setRowId($rowId)
    {
        $this->rowId = $rowId;
    }

    /**
     * @return mixed
     */
    public function getRowId()
    {
        return $this->rowId;
    }


}