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
			$cmsComponent->subTemplate = 'cms/configuration/document-types';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPES, $cmsComponent->storage->getDocumentTypes());
		} elseif ($relativeCmsUri == '/configuration/document-types/new') {
			$cmsComponent->subTemplate = 'cms/configuration/document-types-form';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$bricks = $cmsComponent->storage->getBricks();
			if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
				$cmsComponent->storage->addDocumentType($request::$post);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
				exit;
			}
			$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $bricks);
		} elseif ($relativeCmsUri == '/configuration/document-types/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$cmsComponent->subTemplate = 'cms/configuration/document-types-form';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$documentType = $cmsComponent->storage->getDocumentTypeBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG], false);
			$bricks = $cmsComponent->storage->getBricks();
			if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
				$cmsComponent->storage->saveDocumentType($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
				exit;
			}
			$cmsComponent->setParameter(CmsComponent::PARAMETER_DOCUMENT_TYPE, $documentType);
			$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $bricks);
		} elseif ($relativeCmsUri == '/configuration/document-types/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$cmsComponent->storage->deleteDocumentTypeBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/document-types');
			exit;
		}
	}
}