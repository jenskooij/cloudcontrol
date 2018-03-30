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

class FilesRouting extends CmsRouting
{
    protected static $routes = array(
        '/files' => 'overviewRoute',
        '/files/new' => 'newRoute',
        '/files/new-ajax' => 'newAjaxRoute',
        '/files/get' => 'downloadRoute',
        '/files/delete' => 'deleteRoute',
    );

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        $this->doRouting($request, $relativeCmsUri, $cmsComponent);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    protected function downloadRoute($request, $cmsComponent)
    {
        if (null === $request::$get[CmsConstants::FILES_PARAMETER_FILE]) {
            return;
        }
        $slug = $request::$get[CmsConstants::FILES_PARAMETER_FILE];
        $file = $cmsComponent->storage->getFiles()->getFileByName($slug);
        if ($file === null) {
            return;
        }
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
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    protected function overviewRoute($request, $cmsComponent)
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
    protected function newRoute($request, $cmsComponent)
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
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newAjaxRoute($request, $cmsComponent)
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
    protected function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getFiles()->deleteFileByName($request::$get[CmsConstants::FILES_PARAMETER_FILE]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/files');
        exit;
    }

}