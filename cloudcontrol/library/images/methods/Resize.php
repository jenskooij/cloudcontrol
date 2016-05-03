<?php
/**
 * Resize
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */
 
namespace library\images\methods
{
	use \library\images\IMethod;

	class Resize extends IMethod
	{
		protected $_width;
		protected $_height;
		protected $_preserveAspectRatio = true;
		
		/**
		 * Set the width
		 *
		 * @param  int $width
		 * @return self
		 */
		public function SetWidth($width)
		{
			$this->_width = intval($width);
			return $this;
		}
		
		/**
		 * Set the height
		 *
		 * @param  int $height
		 * @return self
		 */
		public function SetHeight($height)
		{
			$this->_height = intval($height);
			return $this;
		}
		
		/**
		 * Sets wheter or not the aspect ratio of the original
		 * image needs to preserved
		 *
		 * @param  bool $bool
		 * @return self
		 */
		public function SetPreserveAspectRatio($bool)
		{
			$this->_preserveAspectRatio = (bool) $bool;
			return $this;
		}
		
		public function Execute($imageResource)
		{
			// Define the origial width and height
			$originalWidth = imagesx($imageResource);
			$originalHeight = imagesy($imageResource);
			
			// Define the ratio and adjust the width and height
			if ($this->_preserveAspectRatio) {
				$ratio = min($this->_width/$originalWidth, $this->_height/$originalHeight);
				$this->_width = $originalWidth * $ratio;
				$this->_height = $originalHeight * $ratio;
			}
			
			// Create the new image
			$new = imagecreatetruecolor($this->_width, $this->_height);
			
			// Preserve transparency
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
			imagealphablending($new, false);
			imagesavealpha($new, true);
			
			// Do the actual resizing
			imagecopyresampled($new, $imageResource, 0, 0, 0, 0, $this->_width, $this->_height, $originalWidth, $originalHeight);
			
			return $new;
		}
	}
}
?>