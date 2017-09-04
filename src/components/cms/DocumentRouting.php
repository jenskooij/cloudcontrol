<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 15:23
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\document\FolderRouting;
use CloudControl\Cms\components\cms\document\ValuelistRouting;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\search\Search;
use CloudControl\Cms\storage\Document;

class DocumentRouting implements CmsRouting
{
    /**
     * DocumentRouting constructor.
     * @param $request
     * @param $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        if ($relativeCmsUri == '/documents') {
            $this->overviewRouting($cmsComponent, $request);
        }
        $this->documentRouting($request, $relativeCmsUri, $cmsComponent);
        new FolderRouting($request, $relativeCmsUri, $cmsComponent);
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
            $docTypesCount = count($documentTypes);
            if ($docTypesCount < 1) {
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?no-document-types');
                exit;
            } elseif ($docTypesCount == 1) {
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/new-document?path=' . urlencode($_GET['path']) . '&documentType=' . $documentTypes[0]->slug);
                exit;
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
    private function publishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->publishDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
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
        $cmsComponent->storage->getDocuments()->unpublishDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        $path = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add('unpublished document <a href="' . $docLink . '">' . $request::$get[CmsComponent::GET_PARAMETER_SLUG] . '</a>', 'times-circle-o');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?unpublished=' . htmlentities($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
        exit;
    }

    /**
     * @param CmsComponent $cmsComponent
     * @param Request $request
     */
    private function overviewRouting($cmsComponent, $request)
    {
        $cmsComponent->subTemplate = 'documents';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENTS, $cmsComponent->storage->getDocuments()->getDocumentsWithState());
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);

        $documentCount = $cmsComponent->storage->getDocuments()->getTotalDocumentCount();
        $indexer = new Search($cmsComponent->storage);
        $indexedDocuments = $indexer->getIndexedDocuments();
        $cmsComponent->setParameter(CmsComponent::PARAMETER_SEARCH_NEEDS_UPDATE, $documentCount !== $indexedDocuments);

        $this->handleInfoMessages($cmsComponent, $request);
    }

    /**
     * @param CmsComponent $cmsComponent
     * @param Request $request
     */
    private function handleInfoMessages($cmsComponent, $request)
    {
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
        } elseif (isset($_GET['no-document-types'])) {
            $documentTypesLink = $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types/new';
            $cmsComponent->setParameter('infoMessage', '<i class="fa fa-exclamation-circle"></i> No document types defined yet. Please do so first, <a href="' . $documentTypesLink . '">here</a>.');
        }
    }
}