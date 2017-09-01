<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 15:23
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\storage\Document;

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
            $cmsComponent->subTemplate = 'documents';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENTS, $cmsComponent->storage->getDocuments()->getDocumentsWithState());
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            if (isset($_GET['not-found'])) {
                $cmsComponent->setParameter('infoMessage', 'Document could not be found. It might have been removed.');
                $cmsComponent->setParameter('infoMessageClass', 'error');
            } elseif (isset($_GET['published'])) {
                $cmsComponent->setParameter('infoMessage', '<i class="fa fa-check-circle-o"></i> Document ' . $_GET['published'] . ' published');
            } elseif (isset($_GET['unpublished'])) {
                $cmsComponent->setParameter('infoMessage', '<i class="fa fa-times-circle-o"></i> Document ' . $_GET['unpublished'] . ' unpublished');
            } elseif (isset($_GET['folder-delete'])) {
                $cmsComponent->setParameter('infoMessage', '<i class="fa fa-trash"></i> Folder deleted');
            } elseif (isset($_GET['document-delete'])) {
                $cmsComponent->setParameter('infoMessage', '<i class="fa fa-trash"></i> Document deleted');
            }
        }
        $this->documentRouting($request, $relativeCmsUri, $cmsComponent);
        $this->folderRouting($request, $relativeCmsUri, $cmsComponent);
        $this->valuelistsRouting($request, $relativeCmsUri, $cmsComponent);
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
            $this->documentNewRoute($request, $cmsComponent);
        } elseif (isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            switch ($relativeCmsUri) {
                case '/documents/edit-document': $this->editDocumentRoute($request, $cmsComponent); break;
                case '/documents/get-brick': $this->getBrickRoute($request, $cmsComponent); break;
                case '/documents/delete-document': $this->deleteDocumentRoute($request, $cmsComponent); break;
                case '/documents/publish-document': $this->publishDocumentRoute($request, $cmsComponent); break;
                case '/documents/unpublish-document': $this->unpublishDocumentRoute($request, $cmsComponent); break;
            }
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
            $this->newFolderRoute($request, $cmsComponent);
        } else if ($relativeCmsUri == '/documents/edit-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->editFolderRoute($request, $cmsComponent);
        } else if ($relativeCmsUri == '/documents/delete-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->deleteFolderRoute($request, $cmsComponent);
        }
    }

    private function valuelistsRouting($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/documents/valuelists') {
            $this->valuelistsRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/documents/valuelists/new') {
            $this->newValuelistRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/documents/valuelists/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->editValuelistRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/documents/valuelists/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->deleteValuelistRoute($request, $cmsComponent);
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     *
     * @throws \Exception
     */
    private function documentNewRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/document-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
        if (isset($request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE])) {
            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE], $request::$get[CmsComponent::GET_PARAMETER_PATH])) {
                $path = substr($cmsComponent->storage->getDocuments()->addDocument($request::$post), 1);
                $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
                $cmsComponent->storage->getActivityLog()->add('created document <a href="' . $docLink . '">' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . '</a> in path ' . $request::$get[CmsComponent::GET_PARAMETER_PATH], 'plus');
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE], true));
            $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks()->getBricks());
        } else {
            $documentTypes = $cmsComponent->storage->getDocumentTypes()->getDocumentTypes();
            if (count($documentTypes) < 1) {
                throw new \Exception('No Document Types defined yet. Please do so first.');
            }
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPES, $documentTypes);
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function editDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/document-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $path = substr($cmsComponent->storage->getDocuments()->saveDocument($request::$post), 1);
            $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
            $cmsComponent->storage->getActivityLog()->add('edited document <a href="' . $docLink . '">' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . '</a> in path /' . $request::$get[CmsComponent::GET_PARAMETER_SLUG], 'pencil');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
            exit;
        }
        $document = $cmsComponent->storage->getDocuments()->getDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG], 'unpublished');
        $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT, $document);

        $request::$get[CmsComponent::GET_PARAMETER_PATH] = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
        if ($document instanceof Document) {
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($document->documentTypeSlug, true));
        } else {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?not-found');
            exit;
        }

        $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks()->getBricks());
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function getBrickRoute($request, $cmsComponent)
    {
        $cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
        $cmsComponent->subTemplate = 'documents/brick';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_BRICK, $cmsComponent->storage->getBricks()->getBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
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
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->deleteDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        $cmsComponent->storage->getActivityLog()->add('deleted document /' . $request::$get[CmsComponent::GET_PARAMETER_SLUG], 'trash');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?document-delete');
        exit;
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function newFolderRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/folder-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::GET_PARAMETER_PATH])) {
            $cmsComponent->storage->addDocumentFolder($request::$post);
            $cmsComponent->storage->getActivityLog()->add('created folder ' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . ' in path ' . $request::$get[CmsComponent::GET_PARAMETER_PATH], 'plus');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
            exit;
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function editFolderRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/folder-form';
        $folder = $cmsComponent->storage->getDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);

        $path = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
        $path = explode('/', $path);
        array_pop($path);
        $path = implode('/', $path);

        $request::$get[CmsComponent::GET_PARAMETER_PATH] = '/' . $path;

        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post['content'])) {
            $cmsComponent->storage->saveDocumentFolder($request::$post);
            $cmsComponent->storage->getActivityLog()->add('edited folder ' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . ' in path ' . $request::$get[CmsComponent::GET_PARAMETER_PATH], 'pencil');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
            exit;
        }

        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_FOLDER, $folder);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteFolderRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->deleteDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        $cmsComponent->storage->getActivityLog()->add('deleted folder /' . $request::$get[CmsComponent::GET_PARAMETER_SLUG], 'trash');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?folder-delete');
        exit;
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function publishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->publishDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        $path = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add('published document <a href="' . $docLink . '">' . $request::$get[CmsComponent::GET_PARAMETER_SLUG] . '</a>', 'check-circle-o');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?published=' . htmlentities($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
        exit;
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function unpublishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->unpublishDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        $path = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add('unpublished document <a href="' . $docLink . '">' . $request::$get[CmsComponent::GET_PARAMETER_SLUG] . '</a>', 'times-circle-o');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?unpublished=' . htmlentities($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
        exit;
    }

    private function valuelistsRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/valuelists';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_VALUELISTS, $cmsComponent->storage->getValuelists()->getValuelists());
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function newValuelistRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/valuelist-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
            $slug = $cmsComponent->storage->getValuelists()->addValuelist($request::$post);
            $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/valuelists/edit?slug=' . $slug;
            $cmsComponent->storage->getActivityLog()->add('created valuelist <a href="' . $docLink . '">' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . '</a>', 'plus');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/valuelists');
            exit;
        }
    }

    private function editValuelistRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/valuelist-form';
        $folder = $cmsComponent->storage->getValuelists()->getValuelistBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);

        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->storage->getValuelists()->saveValuelist($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
            $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/valuelists/edit?slug=' . $request::$get[CmsComponent::GET_PARAMETER_SLUG];
            $cmsComponent->storage->getActivityLog()->add('edited valuelist <a href="' . $docLink . '">' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . '</a>', 'pencil');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/valuelists');
            exit;
        }

        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_VALUELIST, $folder);
    }

    private function deleteValuelistRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getValuelists()->deleteValuelistBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        $cmsComponent->storage->getActivityLog()->add('deleted valuelist ' . $request::$get[CmsComponent::GET_PARAMETER_SLUG], 'trash');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/valuelists');
        exit;
    }
}