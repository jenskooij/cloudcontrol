<?php
/**
 * User: Jens
 * Date: 4-9-2017
 * Time: 20:44
 */

namespace CloudControl\Cms\components\cms;


interface CmsConstants
{

    const CONTENT_TYPE_APPLICATION_JSON = 'Content-type:application/json';

    const INVALID_CREDENTIALS_MESSAGE = 'Invalid username / password combination';
    const LOGIN_TEMPLATE_PATH = 'login';
    const MAIN_NAV_CLASS = 'default';

    const FILES_PARAMETER_FILE = 'file';

    const GET_PARAMETER_PATH = 'path';
    const GET_PARAMETER_SLUG = 'slug';

    const PARAMETER_AUTO_UPDATE_SEARCH_INDEX = 'autoUpdateSearchIndex';
    const PARAMETER_APPLICATION_COMPONENT = 'applicationComponent';
    const PARAMETER_APPLICATION_COMPONENTS = 'applicationComponents';
    const PARAMETER_BLACKLIST_IPS = 'blacklistIps';
    const PARAMETER_BODY = 'body';
    const PARAMETER_BRICK = 'brick';
    const PARAMETER_BRICKS = 'bricks';
    const PARAMETER_CMS_PREFIX = 'cmsPrefix';
    const PARAMETER_CONFIGURATION = 'configuration';
    const PARAMETER_DOCUMENT = 'document';
    const PARAMETER_DOCUMENTS = 'documents';
    const PARAMETER_DOCUMENT_TYPE = 'documentType';
    const PARAMETER_DOCUMENT_TYPES = 'documentTypes';
    const PARAMETER_ERROR_MESSAGE = 'errorMsg';
    const PARAMETER_FILES = 'files';
    const PARAMETER_FOLDER = 'folder';
    const PARAMETER_IMAGE = 'image';
    const PARAMETER_IMAGES = 'images';
    const PARAMETER_IMAGE_SET = 'imageSet';
    const PARAMETER_MAIN_NAV_CLASS = 'mainNavClass';
    const PARAMETER_MY_BRICK_SLUG = 'myBrickSlug';
    const PARAMETER_REDIRECT = 'redirect';
    const PARAMETER_REDIRECTS = 'redirects';
    const PARAMETER_RETURN_URL = 'returnUrl';
    const PARAMETER_SAVE_AND_PUBLISH = 'btn_save_and_publish';
    const PARAMETER_SEARCH = 'search';
    const PARAMETER_SEARCH_LOG = "searchLog";
    const PARAMETER_SEARCH_NEEDS_UPDATE = "searchNeedsUpdate";
    const PARAMETER_SITEMAP = 'sitemap';
    const PARAMETER_SITEMAP_ITEM = 'sitemapItem';
    const PARAMETER_SMALLEST_IMAGE = 'smallestImage';
    const PARAMETER_STATIC = 'static';
    const PARAMETER_USER = 'user';
    const PARAMETER_USERS = 'users';
    const PARAMETER_USER_RIGHTS = 'userRights';
    const PARAMETER_VALUELIST = "valuelist";
    const PARAMETER_VALUELISTS = "valuelists";
    const PARAMETER_WHITELIST_IPS = 'whitelistIps';

    const POST_PARAMETER_COMPONENT = 'component';
    const POST_PARAMETER_FROM_URL = "fromUrl";
    const POST_PARAMETER_PASSWORD = 'password';
    const POST_PARAMETER_SAVE = 'save';
    const POST_PARAMETER_TEMPLATE = 'template';
    const POST_PARAMETER_TITLE = 'title';
    const POST_PARAMETER_TO_URL = "toUrl";
    const POST_PARAMETER_USERNAME = 'username';

    const SESSION_PARAMETER_CLOUD_CONTROL = 'cloudcontrol';
}