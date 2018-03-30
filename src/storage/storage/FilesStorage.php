<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\storage\factories\FileFactory;

class FilesStorage extends AbstractStorage
{
    protected $filesDir;

    /**
     * @param \CloudControl\Cms\storage\Repository $repository
     * @param string $filesDir
     */
    public function __construct($repository, $filesDir)
    {
        parent::__construct($repository);
        $this->filesDir = $filesDir;
    }


    /**
     * @return array
     */
    public function getFiles()
    {
        $files = $this->repository->files;
        usort($files, array($this, 'compareFiles'));

        return $files;
    }

    /**
     * @param $postValues
     *
     * @return \CloudControl\Cms\storage\entities\File
     * @throws \Exception
     */
    public function addFile($postValues)
    {
        $destinationPath = $this->getDestinationPath();

        $filename = $this->validateFilename($postValues['name'], $destinationPath);
        $destination = $destinationPath . '/' . $filename;

        if ('0' != $postValues['error']) {
            throw new \RuntimeException('Error uploading file. Error code: ' . $postValues['error']);
        }

        if (move_uploaded_file($postValues['tmp_name'], $destination)) {
            $file = FileFactory::createFileFromPostValues($postValues, $filename);

            $files = $this->repository->files;
            $files[] = $file;
            $this->repository->files = $files;
            $this->save();
        } else {
            throw new \RuntimeException('Error moving uploaded file');
        }
        return $file;
    }

    /**
     * @param $filename
     *
     * @return \stdClass|null
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
        $destinationPath = $this->getDestinationPath();
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
     * @return string
     */
    public function getFilesDir()
    {
        return $this->filesDir;
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

    protected function getDestinationPath()
    {
        $destinationPath = realpath($this->filesDir . DIRECTORY_SEPARATOR);
        return $destinationPath;
    }
}