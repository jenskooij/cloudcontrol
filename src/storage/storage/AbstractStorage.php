<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\cc\StringUtil;
use CloudControl\Cms\storage\Repository;

abstract class AbstractStorage
{
    /**
     * @var Repository
     */
    protected $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Converts filename to lowercase, remove non-ascii chars
     * And adds "-copy" if the file already exists
     *
     * @param $filename
     * @param $path
     *
     * @return string
     */
    protected function validateFilename($filename, $path)
    {
        $fileParts = explode('.', $filename);
        if (count($fileParts) > 1) {
            $extension = end($fileParts);
            array_pop($fileParts);
            $fileNameWithoutExtension = implode('-', $fileParts);
            $fileNameWithoutExtension = StringUtil::slugify($fileNameWithoutExtension);
            $filename = $fileNameWithoutExtension . '.' . $extension;
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

            return $this->validateFilename($filename, $path);
        }

        return $filename;
    }

    /**
     * Save changes made to the repository
     *
     * @throws \Exception
     */
    protected function save()
    {
        $this->repository->save();
    }
}