<?php
/**
 * Water mark
 * Adds a watermark to an image
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */

namespace CloudControl\Cms\images\methods {

    use CloudControl\Cms\images\Image;
    use CloudControl\Cms\images\IMethod;

    class Watermark extends IMethod
    {
        protected $_x = 0;
        protected $_y = 0;
        protected $_transparency = 100;

        /**
         * @var Image
         */
        protected $_watermark;

        protected function init()
        {
        }

        /**
         * Sets transparency for watermark
         *
         * @param  int $transparency
         * @return self
         */
        public function SetTransparency($transparency)
        {
            $this->_transparency = (int)$transparency;
            return $this;
        }

        /**
         * Use build-in logic to position the watermark
         *
         * @param    string $x
         * @param    string $y
         * @return    self
         */
        public function SetPosition($x, $y)
        {
            $this->SetX($x);
            $this->SetY($y);
            return $this;
        }

        /**
         * Use build-in logic to position the x of watermark
         *
         * @param    resource $imageResource
         * @return    self
         */
        protected function calculateX($imageResource)
        {
            if ((int)$this->_x === $this->_x) return $this->_x;

            $x = strtolower($this->_x);

            $imageWidth = imagesx($imageResource);
            $watermarkWidth = imagesx($this->GetWatermark()->getImageResource());

            if ($x == 'left') {
                $x = 0;
            } elseif ($x == 'center') {
                $x = $imageWidth / 2 - ($watermarkWidth / 2);
            } elseif ($x == 'right') {
                $x = $imageWidth - $watermarkWidth;
            }
            return (int)$x;
        }

        /**
         * Use build-in logic to position the y of watermark
         *
         * @param    resource $imageResource
         * @return    self
         */
        public function calculateY($imageResource)
        {
            if ((int)$this->_y === $this->_y) return $this->_y;

            $y = strtolower($this->_y);

            $imageHeight = imagesy($imageResource);
            $watermarkHeight = imagesy($this->GetWatermark()->getImageResource());

            if ($y == 'top') {
                $y = 0;
            } elseif ($y == 'center') {
                $y = $imageHeight / 2 - ($watermarkHeight / 2);
            } elseif ($y == 'bottom') {
                $y = $imageHeight - $watermarkHeight;
            }
            return (int)$y;
        }

        /**
         * Sets the image that will be used as watermark
         *
         * @param Image $image
         * @return Watermark
         */
        public function SetWatermark(Image $image)
        {
            $this->_watermark = $image;
            return $this;
        }

        /**
         * Returns the watermark.
         * Throws an Exception if it's not set or if it's not an \CloudControl\Cms\image\Image
         * @return \images\Image
         * @throws \Exception
         */
        public function GetWatermark()
        {
            if ($this->_watermark == null) throw new \Exception('A watermark is not set. Please supply a \CloudControl\Cms\image\Image using $this->SetWatermark');
            return $this->_watermark;
        }

        /**
         * Set the x
         *
         * @param  int | string $x
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
         * @param  int | string $y
         * @return self
         */
        public function SetY($y)
        {
            $this->_y = $y;
            return $this;
        }

        public function Execute($imageResource)
        {
            $watermark = $this->GetWatermark();
            $watermarkWidth = imagesx($watermark->getImageResource());
            $watermarkHeight = imagesy($watermark->getImageResource());

            $x = $this->calculateX($imageResource);
            $y = $this->calculateY($imageResource);

            $imageWidth = imagesx($imageResource);
            $imageHeight = imagesy($imageResource);

            $new = imagecreatetruecolor($imageWidth, $imageHeight);

            // Preserve transparency of the image
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);

            // Preserve transparency of the watermark
            imagecolortransparent($watermark->getImageResource(), imagecolorallocatealpha($watermark->getImageResource(), 0, 0, 0, 127));
            imagealphablending($watermark->getImageResource(), false);
            imagesavealpha($watermark->getImageResource(), true);

            imagealphablending($new, true);
            imagealphablending($watermark->getImageResource(), true);

            imagecopy($new, $imageResource, 0, 0, 0, 0, $imageWidth, $imageHeight);
            imagecopymerge($new, $watermark->getImageResource(), $x, $y, 0, 0, $watermarkWidth, $watermarkHeight, $this->_transparency);

            return $new;
        }
    }
}