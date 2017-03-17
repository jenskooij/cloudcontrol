<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;


use library\storage\factories\UserFactory;

class UsersStorage extends Storage
{
	/**
	 * Get all users
	 *
	 * @return mixed
	 */
	public function getUsers()
	{
		return $this->repository->users;
	}

	/**
	 * Get user by slug
	 *
	 * @param $slug
	 *
	 * @return array
	 */
	public function getUserBySlug($slug)
	{
		$return = array();

		$users = $this->repository->users;
		foreach ($users as $user) {
			if ($user->slug == $slug) {
				$return = $user;
				break;
			}
		}

		return $return;
	}

	/**
	 * Save user
	 *
	 * @param $slug
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function saveUser($slug, $postValues)
	{
		$userObj = UserFactory::createUserFromPostValues($postValues);
		if ($userObj->slug != $slug) {
			// If the username changed, check for duplicates
			$doesItExist = $this->getUserBySlug($userObj->slug);
			if (!empty($doesItExist)) {
				throw new \Exception('Trying to rename user to existing username');
			}
		}
		$users = $this->getUsers();
		foreach ($users as $key => $user) {
			if ($user->slug == $slug) {
				$users[$key] = $userObj;
			}
		}
		$this->repository->users = $users;
		$this->save();
	}

	/**
	 * Add user
	 *
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function addUser($postValues)
	{
		$userObj = UserFactory::createUserFromPostValues($postValues);

		$doesItExist = $this->getUserBySlug($userObj->slug);
		if (!empty($doesItExist)) {
			throw new \Exception('Trying to add username that already exists.');
		}
		$users = $this->repository->users;
		$users[] = $userObj;
		$this->repository->users = $users;
		$this->save();
	}

	/**
	 * Delete user by slug
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function deleteUserBySlug($slug)
	{
		$userToDelete = $this->getUserBySlug($slug);
		if (empty($userToDelete)) {
			throw new \Exception('Trying to delete a user that doesn\'t exist.');
		}
		$users = $this->getUsers();
		foreach ($users as $key => $user) {
			if ($user->slug == $userToDelete->slug) {
				unset($users[$key]);
				$this->repository->users = array_values($users);
			}
		}
		$this->save();
	}
}