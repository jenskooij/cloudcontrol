<?php
/**
 * Created by: Jens
 * Date: 28-3-2018
 */

namespace CloudControl\Cms\images;


class BitmapFactory
{

    /**
     * Checks if native function imagecreatefrombmp exists (PHP >= 7.2.0)
     * otherwise uses built in implementation
     *
     * @param $pathToBitmapFile
     * @return resource
     */
    public static function createImageFromBmp($pathToBitmapFile)
    {
        if (function_exists('imagecreatefrombmp ')) {
            return /** @scrutinizer ignore-call */ imagecreatefrombmp($pathToBitmapFile);
        }
        return self::imageCreateFromBmp($pathToBitmapFile);
    }

    /**
     * @param $pathToBitmapFile
     * @throws \Exception
     * @return bool|string
     */
    private static function getBitmapFileData($pathToBitmapFile)
    {
        $fileHandle = fopen($pathToBitmapFile, 'rb');
        if ($fileHandle === false) {
            throw new \RuntimeException('Could not open bitmapfile ' . $pathToBitmapFile);
        }
        $bitmapFileData = fread($fileHandle, 10);
        while (!feof($fileHandle) && ($bitmapFileData <> "")) {
            $bitmapFileData .= fread($fileHandle, 1024);
        }

        return $bitmapFileData;
    }

    /**
     * @param string $header
     *
     * @return array
     */
    private static function calculateWidthAndHeight($header)
    {
        $width = null;
        $height = null;

        //    Structure: http://www.fastgraph.com/help/bmp_header_format.html
        if (0 === strpos($header, '424d')) {
            //    Cut it in parts of 2 bytes
            $header_parts = str_split($header, 2);
            //    Get the width        4 bytes
            $width = hexdec($header_parts[19] . $header_parts[18]);
            //    Get the height        4 bytes
            $height = hexdec($header_parts[23] . $header_parts[22]);
            //    Unset the header params
            unset($header_parts);

            return array($width, $height);
        }

        return array($width, $height);
    }

    /**
     * Loop through the data in the body of the bitmap
     * file and calculate each individual pixel based on the
     * bytes
     *
     * Using a for-loop with index-calculation instead of str_split to avoid large memory consumption
     * Calculate the next DWORD-position in the body
     *
     * @param BitmapBodyModel $bitmapBodyModel
     */
    private static function loopThroughBodyAndCalculatePixels(BitmapBodyModel $bitmapBodyModel)
    {
        for ($i = 0; $i < $bitmapBodyModel->bodySize; $i += 3) {
            //    Calculate line-ending and padding
            if ($bitmapBodyModel->x >= $bitmapBodyModel->width) {
                //    If padding needed, ignore image-padding. Shift i to the ending of the current 32-bit-block
                if ($bitmapBodyModel->usePadding) {
                    $i += $bitmapBodyModel->width % 4;
                }
                //    Reset horizontal position
                $bitmapBodyModel->x = 0;
                //    Raise the height-position (bottom-up)
                $bitmapBodyModel->y++;
                //    Reached the image-height? Break the for-loop
                if ($bitmapBodyModel->y > $bitmapBodyModel->height) {
                    break;
                }
            }
            //    Calculation of the RGB-pixel (defined as BGR in image-data). Define $iPos as absolute position in the body
            $iPos = $i * 2;
            $r = hexdec($bitmapBodyModel->body[$iPos + 4] . $bitmapBodyModel->body[$iPos + 5]);
            $g = hexdec($bitmapBodyModel->body[$iPos + 2] . $bitmapBodyModel->body[$iPos + 3]);
            $b = hexdec($bitmapBodyModel->body[$iPos] . $bitmapBodyModel->body[$iPos + 1]);
            //    Calculate and draw the pixel
            $color = imagecolorallocate($bitmapBodyModel->image, (int)$r, (int)$g, (int)$b);
            imagesetpixel($bitmapBodyModel->image, $bitmapBodyModel->x, $bitmapBodyModel->height - $bitmapBodyModel->y, $color);
            //    Raise the horizontal position
            $bitmapBodyModel->x++;
        }
    }

    /**
     * Create Image resource from Bitmap
     *
     * @see       http://www.php.net/manual/en/function.imagecreatefromwbmp.php#86214
     * @author    alexander at alexauto dot nl
     *
     * @param    string $pathToBitmapFile
     *
     * @return  resource
     * @throws \Exception
     */
    public static function imageCreateFromBmp($pathToBitmapFile)
    {
        $bitmapFileData = self::getBitmapFileData($pathToBitmapFile);

        $temp = unpack('H*', $bitmapFileData);
        $hex = $temp[1];
        $header = substr($hex, 0, 108);
        list($width, $height) = self::calculateWidthAndHeight($header);

        //    Define starting X and Y
        $x = 0;
        $y = 1;

        $image = imagecreatetruecolor($width, $height);

        //    Grab the body from the image
        $body = substr($hex, 108);

        //    Calculate if padding at the end-line is needed. Divided by two to keep overview. 1 byte = 2 HEX-chars
        $bodySize = (strlen($body) / 2);
        $headerSize = ($width * $height);

        //    Use end-line padding? Only when needed
        $usePadding = ($bodySize > ($headerSize * 3) + 4);

        $bitmapBody = new BitmapBodyModel();
        $bitmapBody->bodySize = $bodySize;
        $bitmapBody->x = $x;
        $bitmapBody->width = $width;
        $bitmapBody->usePadding = $usePadding;
        $bitmapBody->y = $y;
        $bitmapBody->height = $height;
        $bitmapBody->body = $body;
        $bitmapBody->image = $image;

        self::loopThroughBodyAndCalculatePixels($bitmapBody);

        unset($body);

        return $bitmapBody->image;
    }
}