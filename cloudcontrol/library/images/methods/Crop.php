<?php
/**
 * Crop
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */
 
namespace library\images\methods
{
	use \library\images\IMethod;

	class Crop extends IMethod
	{
		protected $_width;
		protected $_height;
		protected $_x;
		protected $_y;
		
		protected $_destWidth;
		protected $_destHeight;
		protected $_destX = 0.0;
		protected $_destY = 0.0;

		/**
		 * @var null|array
		 */
		protected $_backgroundColor = null;
	
		public function init()
		{}
		
		/**
		 * Set the width
		 *
		 * @param  int $width
		 * @return self
		 */
		public function SetWidth($width)
		{
			$this->_width = intval($width);
			$this->_destWidth = intval($width);
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
			$this->_destHeight = intval($height);
			return $this;
		}
		
		/**
		 * Set the x
		 *
		 * @param  int $x
		 * @return self
		 */
		public function SetX($x)
		{
			$this->_x = $x;
			return $this;
		}
		
		/**
		 * Set the y
		 *
		 * @param  int $y
		 * @return self
		 */
		public function SetY($y)
		{
			$this->_y = $y;
			return $this;
		}

		/**
		 * If neccesary, fill the background color
		 * with this color
		 *
		 * @param int $r Red
		 * @param int $g Green
		 * @param int $b Blue
		 *
		 * @return $this
		 */
		public function FillBackground($r, $g, $b)
		{
			$this->_backgroundColor = array(intval($r), intval($g), intval($b));
			return $this;
		}

		/**
		 * @param resource $imageResource
		 *
		 * @return resource
		 */
		public function Execute($imageResource)
		{
			// Create the new image
			$new = imagecreatetruecolor($this->_width, $this->_height);
			
			if ($this->_backgroundColor !== null) {
				$fill = imagecolorallocate($new, $this->_backgroundColor[0], $this->_backgroundColor[1], $this->_backgroundColor[2]);
				imagefill($new, 0, 0, $fill);
			}

			// Preserve transparency
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
			
			imagecopyresampled($new, $imageResource, $this->_destX, $this->_destY, $this->_x, $this->_y, $this->_destWidth, $this->_destHeight, $this->_destWidth, $this->_destHeight);
			
			return $new;
		}
	}
}