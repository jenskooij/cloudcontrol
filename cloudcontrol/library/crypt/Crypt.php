<?php
namespace library\crypt
{
	/**
	 * Class Crypt
	 * @package library\crypt
	 */
	class Crypt
	{
		/**
		 * @var string
		 */
		private $lastSalt;
		
		/**
		 * Encrypts the given value using the blowfish algorithm
		 *
		 * @param  string  	$value 					The sting to be encrypted
		 * @param  int 	  	$encryptionIterations 	The amount of iterations used for encryption, 13 by default, resulting in aprox. 0.5 seconds of encrypting. Each raise, will result in about double the time
		 * @return string 	The hash
		 */
		public function encrypt($value, $encryptionIterations = 13)
		{
			$random = $this->getRandomBytes(16);
			$this->lastSalt = $this->getSalt($random, $encryptionIterations);
			$hash = crypt($value, $this->lastSalt);
			return $hash;
		}
		
		/**
		 * If on Linux, tries to use built in random byte feed
		 * else generates its own feed
		 *
		 * @param  int 		$count 		The amount of bytes to generates
		 * @return string 	The bytes
		 */
		private function getRandomBytes($count)
		{
			$output = '';
			$random_state = microtime();

			$openBasedir = ini_get('open_basedir');
			if (empty($openBasedir) &&
				is_readable('/dev/urandom') &&
				($fh = @fopen('/dev/urandom', 'rb'))) {
				$output = fread($fh, $count);
				fclose($fh);
			}

			if (strlen($output) < $count) {
				$output = '';
				for ($i = 0; $i < $count; $i += 16) {
					$random_state =
						md5(microtime() . $random_state);
					$output .=
						pack('H*', md5($random_state));
				}
				$output = substr($output, 0, $count);
			}
			
			return $output;
		}

		/**
		 * Generates the salt used for encryption
		 *
		 * @param string $input      Feed for iteration
		 * @param int    $iterations Amount of iterations
		 *
		 * @return string
		 */
		private function getSalt($input, $iterations)
		{
			$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

			$output = '$2a$';
			$output .= chr(ord('0') + $iterations / 10);
			$output .= chr(ord('0') + $iterations % 10);
			$output .= '$';

			$i = 0;
			do {
				$c1 = ord($input[$i++]);
				$output .= $itoa64[$c1 >> 2];
				$c1 = ($c1 & 0x03) << 4;
				if ($i >= 16) {
					$output .= $itoa64[$c1];
					break;
				}

				$c2 = ord($input[$i++]);
				$c1 |= $c2 >> 4;
				$output .= $itoa64[$c1];
				$c1 = ($c2 & 0x0f) << 2;

				$c2 = ord($input[$i++]);
				$c1 |= $c2 >> 6;
				$output .= $itoa64[$c1];
				$output .= $itoa64[$c2 & 0x3f];
			} while (1);

			return $output;
		}
		
		/**
		 * Returns the last used salt for encryption
		 *
		 * @return string | NULL
		 */
		public function getLastSalt()
		{
			return $this->lastSalt;
		}
		
		/**
		 * Compare the input with a known hash and salt
		 *
		 * @return bool
		 */
		public function compare($input, $hash, $salt)
		{
			$newHash = crypt($input, $salt);
			return $newHash == $hash;
		}
	}
}
?>