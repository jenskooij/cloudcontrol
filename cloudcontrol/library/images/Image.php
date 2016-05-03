<?php
/**
 * Image
 *
 * @Author: Jens Kooij
 * @Version: 1.0
 * @package: JNS MVC
 * @Licence: http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */
 
namespace library\images
{
	class Image
	{
		private $_imageResource;
		
		/**
		 * Load the a image resource into $this->_imageResource
		 * automagically :-)
		 *
		 * @param 	resource | string | path
		 * @throws 	\Exception
		 */
		public function LoadImage($imageContainer)
		{
			if (is_resource($imageContainer) && get_resource_type($imageContainer) === 'gd') {
				$this->_imageResource = $imageContainer;
			} elseif (is_string($imageContainer) && file_exists($imageContainer)) {
				if ($this->GetImageMimeType($imageContainer) == IMAGETYPE_BMP) {
					$this->_imageResource = $this->CreateImageFromBmp($imageContainer);
				} else {
					$imageContent = file_get_contents($imageContainer);
					$this->_imageResource = imagecreatefromstring($imageContent);
				}
			} elseif(is_string($imageContainer)) {
				$this->_imageResource = imagecreatefromstring($imageContainer);
			} else {
				throw new \Exception('Could not create image resource, accepted inputs are: "resource of type (gd)", path_to_image and "string". <br /><pre>' . var_export($imageContainer, true) . '</pre>');
			}
		}
		
		/**
		 * Saves the image to a file
		 *
		 * @param string 	$path
		 * @param int 		$mimeTypeConstantValue
		 * @param int		$quality
		 * @param null		$imageResource			If no resource is given, uses $this->_imageResource
		 */
		public function SaveImage($path, $mimeTypeConstantValue, $quality = 100, $imageResource = null)
		{
			if ($imageResource == null) {
				$imageResource = $this->GetImageResource();
			}
			
			if ($mimeTypeConstantValue == IMAGETYPE_GIF) {
				return imagegif($imageResource, $path);
			} elseif ($mimeTypeConstantValue == IMAGETYPE_JPEG)  {
				return imagejpeg($imageResource, $path, $quality);
			} elseif ($mimeTypeConstantValue == IMAGETYPE_PNG) {
				return imagepng($imageResource, $path, (intval($quality / 10) -1));
			}
			else {
				throw new \Exception('Not a valid mimetypeconstant given see function documentation');
			}
			return false;
		}
		
		/**
		 * Returns either the Mime-Type Constant value
		 * or the default extension for the detected mime-type;
		 *
		 * @see http://www.php.net/manual/en/function.image-type-to-mime-type.php
		 * @param string 	$imagePath
		 * @param bool 		$getExtension
		 */
		public function GetImageMimeType($imagePath, $getExtension = false)
		{
			if (function_exists('exif_imagetype')) {
				$exif = exif_imagetype($imagePath);
			} else {
				if ((list($width, $height, $type, $attr) = getimagesize($imagePath)) !== false ) {
					$exif = $type;
				} else {
					$exif = false;
				}
			}
			return $getExtension ? image_type_to_extension($exif) : $exif;
		}
		
		/**
		 * Create Image resource from Bitmap
		 *
		 * @see 	http://www.php.net/manual/en/function.imagecreatefromwbmp.php#86214
		 * @author 	alexander at alexauto dot nl 
		 * @param	string		$imagePath
		 * @return  resource
		 */
		public function CreateImageFromBmp($p_sFile)
		{
			//    Load the image into a string 
			$file    =    fopen($p_sFile,"rb"); 
			$read    =    fread($file,10); 
			while(!feof($file)&&($read<>"")) 
				$read    .=    fread($file,1024); 
			
			$temp    =    unpack("H*",$read); 
			$hex    =    $temp[1]; 
			$header    =    substr($hex,0,108); 
			
			//    Process the header 
			//    Structure: http://www.fastgraph.com/help/bmp_header_format.html 
			if (substr($header,0,4)=="424d") 
			{ 
				//    Cut it in parts of 2 bytes 
				$header_parts    =    str_split($header,2); 
				
				//    Get the width        4 bytes 
				$width            =    hexdec($header_parts[19].$header_parts[18]); 
				
				//    Get the height        4 bytes 
				$height            =    hexdec($header_parts[23].$header_parts[22]); 
				
				//    Unset the header params 
				unset($header_parts); 
			} 
			
			//    Define starting X and Y 
			$x                =    0; 
			$y                =    1; 
			
			//    Create newimage 
			$image            =    imagecreatetruecolor($width,$height); 
			
			//    Grab the body from the image 
			$body            =    substr($hex,108); 

			//    Calculate if padding at the end-line is needed 
			//    Divided by two to keep overview. 
			//    1 byte = 2 HEX-chars 
			$body_size        =    (strlen($body)/2); 
			$header_size    =    ($width*$height); 

			//    Use end-line padding? Only when needed 
			$usePadding        =    ($body_size>($header_size*3)+4); 
			
			//    Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption 
			//    Calculate the next DWORD-position in the body 
			for ($i=0;$i<$body_size;$i+=3) 
			{ 
				//    Calculate line-ending and padding 
				if ($x>=$width) 
				{ 
					//    If padding needed, ignore image-padding 
					//    Shift i to the ending of the current 32-bit-block 
					if ($usePadding) 
						$i    +=    $width%4; 
					
					//    Reset horizontal position 
					$x    =    0; 
					
					//    Raise the height-position (bottom-up) 
					$y++; 
					
					//    Reached the image-height? Break the for-loop 
					if ($y>$height) 
						break; 
				} 
				
				//    Calculation of the RGB-pixel (defined as BGR in image-data) 
				//    Define $i_pos as absolute position in the body 
				$i_pos    =    $i*2; 
				$r        =    hexdec($body[$i_pos+4].$body[$i_pos+5]); 
				$g        =    hexdec($body[$i_pos+2].$body[$i_pos+3]); 
				$b        =    hexdec($body[$i_pos].$body[$i_pos+1]); 
				
				//    Calculate and draw the pixel 
				$color    =    imagecolorallocate($image,$r,$g,$b); 
				imagesetpixel($image,$x,$height-$y,$color); 
				
				//    Raise the horizontal position 
				$x++; 
			} 
			
			//    Unset the body / free the memory 
			unset($body); 
			
			//    Return image-object 
			return $image;
		}
		
		/**
		 * Returns the image resource
		 *
		 * @return resource
		 */
		final public function GetImageResource()
		{
			if (is_resource($this->_imageResource) && get_resource_type($this->_imageResource) === 'gd') {
				return $this->_imageResource;
			} else {
				throw new \Exception('Image resource is not set. Use $this->LoadImage to load an image into the resource');
			}
		}
	}
}
?>