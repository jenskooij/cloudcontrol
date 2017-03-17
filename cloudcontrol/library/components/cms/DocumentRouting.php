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
            $cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENTS, $cmsComponent->storage->getDocuments()->getDocuments());
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
			$this->documentOverviewRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/documents/edit-document' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editDocumentRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/documents/get-brick' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->getBrickRoute($request, $cmsComponent);
        } else if ($relativeCmsUri == '/documents/delete-document' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteDocumentRoute($request, $cmsComponent);
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

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 *
	 * @throws \Exception
	 */
	private function documentOverviewRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/documents/document-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
		if (isset($request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE])) {
			if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE], $request::$get[CmsComponent::GET_PARAMETER_PATH])) {
				$cmsComponent->storage->getDocuments()->addDocument($request::$post);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
				exit;
			}
			$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($request::$get[CmsComponent::PARAMETER_DOCUMENT_TYPE], true));
			$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks()->getBricks());
		} else {
			$documentTypes = $cmsComponent->storage->getDocumentTypes()->getDocumentTypes();
			if (count($documentTypes) < 1) {
				throw new \Exception('No Document Types defined yet. <a href="' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types/new">Please do so first.</a>');
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
		$cmsComponent->subTemplate = 'cms/documents/document-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$cmsComponent->storage->getDocuments()->saveDocument($request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
			exit;
		}
		$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT, $cmsComponent->storage->getDocuments()->getDocumentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
		$request::$get[CmsComponent::GET_PARAMETER_PATH] = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
		$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($cmsComponent->getParameter(CmsComponent::PARAMETER_DOCUMENT)->documentTypeSlug, true));
		$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks()->getBricks());
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function getBrickRoute($request, $cmsComponent)
	{
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
		$cmsComponent->subTemplate = 'cms/documents/brick';
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
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
		exit;
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function newFolderRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/documents/folder-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::GET_PARAMETER_PATH])) {
			$cmsComponent->storage->addDocumentFolder($request::$post);
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
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function deleteFolderRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->deleteDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
		exit;
	}
}