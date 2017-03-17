<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;


class FilesStorage extends AbstractStorage
{
	/**
	 * @return array
	 */
	public function getFiles() {
		$files = $this->repository->files;
		usort($files, array($this, 'compareFiles'));

		return $files;
	}

	/**
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function addFile($postValues)
	{
		$destinationPath = realpath(__DIR__ . '/../../../www/files/');

		$filename = $this->validateFilename($postValues['name'], $destinationPath);
		$destination = $destinationPath . '/' . $filename;

		if ($postValues['error'] != '0') {
			throw new \Exception('Error uploading file. Error code: ' . $postValues['error']);
		}

		if (move_uploaded_file($postValues['tmp_name'], $destination)) {
			$file = new \stdClass();
			$file->file = $filename;
			$file->type = $postValues['type'];
			$file->size = $postValues['size'];

			$files = $this->repository->files;
			$files[] = $file;
			$this->repository->files = $files;
			$this->save();
		} else {
			throw new \Exception('Error moving uploaded file');
		}
	}

	/**
	 * @param $filename
	 *
	 * @return null
	 */
	public function getFileByName($filename)
	{
		$files = $this->getFiles();
		foreach ($files as $file) {
			if ($filename == $file->file) {
				return $file;
			}
		}

		return null;
	}

	/**
	 * @param $filename
	 *
	 * @throws \Exception
	 */
	public function deleteFileByName($filename)
	{
		$destinationPath = realpath(__DIR__ . '/../../../www/files/');
		$destination = $destinationPath . '/' . $filename;

		if (file_exists($destination)) {
			$files = $this->getFiles();
			foreach ($files as $key => $file) {
				if ($file->file == $filename) {
					unlink($destination);
					unset($files[$key]);
				}
			}

			$files = array_values($files);
			$this->repository->files = $files;
			$this->save();
		}
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	private function compareFiles($a, $b)
	{
		return strcmp($a->file, $b->file);
	}
}