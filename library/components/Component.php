<?php
namespace library\components
{
	interface Component
	{
		function __construct($template, \library\cc\Request $request, $parameters);
		
		function run(\library\storage\Storage $storage);
		function render();
		
		function get();
	}
}
?>