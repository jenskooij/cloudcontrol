<?php
/**
 * SmartCrop
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */
 
namespace library\images\methods
{
	class SmartCrop extends Crop
	{
		/**
		 * Use build-in logic to position the smartcrop
		 *
		 * @param 	string $x
		 * @param 	string $y
		 * @return 	self
		 */
		public function SetPosition($x, $y)
		{
			$this->SetX($x);
			$this->SetY($y);
			return $this;
		}
	
		public function Execute($imageResource)
		{
			// Define the origial width and height
			$originalWidth = imagesx($imageResource);
			$originalHeight = imagesy($imageResource);
			
			// Define the ratios
			$wRatio = $originalWidth / $this->_width;
			$hRatio = $originalHeight / $this->_height;
			
			// Define which ratio will be used, depending on which is the biggest side
			$ratio = $wRatio < $hRatio ? $wRatio : $hRatio;
			if ($ratio < 1) $ratio = 1;
			
			// Calculate the destination width, height, x and y
			$this->_destWidth = $originalWidth / $ratio;
			$this->_destHeight = $originalHeight / $ratio;
			$this->_destX = ($this->_width - $this->_destWidth) / 2;
			$this->_destY = ($this->_height - $this->_destHeight) / 2;
			
			// Define the origial width and height
			$originalWidth = imagesx($imageResource);
			$originalHeight = imagesy($imageResource);
			
			// Create the new image
			$new = imagecreatetruecolor($this->_width, $this->_height);
			
			if ($this->_backgroundColor !== false) {
				$fill = imagecolorallocate($new, $this->_backgroundColor[0], $this->_backgroundColor[1], $this->_backgroundColor[2]);
				imagefill($new, 0, 0, $fill);
			}

			// Preserve transparency
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
			
			imagecopyresampled($new, $imageResource, $this->_destX, $this->_destY, $this->_x, $this->_y, $this->_destWidth, $this->_destHeight, $originalWidth, $originalHeight);
			
			return $new;
		}
	}
}