<?php
/**
 * Created by: Jens
 * Date: 9-1-2018
 */

namespace CloudControl\Cms\util;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;

class Cms
{
    public static $assetsIncluded = false;

    /**
     * Returns a button with a link for editing a document
     * @param $path
     * @return string
     * @throws \Exception
     */
    public static function editDocument($path)
    {
        if (self::isLoggedIn()) {
            $return = self::getAssetsIfNotIncluded();
            return $return . '<a title="Edit Document" data-href="' . self::editDocumentLink($path) . '" class="ccEditDocumentButton"></a>';
        } else {
            return '';
        }
    }

    /**
     * Returns the cms link for editing a document
     * @param $path
     * @return string
     * @throws \Exception
     */
    public static function editDocumentLink($path)
    {
        if (self::isLoggedIn()) {
            $path = substr($path,0,1) === '/' ? substr($path, 1) : $path;
            return Request::$subfolders . 'cms/documents/edit-document?slug=' . urlencode($path);
        } else {
            return '';
        }
    }

    /**
     * Returns a button with a link for creating a new document
     * @param string $path
     * @param string $documentType
     * @return string
     * @throws \Exception
     */
    public static function newDocument($path = '/', $documentType = '')
    {
        if (self::isLoggedIn()) {
            $return = self::getAssetsIfNotIncluded();
            return $return . '<a title="New Document" data-href="' . self::newDocumentLink($path,
                    $documentType) . '" class="ccEditDocumentButton ccNewDocumentButton"></a>';
        } else {
            return '';
        }
    }

    /**
     * Returns the cms link for creating a new document
     * @param string $path
     * @param string $documentType
     * @return string
     * @throws \Exception
     */
    public static function newDocumentLink($path = '/', $documentType = '')
    {
        if (self::isLoggedIn()) {
            $path = substr($path,0,1) === '/' ? $path : '/' . $path;
            $linkPostFix = '';
            if ($documentType !== '') {
                $linkPostFix = '&amp;documentType=' . $documentType;
            }
            return Request::$subfolders . 'cms/documents/new-document?path=' . urlencode($path) . $linkPostFix;
        } else {
            return '';
        }
    }


    /**
     * See if a user is logged or wants to log in and
     * takes appropriate actions.
     *
     * @throws \Exception
     */
    private
    static function isLoggedIn()
    {
        return isset($_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]);
    }

    private
    static function getAssetsIfNotIncluded()
    {
        if (!self::$assetsIncluded) {
            self::$assetsIncluded = true;
            return '<style>
            .ccEditDocumentButton{
                opacity:0;
                -webkit-transition: opacity 0.5s;-moz-transition: opacity 0.5s;-ms-transition: opacity 0.5s;-o-transition: opacity 0.5s;transition: opacity 0.5s;
            }
            
            .ccEditDocumentButton.active {
                display:block;
                position:absolute;
                line-height:50px;
                width:50px;
                height:50px;                
                background: rgb(0, 106, 193) url("data:image/svg+xml;utf8,<svg width=\'25\' height=\'25\' viewBox=\'0 0 1792 1792\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M491 1536l91-91-235-235-91 91v107h128v128h107zm523-928q0-22-22-22-10 0-17 7l-542 542q-7 7-7 17 0 22 22 22 10 0 17-7l542-542q7-7 7-17zm-54-192l416 416-832 832h-416v-416zm683 96q0 53-37 90l-166 166-416-416 166-165q36-38 90-38 53 0 91 38l235 234q37 39 37 91z\' fill=\'#fff\'/></svg>") no-repeat center;
                border-radius:50%;
                color:#fff;
                font-family:Arial, sans-serif;
                text-align:center;
                cursor:pointer;
                box-shadow: 5px 5px 5px rgba(128, 128, 128, 0.5);
                z-index:255;
                opacity:0.7;
            }
            
            @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
              /* IE10+ CSS styles go here */
              .ccEditDocumentButton.active:before {
                content:\'edit\';
                font-family:Arial, sans-serif;
                font-size:12px;
                line-height:50px;
              }
            }
            
            .ccEditDocumentButton.active:hover {
                color:rgb(0, 106, 193);
                background: #fff url("data:image/svg+xml;utf8,<svg width=\'25\' height=\'25\' viewBox=\'0 0 1792 1792\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M491 1536l91-91-235-235-91 91v107h128v128h107zm523-928q0-22-22-22-10 0-17 7l-542 542q-7 7-7 17 0 22 22 22 10 0 17-7l542-542q7-7 7-17zm-54-192l416 416-832 832h-416v-416zm683 96q0 53-37 90l-166 166-416-416 166-165q36-38 90-38 53 0 91 38l235 234q37 39 37 91z\' fill=\'#006AC1\'/></svg>") no-repeat center;
                opacity:1;
            }
            
            .ccEditDocumentButton.active.ccNewDocumentButton {
              background-image:none;
            }
            
            .ccEditDocumentButton.active.ccNewDocumentButton:before {
                content:\'+\';
                font-family:Arial, sans-serif;
                font-size:20px;
                line-height:50px;
              }
            
            .ccDocumentEditorHidden {
                transform: translateX(100%);
            }
            
            @keyframes slideInFromRight {
              0% {
                transform: translateX(100%);
              }
              100% {
                transform: translateX(0);
              }
            }
            
            @keyframes slideOutToRight {
              0% {
                transform: translateX(0);
              }
              100% {
                transform: translateX(100%);
              }
            }
            </style>' .
                '<script src="' . Request::$subfolders . 'js/cms.js"></script>';
        }
        return '';
    }
}

