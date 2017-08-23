<?php
namespace CloudControl\Cms\components
{

    use CloudControl\Cms\cc\Request;
    use CloudControl\Cms\storage\Storage;

    /**
	 * Interface Component
	 * @package CloudControl\Cms\components
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