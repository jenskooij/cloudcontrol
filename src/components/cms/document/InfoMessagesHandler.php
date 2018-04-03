<?php
/**
 * Created by: Jens
 * Date: 3-4-2018
 */

namespace CloudControl\Cms\components\cms\document;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;
use CloudControl\Cms\components\cms\DocumentRouting;
use CloudControl\Cms\components\CmsComponent;

/**
 * Class InfoMessagesHandler
 *
 * Sets the info messages on the cms component
 *
 * @package CloudControl\Cms\components\cms\document
 */
class InfoMessagesHandler
{
    const PARAMETER_INFO_MESSAGE = 'infoMessage';
    const PARAMETER_INFO_MESSAGE_CLASS = 'infoMessageClass';

    /**
     * @param CmsComponent $cmsComponent
     */
    public static function notFound(CmsComponent $cmsComponent)
    {
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE, 'Document could not be found. It might have been removed.');
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE_CLASS, 'error');
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    public static function published(CmsComponent $cmsComponent)
    {
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE,
            '<i class="fa fa-check-circle-o"></i> Document ' . $_GET[DocumentRouting::GET_PARAMETER_PUBLISHED] . ' published');
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    public static function unpublished(CmsComponent $cmsComponent)
    {
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE,
            '<i class="fa fa-times-circle-o"></i> Document ' . $_GET[DocumentRouting::GET_PARAMETER_UNPUBLISHED] . ' unpublished');
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    public static function folderDelete(CmsComponent $cmsComponent)
    {
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE, '<i class="fa fa-trash"></i> Folder deleted');
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    public static function documentDelete(CmsComponent $cmsComponent)
    {
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE, '<i class="fa fa-trash"></i> Document deleted');
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    public static function noDocumentTypes(CmsComponent $cmsComponent)
    {
        $documentTypesLink = Request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/document-types/new';
        $cmsComponent->setParameter(self::PARAMETER_INFO_MESSAGE,
            '<i class="fa fa-exclamation-circle"></i> No document types defined yet. Please do so first, <a href="' . $documentTypesLink . '">here</a>.');
    }

}