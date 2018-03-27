<?php
/**
 * Image
 *
 * @Author : Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */

namespace CloudControl\Cms\images {

    class Image
    {
        /**
         * @var resource
         */
        private $_imageResource;

        /**
         * Load the a image resource into $this->_imageResource
         * automagically :-)
         *
         * @param resource|string $imageContainer
         *
         * @throws \Exception
         */
        public function loadImage($imageContainer)
        {
            if ($this->createImageResourceFromResource($imageContainer) ||
                $this->createImageResourceFromFile($imageContainer) ||
                $this->createImageResourceFromString($imageContainer)
            ) {
                return;
            }

            throw new \RuntimeException('Could not create image resource, accepted inputs are: "resource of type (gd)", path_to_image and "string". <br /><pre>' . var_export($imageContainer,
                    true) . '</pre>');
        }

        /**
         * @param $imageContainer
         * @return bool
         */
        private function createImageResourceFromResource($imageContainer)
        {
            if (is_resource($imageContainer) && get_resource_type($imageContainer) === 'gd') {
                $this->_imageResource = $imageContainer;
                return true;
            }
            return false;
        }

        /**
         * @param $imageContainer
         * @return bool
         * @throws \Exception
         */
        private function createImageResourceFromFile($imageContainer)
        {
            if (is_string($imageContainer) && file_exists($imageContainer)) {
                if ($this->getImageMimeType($imageContainer) === IMAGETYPE_BMP) {
                    $this->_imageResource = BitmapFactory::createImageFromBmp($imageContainer);
                } else {
                    $imageContent = file_get_contents($imageContainer);
                    $this->_imageResource = imagecreatefromstring($imageContent);
                }
                return true;
            }
            return false;
        }

        /**
         * @param $imageContainer
         * @return bool
         */
        private function createImageResourceFromString($imageContainer)
        {
            if (is_string($imageContainer)) {
                $this->_imageResource = imagecreatefromstring($imageContainer);
                return true;
            }
            return false;
        }

        /**
         * Saves the image to a file
         *
         * @param string $path
         * @param int $mimeTypeConstantValue
         * @param int $quality
         * @param resource $imageResource If no resource is given, uses $this->_imageResource
         *
         * @return bool
         * @throws \Exception
         */
        public function saveImage($path, $mimeTypeConstantValue, $quality = 100, $imageResource = null)
        {
            if ($imageResource === null) {
                $imageResource = $this->getImageResource();
            }

            if ($mimeTypeConstantValue == IMAGETYPE_GIF) {
                return imagegif($imageResource, $path);
            }

            if ($mimeTypeConstantValue == IMAGETYPE_JPEG) {
                return imagejpeg($imageResource, $path, $quality);
            }

            if ($mimeTypeConstantValue == IMAGETYPE_PNG) {
                return imagepng($imageResource, $path, ((int)($quality / 10) - 1));
            }

            throw new \RuntimeException('Not a valid mimetypeconstant given see function documentation');
        }

        /**
         * Returns either the Mime-Type Constant value
         * or the default extension for the detected mime-type;
         *
         * @see http://www.php.net/manual/en/function.image-type-to-mime-type.php
         *
         * @param string $imagePath
         * @param bool $getExtension
         *
         * @return integer
         */
        public function getImageMimeType($imagePath, $getExtension = false)
        {
            if (function_exists('exif_imagetype')) {
                $exif = exif_imagetype($imagePath);
            } else {
                $exif = false;
                if ((list($width, $height, $type, $attr) = getimagesize($imagePath)) !== false) {
                    $exif = $type;
                }
            }

            return $getExtension ? image_type_to_extension($exif) : $exif;
        }



        /**
         * Returns the image resource
         * @return resource
         * @throws \Exception
         */
        final public function getImageResource()
        {
            if (is_resource($this->_imageResource) && get_resource_type($this->_imageResource) === 'gd') {
                return $this->_imageResource;
            }

            throw new \RuntimeException('Image resource is not set. Use $this->LoadImage to load an image into the resource');
        }
    }
}