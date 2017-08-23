<?php
/**
 * Image Method Interface
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */
 
namespace CloudControl\Cms\images
{
	abstract class IMethod
	{
		/**
		 * Method stub, use for executing the manipulation
		 * 
		 * @param  resource $imageResource
		 * @return resource
		 */
		abstract public function Execute($imageResource);
	}
}