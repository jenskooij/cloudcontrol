<?php
namespace library\components
{

	use library\cc\Request;
	use library\storage\Storage;

	/**
	 * Interface Component
	 * @package library\components
	 */
	interface Component
	{
		/**
		 * Component constructor.
		 *
		 * @param                     $template
		 * @param Request $request
		 * @param                     $parameters
		 * @param                     $matchedSitemapItem
		 */
		function __construct($template, Request $request, $parameters, $matchedSitemapItem);

		/**
		 * @param Storage $storage
		 */
		function run(Storage $storage);

		/**
		 * @return void
		 */
		function render();

		/**
		 * @return mixed
		 */
		function get();

		/**
		 * @return \stdClass
		 */
		function getParameters();
	}
}