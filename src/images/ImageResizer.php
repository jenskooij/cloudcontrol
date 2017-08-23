<?php
namespace CloudControl\Cms\images
{

    use CloudControl\Cms\cc\StringUtil;

    /**
	 * Class ImageResizer
	 * @package CloudControl\Cms\images
	 */
	class ImageResizer
	{
		protected $imageSet;

		/**
		 * ImageResizer constructor.
		 *
		 * @param $imageSet
		 */
		public function __construct($imageSet)
		{
			$this->imageSet = $imageSet;
		}

		/**
		 * @param $imagePath
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function applyImageSetToImage($imagePath)
		{
			$returnFileNames = array();
			$filename = '';
			if (file_exists($imagePath)) {
				foreach ($this->imageSet as $set) {
					if ($set->method == 'resize') {
						$filename = $this->resize($imagePath, $set->width, $set->height);
					} elseif ($set->method == 'smartcrop') {
						$filename = $this->smartcrop($imagePath, $set->width, $set->height);
					} elseif ($set->method == 'boxcrop') {
						$filename = $this->boxcrop($imagePath, $set->width, $set->height);
					}
					$returnFileNames[$set->slug] = $filename;
				}
				return $returnFileNames;
			} else {
				throw new \Exception('Image doesnt exist: ' . $imagePath);
			}
		}

		/**
		 * @param string $imagePath
		 * @param string $width
		 * @param string $height
		 * @return string
		 * @throws \Exception
		 */
		public function resize($imagePath='', $width='',$height='')
		{
			$modifier = '-r' . $width . 'x' . $height;
			return $this->applyMethod('Resize', $imagePath, $width,$height, $modifier);
		}

		/**
		 * @param string $imagePath
		 * @param string $width
		 * @param string $height
		 * @return string
		 * @throws \Exception
		 */
		public function smartcrop($imagePath='', $width='',$height='')
		{
			$modifier = '-s' . $width . 'x' . $height;
			return $this->applyMethod('SmartCrop', $imagePath, $width,$height, $modifier);
		}

		/**
		 * @param string $imagePath
		 * @param string $width
		 * @param string $height
		 * @return string
		 * @throws \Exception
		 */
		public function boxcrop($imagePath='', $width='',$height='')
		{
			$modifier = '-b' . $width . 'x' . $height;
			return $this->applyMethod('BoxCrop', $imagePath, $width,$height, $modifier);
		}

		/**
		 * @param        $imagePath
		 * @param string $modifier
		 *
		 * @return string
		 */
		private function modifyName($imagePath, $modifier='')
		{
			$filename = basename($imagePath);
			$path = dirname($imagePath);
			$fileParts = explode('.', $filename);
			if (count($fileParts) > 1) {
				$extension = end($fileParts);
				array_pop($fileParts);
				$fileNameWithoutExtension = implode('-', $fileParts);
				$fileNameWithoutExtension = StringUtil::slugify($fileNameWithoutExtension);
				$filename = $fileNameWithoutExtension . $modifier  . '.' . $extension;
			} else {
				$filename = StringUtil::slugify($filename);
			}

			if (file_exists($path . '/' . $filename)) {
				$fileParts = explode('.', $filename);
				if (count($fileParts) > 1) {
					$extension = end($fileParts);
					array_pop($fileParts);
					$fileNameWithoutExtension = implode('-', $fileParts);
					$fileNameWithoutExtension .= '-copy';
					$filename = $fileNameWithoutExtension . '.' . $extension;
				} else {
					$filename .= '-copy';
				}
				return $this->modifyName($path . '/' . $filename);
			}
			return $path . '/' . $filename;
		}

		private function applyMethod($method, $imagePath, $width, $height, $modifier)
		{
			$method = 'CloudControl\Cms\\images\\methods\\' . $method;
			$destination = $this->modifyName($imagePath, $modifier);
			if (file_exists($imagePath)) {
				$image = new Image();
				$image->loadImage($imagePath);
				$resize = new $method();
				$resize->SetWidth($width);
				$resize->SetHeight($height);
				$resizedImageResource = $resize->Execute($image->getImageResource());
				$resizedImage = new Image();
				$resizedImage->loadImage($resizedImageResource);
				$resizedImage->saveImage($destination, $resizedImage->getImageMimeType($imagePath), 80);
				return basename($destination);
			} else {
				throw new \Exception('Image doesnt exist: ' . $imagePath);
			}
		}
	}
}