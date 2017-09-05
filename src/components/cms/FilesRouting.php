<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 12:46
 */

namespace CloudControl\Cms\components\cms;

use CloudControl\Cms\cc\Request;
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

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file->type);
        header('Content-Disposition: attachment; filename=' . $quoted);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);

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
     */
    private function newAjaxRoute($cmsComponent)
    {
        if (isset($_FILES[CmsConstants::FILES_PARAMETER_FILE])) {
            $file = $cmsComponent->storage->getFiles()->addFile($_FILES[CmsConstants::FILES_PARAMETER_FILE]);
            header('Content-type: application/json');
            die(json_encode($file));
        }
        die('error occured');
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getFiles()->deleteFileByName($request::$get[CmsConstants::FILES_PARAMETER_FILE]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/files');
        exit;
    }

}