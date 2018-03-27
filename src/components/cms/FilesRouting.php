<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 12:46
 */

namespace CloudControl\Cms\components\cms;

use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\CmsComponent;

class FilesRouting implements CmsRouting
{
    /**
     * FilesRouting constructor.
     *
     * @param Request $request
     * @param $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        if ($relativeCmsUri == '/files') {
            $this->overviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/files/new') {
            $this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/files/new-ajax') {
            $this->newAjaxRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/files/get' && isset($request::$get[CmsConstants::FILES_PARAMETER_FILE])) {
            $this->downloadRoute($request::$get[CmsConstants::FILES_PARAMETER_FILE], $cmsComponent);
        } elseif ($relativeCmsUri == '/files/delete' && isset($request::$get[CmsConstants::FILES_PARAMETER_FILE])) {
            $this->deleteRoute($request, $cmsComponent);
        }
    }

    /**
     * @param $slug
     * @param CmsComponent $cmsComponent
     */
    private function downloadRoute($slug, $cmsComponent)
    {
        $file = $cmsComponent->storage->getFiles()->getFileByName($slug);
        $path = realpath($cmsComponent->storage->getFiles()->getFilesDir());
        $quoted = sprintf('"%s"', addcslashes(basename($path . '/' . $file->file), '"\\'));
        $size = filesize($path . '/' . $file->file);

        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_DESCRIPTION, ResponseHeaders::HEADER_CONTENT_DESCRIPTION_CONTENT);
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, $file->type);
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_DISPOSITION, 'attachment; filename=' . $quoted);
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TRANSFER_ENCODING, ResponseHeaders::HEADER_CONTENT_TRANSFER_ENCODING_CONTENT_BINARY);
        ResponseHeaders::add(ResponseHeaders::HEADER_CONNECTION, ResponseHeaders::HEADER_CONNECTION_CONTENT_KEEP_ALIVE);
        ResponseHeaders::add(ResponseHeaders::HEADER_EXPIRES, 0);
        ResponseHeaders::add(ResponseHeaders::HEADER_CACHE_CONTROL, 'must-revalidate, post-check=0, pre-check=0');
        ResponseHeaders::add(ResponseHeaders::HEADER_PRAGMA, ResponseHeaders::HEADER_PRAGMA_CONTENT_PUBLIC);
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_LENGTH, $size);
        ResponseHeaders::sendAllHeaders();

        readfile($path . '/' . $file->file);
        exit;
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    private function overviewRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'files';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_FILES);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_FILES, $cmsComponent->storage->getFiles()->getFiles());
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'files/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_FILES);
        if (isset($_FILES[CmsConstants::FILES_PARAMETER_FILE])) {
            $cmsComponent->storage->getFiles()->addFile($_FILES[CmsConstants::FILES_PARAMETER_FILE]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/files');
            exit;
        }
    }

    /**
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function newAjaxRoute($cmsComponent)
    {
        if (isset($_FILES[CmsConstants::FILES_PARAMETER_FILE])) {
            $file = $cmsComponent->storage->getFiles()->addFile($_FILES[CmsConstants::FILES_PARAMETER_FILE]);
            ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
            ResponseHeaders::sendAllHeaders();
            die(json_encode($file));
        }
        die('error occured');
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getFiles()->deleteFileByName($request::$get[CmsConstants::FILES_PARAMETER_FILE]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/files');
        exit;
    }

}