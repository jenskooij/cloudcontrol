<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 15:23
 */

namespace library\components\cms;


use library\components\CmsComponent;

class DocumentRouting implements CmsRouting
{
    /**
     * DocumentRouting constructor.
     * @param $request
     * @param $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/documents') {
            $cmsComponent->subTemplate = 'cms/documents';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENTS, $cmsComponent->storage->getDocuments());
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        }
        $this->documentRouting($request, $relativeCmsUri, $cmsComponent);
        $this->folderRouting($request, $relativeCmsUri, $cmsComponent);
    }


    /**
     * @param $request
     * @param $relativeCmsUri
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function documentRouting($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/documents/new-document' && isset($request::$get[CmsComponent::GET_PARAMETER_PATH])) {
            $cmsComponent->subTemplate = 'cms/documents/document-form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getSmallestImageSet()->slug);
            if (isset($request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE])) {
                if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE], $request::$get[CmsComponent::GET_PARAMETER_PATH])) {
                    $cmsComponent->storage->addDocument($request::$post);
                    header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                    exit;
                }
                $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $cmsComponent->storage->getDocumentTypeBySlug($request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE], true));
                $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks());
            } else {
                $documentTypes = $cmsComponent->storage->getDocumentTypes();
                if (count($documentTypes) < 1) {
                    throw new \Exception('No Document Types defined yet. <a href="' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types/new">Please do so first.</a>');
                }
                $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPES, $documentTypes);
            }
        } elseif ($relativeCmsUri == '/documents/edit-document' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->subTemplate = 'cms/documents/document-form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getSmallestImageSet()->slug);
            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
                $cmsComponent->storage->saveDocument($request::$post);
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT, $cmsComponent->storage->getDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
            $request::$get[CmsComponent::GET_PARAMETER_PATH] = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $cmsComponent->storage->getDocumentTypeBySlug($cmsComponent->getParameter(CmsComponent::PARAMETER_DOCUMENT)->documentTypeSlug, true));
            $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks());
        } elseif ($relativeCmsUri == '/documents/get-brick' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getSmallestImageSet()->slug);
            $cmsComponent->subTemplate = 'cms/documents/brick';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICK, $cmsComponent->storage->getBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
            $cmsComponent->setParameter(CmsComponent::PARAMETER_STATIC, $request::$get[CmsComponent::PARAMETER_STATIC] === 'true');
            if (isset($request::$get[CmsComponent::PARAMETER_MY_BRICK_SLUG])) {
                $cmsComponent->setParameter(CmsComponent::PARAMETER_MY_BRICK_SLUG, $request::$get[CmsComponent::PARAMETER_MY_BRICK_SLUG]);
            }
            $result = new \stdClass();
            $result->body = $cmsComponent->renderTemplate('cms/documents/brick');
            $result->rteList = isset($GLOBALS['rteList']) ? $GLOBALS['rteList'] : array();
            ob_clean();
            header(CmsComponent::CONTENT_TYPE_APPLICATION_JSON);
            die(json_encode($result));
        } else if ($relativeCmsUri == '/documents/delete-document' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->storage->deleteDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
            exit;
        }
    }

    /**
     * @param $request
     * @param $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    private function folderRouting($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/documents/new-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_PATH])) {
            $cmsComponent->subTemplate = 'cms/documents/folder-form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::GET_PARAMETER_PATH])) {
                $cmsComponent->storage->addDocumentFolder($request::$post);
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }
        } else if ($relativeCmsUri == '/documents/edit-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {

            $cmsComponent->subTemplate = 'cms/documents/folder-form';
            $folder = $cmsComponent->storage->getDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);

            $path = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
            $path = explode('/', $path);
            array_pop($path);
            $path = implode('/', $path);

            $request::$get[CmsComponent::GET_PARAMETER_PATH] = '/' . $path;

            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post['content'])) {
                $cmsComponent->storage->saveDocumentFolder($request::$post);
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }

            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_FOLDER, $folder);
        } else if ($relativeCmsUri == '/documents/delete-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->storage->deleteDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
            exit;
        }
    }
}