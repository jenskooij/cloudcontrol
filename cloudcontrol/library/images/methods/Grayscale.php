<?php
/**
 * Resize
 *
 * @Author: Platform 0
 * @Package: JNS MVC
 */
 
namespace library\images\methods
{
	use \library\images\IMethod;

	class Grayscale extends IMethod
	{		
		public function Execute($imageResource)
		{			
			// Preserve transparency
			imagecolortransparent($imageResource, imagecolorallocatealpha($imageResource, 0, 0, 0, 127));
			imagealphablending($imageResource, false);
			imagesavealpha($imageResource, true);
			
			// Make grayscale
			imagefilter($imageResource, IMG_FILTER_GRAYSCALE);
			
			return $imageResource;
		}
	}
}