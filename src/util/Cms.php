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
     * @param $path
     * @return string
     * @throws \Exception
     */
    public static function editDocument($path)
    {
        if (self::checkLogin()) {
            $return = self::getAssetsIfNotIncluded();
            return $return . '<a title="Edit Document" data-href="' . Request::$subfolders . 'cms/documents/edit-document?slug=' . substr($path, 1) . '&returnUrl=' . urlencode(Request::$requestUri) . '" class="ccEditDocumentButton"></a>';
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
    private static function checkLogin()
    {
        return isset($_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]);
    }

    private static function getAssetsIfNotIncluded()
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
            
            .ccEditDocumentButton.active:hover {
                color:rgb(0, 106, 193);
                background: #fff url("data:image/svg+xml;utf8,<svg width=\'25\' height=\'25\' viewBox=\'0 0 1792 1792\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M491 1536l91-91-235-235-91 91v107h128v128h107zm523-928q0-22-22-22-10 0-17 7l-542 542q-7 7-7 17 0 22 22 22 10 0 17-7l542-542q7-7 7-17zm-54-192l416 416-832 832h-416v-416zm683 96q0 53-37 90l-166 166-416-416 166-165q36-38 90-38 53 0 91 38l235 234q37 39 37 91z\' fill=\'#006AC1\'/></svg>") no-repeat center;
                opacity:1;
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

