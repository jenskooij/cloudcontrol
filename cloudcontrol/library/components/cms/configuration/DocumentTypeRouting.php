<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:22
 */

namespace library\components\cms\configuration;


use library\cc\Request;
use library\components\cms\CmsRouting;
use library\components\CmsComponent;

class DocumentTypeRouting implements CmsRouting
{

	/**
	 * DocumentTypeRouting constructor.
	 *
	 * @param Request      $request
	 * @param String       $relativeCmsUri
	 * @param CmsComponent $cmsComponent
	 */
	public function __construct($request, $relativeCmsUri, $cmsComponent)
	{
		if ($relativeCmsUri == '/configuration/document-types') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/document-types/new') {
			$this->newRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/document-types/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/document-types/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteRoute($request, $cmsComponent);
		}
	}

	/**
	 * @param CmsComponent $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/document-types';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPES, $cmsComponent->storage->getDocumentTypes()->getDocumentTypes());
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/document-types-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$bricks = $cmsComponent->storage->getBricks()->getBricks();
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->getDocumentTypes()->addDocumentType($request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
			exit;
		}
		$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $bricks);
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function editRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/document-types-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$documentType = $cmsComponent->storage->getDocumentTypes()->getDocumentTypeBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG], false);
		$bricks = $cmsComponent->storage->getBricks()->getBricks();
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->getDocumentTypes()->saveDocumentType($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
			exit;
		}
		$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $documentType);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $bricks);
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->getDocumentTypes()->deleteDocumentTypeBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
		exit;
	}
}