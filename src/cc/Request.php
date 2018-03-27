<?php

namespace CloudControl\Cms\cc {

    /**
     * Class Request
     * @package CloudControl\Cms\cc
     */
    class Request
    {

        /**
         * @var string
         */
        public static $subfolders;
        /**
         * @var string
         */
        public static $requestUri;
        /**
         * @var string
         */
        public static $relativeUri;
        /**
         * @var string
         */
        public static $queryString;
        /**
         * @var array
         */
        public static $requestParameters;
        /**
         * @var array
         */
        public static $post = array();
        /**
         * @var array
         */
        public static $get = array();
        /**
         * @var array|mixed
         */
        public static $argv;
        /**
         * @var array
         */
        private $statics;

        /**
         * Request constructor.
         */
        public function __construct()
        {
            $rootPath = str_replace('\\', '/', realpath(str_replace('\\', '/', dirname(__FILE__)) . '/../../') . '/');

            if (PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli') {
                self::$subfolders = '/';
            } else {
                self::$subfolders = '/' . str_replace('//', '/',
                        str_replace(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), "", $rootPath));
                self::$subfolders = str_replace('//', '/', self::$subfolders);
                self::$subfolders = str_replace('vendor/getcloudcontrol/cloudcontrol/', '', self::$subfolders);
            }

            if (PHP_SAPI === 'cli') {
                array_shift(self::$argv);
                self::$queryString = '';
                self::$requestUri = self::$subfolders . implode('/', self::$argv);
            } else {
                self::$requestUri = $_SERVER['REQUEST_URI'];
                self::$queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
            }
            if (self::$subfolders === '/') {
                self::$relativeUri = str_replace('?' . self::$queryString, '', substr(self::$requestUri, 1));
            } else {
                self::$relativeUri = str_replace('?' . self::$queryString, '',
                    str_replace(self::$subfolders, '', self::$requestUri));
            }

            self::$requestParameters = explode('/', self::$relativeUri);

            self::$get = $_GET;
            self::$post = $_POST;

            $this->statics = array(
                'subfolders' => self::$subfolders,
                'requestUri' => self::$requestUri,
                'relativeUri' => self::$relativeUri,
                'queryString' => self::$queryString,
                'requestParameters' => self::$requestParameters,
                'post' => self::$post,
                'get' => self::$get
            );
        }

        public static function isSecure()
        {
            return
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || $_SERVER['SERVER_PORT'] == 443
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        }

        public static function isLocalhost()
        {
            $ipchecklist = array("localhost", "127.0.0.1", "::1");
            return in_array($_SERVER['REMOTE_ADDR'], $ipchecklist, true);
        }
    }
}