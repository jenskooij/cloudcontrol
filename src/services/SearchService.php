<?php
/**
 * Created by jensk on 18-5-2018.
 */

namespace CloudControl\Cms\services;


use CloudControl\Cms\search\SearchAnalyzer;
use CloudControl\Cms\services\AbstractStorageService;
use CloudControl\Cms\storage\Storage;

class SearchService extends AbstractStorageService
{
    private static $instance;

    public function init(Storage $storage)
    {
        parent::init($storage);
        $searchAnalyzer = new SearchAnalyzer($storage);
        $searchAnalyzer->analyzeSearchJourney();
    }


    /**
     * SearchService constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return SearchService
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof SearchService) {
            self::$instance = new SearchService();
        }
        return self::$instance;
    }
}