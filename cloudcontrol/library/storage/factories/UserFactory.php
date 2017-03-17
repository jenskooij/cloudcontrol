<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 17:01
 */

namespace library\storage\factories;


use library\cc\StringUtil;
use library\crypt\Crypt;

class UserFactory
{
	/**
	 * Create user from POST values
	 *
	 * @param $postValues
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public static function createUserFromPostValues($postValues)
	{
		if (isset($postValues['username'])) {
			$user = new \stdClass();
			$user->username = $postValues['username'];
			$user->slug = StringUtil::slugify($postValues['username']);
			$user->rights = array();
			if (isset($postValues['rights'])) {
				$user->rights = $postValues['rights'];
			}

			if (isset($postValues['password']) && empty($postValues['password']) === false) {
				$crypt = new Crypt();
				$user->password = $crypt->encrypt($postValues['password'], 16);
				$user->salt = $crypt->getLastSalt();
			} else {
				$user->password = $postValues['passHash'];
				$user->salt = $postValues['salt'];
			}

			return $user;
		} else {
			throw new \Exception('Trying to create user with invalid data.');
		}
	}
}