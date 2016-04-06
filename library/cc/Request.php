<?php
namespace library\cc
{
	/**
	 * Class Request
	 * @package library\cc
	 */
	class Request {

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
		 * @var array
		 */
		private $statics = array();

		/**
		 * Request constructor.
		 */
		public function __construct() 
		{
			global $rootPath;
			
			self::$subfolders = '/' . str_replace('//', '/', str_replace(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), "", $rootPath));
			self::$requestUri = $_SERVER['REQUEST_URI'];
			self::$queryString = $_SERVER['QUERY_STRING'];
			self::$relativeUri = str_replace('?' . self::$queryString, '', str_replace(self::$subfolders, '', self::$requestUri));
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
	}
}
?>