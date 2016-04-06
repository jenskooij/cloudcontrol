<?php
namespace library\cc
{
	class Request {
		
		public static $subfolders;
		public static $requestUri;
		public static $relativeUri;
		public static $queryString;
		public static $requestParameters;
		public static $post = array();
		public static $get = array();
		
		private $statics = array();
		
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