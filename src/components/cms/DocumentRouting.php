<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 15:23
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\cms\document\FolderRouting;
use CloudControl\Cms\components\cms\document\InfoMessagesHandler;
use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\search\Search;
use CloudControl\Cms\storage\Cache;
use CloudControl\Cms\storage\entities\Document;

class DocumentRouting extends CmsRouting
{
    protected static $routes = array(
        '/documents' => 'overviewRouting',
        '/documents/new-document' => 'documentNewRoute',
        '/documents/edit-document' => 'editDocumentRoute',
        '/documents/get-brick' => 'getBrickRoute',
        '/documents/delete-document' => 'deleteDocumentRoute',
        '/documents/publish-document' => 'publishDocumentRoute',
        '/documents/unpublish-document' => 'unpublishDocumentRoute',
    );
    const GET_PARAMETER_NOT_FOUND = 'not-found';
    const GET_PARAMETER_PUBLISHED = 'published';
    const GET_PARAMETER_UNPUBLISHED = 'unpublished';
    const GET_PARAMETER_FOLDER_DELETE = 'folder-delete';
    const GET_PARAMETER_DOCUMENT_DELETE = 'document-delete';
    const GET_PARAMETER_NO_DOCUMENT_TYPES = 'no-document-types';

    private static $infoMessageHandlers = array(
        self::GET_PARAMETER_NOT_FOUND => 'notFound',
        self::GET_PARAMETER_PUBLISHED => 'published',
        self::GET_PARAMETER_UNPUBLISHED => 'unpublished',
        self::GET_PARAMETER_FOLDER_DELETE => 'folderDelete',
        self::GET_PARAMETER_DOCUMENT_DELETE => 'documentDelete',
        self::GET_PARAMETER_NO_DOCUMENT_TYPES => 'noDocumentTypes',
    );

