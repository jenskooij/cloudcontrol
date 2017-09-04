<?php
/**
 * BoxCrop
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */

namespace CloudControl\Cms\images\methods {
    class BoxCrop extends Crop
    {
        /**
         * @param resource $imageResource
         * @return resource
         */
        public function Execute($imageResource)
        {
            // Define the origial width and height
            $originalWidth = imagesx($imageResource);
            $originalHeight = imagesy($imageResource);

            // Define the ratios
            $wRatio = $this->_width / $originalWidth;
            $hRatio = $this->_height / $originalHeight;

            // Define which ratio will be used, depending on which is the smallest side
            $ratio = min($hRatio, $wRatio);
            if ($ratio > 1) $ratio = 1;

            // Define sizes
            $this->_destWidth = floor($originalWidth * $ratio);
            $this->_destHeight = floor($originalHeight * $ratio);

            // Define margins
            $this->_destX = floor(($this->_width - $this->_destWidth) / 2);
            $this->_destY = floor(($this->_height - $this->_destHeight) / 2);

            // Execute the Crop method with the given parameters
            return parent::Execute($imageResource);
        }
    }
}