<?php
namespace library\images
{

	use library\images\methods\BoxCrop;
	use library\images\methods\Resize;
	use library\images\methods\SmartCrop;

	/**
	 * Class ImageResizer
	 * @package library\images
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

		public function applyImageSetToImage($imagePath)
		{
			$returnFileNames = array();
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

		public function resize($imagePath='', $width='',$height='')
		{
			$modifier = '-r' . $width . 'x' . $height;
			$destination = $this->modifyName($imagePath, $modifier);
			if (file_exists($imagePath)) {
				$image = new Image();
				$image->LoadImage($imagePath);
				$resize = new Resize();
				$resize->SetWidth($width);
				$resize->SetHeight($height);
				$resizedImageResource = $resize->Execute($image->GetImageResource());
				$resizedImage = new Image();
				$resizedImage->LoadImage($resizedImageResource);
				$resizedImage->SaveImage($destination, $resizedImage->GetImageMimeType($imagePath), 80);
				return basename($destination);
			} else {
				throw new \Exception('Image doesnt exist: ' . $imagePath);
			}
		}

		public function smartcrop($imagePath='', $width='',$height='')
		{
			$modifier = '-s' . $width . 'x' . $height;
			$destination = $this->modifyName($imagePath, $modifier);
			if (file_exists($imagePath)) {
				$image = new Image();
				$image->LoadImage($imagePath);
				$resize = new SmartCrop();
				$resize->SetWidth($width);
				$resize->SetHeight($height);
				$resizedImageResource = $resize->Execute($image->GetImageResource());
				$resizedImage = new Image();
				$resizedImage->LoadImage($resizedImageResource);
				$resizedImage->SaveImage($destination, $resizedImage->GetImageMimeType($imagePath), 80);
				return basename($destination);
			} else {
				throw new \Exception('Image doesnt exist: ' . $imagePath);
			}
		}

		public function boxcrop($imagePath='', $width='',$height='')
		{
			$modifier = '-b' . $width . 'x' . $height;
			$destination = $this->modifyName($imagePath, $modifier);
			if (file_exists($imagePath)) {
				$image = new Image();
				$image->LoadImage($imagePath);
				$resize = new BoxCrop();
				$resize->SetWidth($width);
				$resize->SetHeight($height);
				$resizedImageResource = $resize->Execute($image->GetImageResource());
				$resizedImage = new Image();
				$resizedImage->LoadImage($resizedImageResource);
				$resizedImage->SaveImage($destination, $resizedImage->GetImageMimeType($imagePath), 80);
				return basename($destination);
			} else {
				throw new \Exception('Image doesnt exist: ' . $imagePath);
			}
		}

		private function modifyName($imagePath, $modifier)
		{
			$filename = basename($imagePath);
			$path = dirname($imagePath);
			$fileParts = explode('.', $filename);
			if (count($fileParts) > 1) {
				$extension = end($fileParts);
				array_pop($fileParts);
				$fileNameWithoutExtension = implode('-', $fileParts);
				$fileNameWithoutExtension = slugify($fileNameWithoutExtension);
				$filename = $fileNameWithoutExtension . $modifier  . '.' . $extension;
			} else {
				$filename = slugify($filename);
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

		private function validateFilename($filename, $path)
		{

		}

	}
}