    /**
     * DocumentRouting constructor.
     * @param $request
     * @param $relativeCmsUri
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        $this->doRouting($request, $relativeCmsUri, $cmsComponent);
        new FolderRouting($request, $relativeCmsUri, $cmsComponent);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     *
     * @throws \Exception
     */
    protected function documentNewRoute($request, $cmsComponent)
    {
        if (isset($request::$get[CmsConstants::GET_PARAMETER_PATH])) {
            $this->setDocumentFormParameters($cmsComponent);
            if (isset($request::$get[CmsConstants::PARAMETER_DOCUMENT_TYPE])) {
                $this->newDocumentRoute($request, $cmsComponent);
            } else {
                $this->selectDocumentTypesRoute($request, $cmsComponent);
            }
        }

    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function editDocumentRoute($request, $cmsComponent)
    {
        $document = $cmsComponent->storage->getDocuments()->getDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG],
            self::GET_PARAMETER_UNPUBLISHED);
        if (!$document instanceof Document) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?not-found');
            exit;
        }

        $this->setDocumentFormParameters($cmsComponent);

        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
            $this->saveDocument($request, $cmsComponent);
        }

        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT, $document);

        $request::$get[CmsConstants::GET_PARAMETER_PATH] = $request::$get[CmsConstants::GET_PARAMETER_SLUG];
        $this->setDocumentTypeParameter($cmsComponent, $document);


    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function getBrickRoute($request, $cmsComponent)
    {
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SMALLEST_IMAGE,
            $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
        $cmsComponent->subTemplate = 'documents/brick';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_BRICK,
            $cmsComponent->storage->getBricks()->getBrickBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]));
        $cmsComponent->setParameter(CmsConstants::PARAMETER_STATIC,
            $request::$get[CmsConstants::PARAMETER_STATIC] === 'true');
        if (isset($request::$get[CmsConstants::PARAMETER_MY_BRICK_SLUG])) {
            $cmsComponent->setParameter(CmsConstants::PARAMETER_MY_BRICK_SLUG,
                $request::$get[CmsConstants::PARAMETER_MY_BRICK_SLUG]);
        }
        $result = new \stdClass();
        $result->body = $cmsComponent->renderTemplate('documents/brick');
        $result->rteList = isset($GLOBALS['rteList']) ? $GLOBALS['rteList'] : array();
        ob_clean();
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
        ResponseHeaders::sendAllHeaders();
        die(json_encode($result));
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function deleteDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->deleteDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        $cmsComponent->storage->getActivityLog()->add('deleted document /' . $request::$get[CmsConstants::GET_PARAMETER_SLUG],
            'trash');
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?document-delete');
        exit;
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function publishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->publishDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        $this->clearCacheAndLogActivity($request, $cmsComponent);
        $this->doAfterPublishRedirect($request, $cmsComponent);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function unpublishDocumentRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getDocuments()->unpublishDocumentBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        $this->clearCacheAndLogActivity($request, $cmsComponent, 'times-circle-o', self::GET_PARAMETER_UNPUBLISHED);
        $this->doAfterPublishRedirect($request, $cmsComponent, self::GET_PARAMETER_UNPUBLISHED);
    }

    /**
     * @param CmsComponent $cmsComponent
     * @param Request $request
     * @throws \Exception
     */
    protected function overviewRouting($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents';

        $path = isset($request::$get['path']) ? $request::$get['path'] : '/';

        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENTS,
            $cmsComponent->storage->getDocuments()->getDocumentsWithState($path));
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_DOCUMENTS);
        $cmsComponent->setParameter('path', $path);

        $documentCount = $cmsComponent->storage->getDocuments()->getTotalDocumentCount();
        $indexer = new Search($cmsComponent->storage);
        $indexedDocuments = $indexer->getIndexedDocuments();
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SEARCH_NEEDS_UPDATE, $documentCount !== $indexedDocuments);

        $this->handleInfoMessages($cmsComponent);
    }

    /**
     * @param CmsComponent $cmsComponent
     * @internal param Request $request
     */
    private function handleInfoMessages($cmsComponent)
    {
        $getParameters = array_keys($_GET);
        $infoMessageKeys = array_keys(self::$infoMessageHandlers);
        foreach ($getParameters as $parameter) {
            if (in_array($parameter, $infoMessageKeys, true)) {
                $method = self::$infoMessageHandlers[$parameter];
                InfoMessagesHandler::$method($cmsComponent);
            }
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @param string $param
     */
    private function doAfterPublishRedirect($request, $cmsComponent, $param = 'published')
    {
        if ($cmsComponent->autoUpdateSearchIndex) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/search/update-index?returnUrl=' . urlencode($request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?' . $param . '=' . urlencode($request::$get[CmsConstants::GET_PARAMETER_SLUG]) . '&path=' . $this->getReturnPath($request)));
        } else {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?' . $param . '=' . urlencode($request::$get[CmsConstants::GET_PARAMETER_SLUG]) . '&path=' . $this->getReturnPath($request));
        }
        exit;
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @param string $icon
     * @param string $activity
     */
    private function clearCacheAndLogActivity(
        $request,
        $cmsComponent,
        $icon = 'check-circle-o',
        $activity = 'published'
    )
    {
        Cache::getInstance()->clearCache();
        $path = $request::$get[CmsConstants::GET_PARAMETER_SLUG];
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add($activity . ' document <a href="' . $docLink . '">' . $request::$get[CmsConstants::GET_PARAMETER_SLUG] . '</a>',
            $icon);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function createNewDocument($request, $cmsComponent)
    {
        $path = substr($cmsComponent->storage->getDocuments()->addDocument($request::$post), 1);
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add('created document <a href="' . $docLink . '">' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . '</a> in path ' . $request::$get[CmsConstants::GET_PARAMETER_PATH],
            'plus');
        if (isset($request::$post[CmsConstants::PARAMETER_SAVE_AND_PUBLISH])) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/publish-document?slug=' . $path);
        } else {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents');
        }
        exit;
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function putDocumentTypeOnRequest($request, $cmsComponent)
    {
        $documentType = $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($request::$get[CmsConstants::PARAMETER_DOCUMENT_TYPE],
            true);
        if ($documentType === null) {
            $documentTypes = $cmsComponent->storage->getDocumentTypes()->getDocumentTypes();
            $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPES, $documentTypes);
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPE, $documentType);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     * @param $documentTypes
     */
    private function checkDocumentType($request, $cmsComponent, $documentTypes)
    {
        $docTypesCount = count($documentTypes);
        if ($docTypesCount < 1) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?no-document-types');
            exit;
        }

        if ($docTypesCount === 1) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/new-document?path=' . urlencode($_GET['path']) . '&documentType=' . $documentTypes[0]->slug);
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    private function newDocumentRoute($request, $cmsComponent)
    {
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$get[CmsConstants::PARAMETER_DOCUMENT_TYPE], $request::$get[CmsConstants::GET_PARAMETER_PATH])) {
            $this->createNewDocument($request, $cmsComponent);
        }
        $this->putDocumentTypeOnRequest($request, $cmsComponent);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function selectDocumentTypesRoute($request, $cmsComponent)
    {
        $documentTypes = $cmsComponent->storage->getDocumentTypes()->getDocumentTypes();
        $this->checkDocumentType($request, $cmsComponent, $documentTypes);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPES, $documentTypes);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function saveDocument($request, $cmsComponent)
    {
        $path = substr($cmsComponent->storage->getDocuments()->saveDocument($request::$post), 1);
        $docLink = $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/edit-document?slug=' . $path;
        $cmsComponent->storage->getActivityLog()->add('edited document <a href="' . $docLink . '">' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . '</a> in path /' . $request::$get[CmsConstants::GET_PARAMETER_SLUG],
            'pencil');
        if (isset($request::$post[CmsConstants::PARAMETER_SAVE_AND_PUBLISH])) {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents/publish-document?slug=' . $path);
        } else {
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?path=' . $this->getReturnPath($request));
        }
        exit;
    }

    /**
     * @param CmsComponent $cmsComponent
     * @param Document $document
     */
    protected function setDocumentTypeParameter($cmsComponent, $document)
    {
        $documentType = $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($document->documentTypeSlug,
            true);
        if ($documentType === null) {
            $documentTypes = $cmsComponent->storage->getDocumentTypes()->getDocumentTypes();
            $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPES, $documentTypes);
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_DOCUMENT_TYPE, $documentType);
    }

    /**
     * @param $cmsComponent
     */
    protected function setDocumentFormParameters($cmsComponent)
    {
        $cmsComponent->subTemplate = 'documents/document-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_DOCUMENTS);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SMALLEST_IMAGE,
            $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_BRICKS,
            $cmsComponent->storage->getBricks()->getBricks());
    }

    /**
     * @param $request
     * @return string
     */
    protected function getReturnPath($request)
    {
        $returnPathParts = explode('/', $request::$get['slug']);
        array_pop($returnPathParts);
        return '/' . implode('/', $returnPathParts);
    }
}