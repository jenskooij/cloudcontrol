<?php
namespace library\components {

    use library\components\cms\DocumentRouting;
	use library\components\cms\FilesRouting;
	use library\components\cms\ImagesRouting;
    use library\components\cms\SitemapRouting;
    use library\crypt\Crypt;
    use library\storage\Storage;

    class CmsComponent extends BaseComponent
    {
        /*
         * var \library\storage\Storage
         */
        public $storage;

        const INVALID_CREDENTIALS_MESSAGE = 'Invalid username / password combination';

        const MAIN_NAV_CLASS = 'default';

        const PARAMETER_BLACKLIST_IPS = 'blacklistIps';
        const PARAMETER_BODY = 'body';
        const PARAMETER_BRICK = 'brick';
        const PARAMETER_BRICKS = 'bricks';
        const PARAMETER_CMS_PREFIX = 'cmsPrefix';
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
        const PARAMETER_SITEMAP = 'sitemap';
        const PARAMETER_SITEMAP_ITEM = 'sitemapItem';
        const PARAMETER_SMALLEST_IMAGE = 'smallestImage';
        const PARAMETER_STATIC = 'static';
        const PARAMETER_USER = 'user';
        const PARAMETER_USERS = 'users';
        const PARAMETER_USER_RIGHTS = 'userRights';
        const PARAMETER_WHITELIST_IPS = 'whitelistIps';

        const POST_PARAMETER_COMPONENT = 'component';
        const POST_PARAMETER_PASSWORD = 'password';
        const POST_PARAMETER_SAVE = 'save';
        const POST_PARAMETER_TEMPLATE = 'template';
        const POST_PARAMETER_TITLE = 'title';
        const POST_PARAMETER_USERNAME = 'username';

        const GET_PARAMETER_PATH = 'path';
        const GET_PARAMETER_SLUG = 'slug';

        const FILES_PARAMETER_FILE = 'file';

        const SESSION_PARAMETER_CLOUD_CONTROL = 'cloudcontrol';

        const LOGIN_TEMPLATE_PATH = 'cms/login';

        const CONTENT_TYPE_APPLICATION_JSON = 'Content-type:application/json';

        public $subTemplate = null;


        /**
         * @param \library\storage\Storage $storage
         *
         * @return void
         */
        public function run(Storage $storage)
        {
            $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = self::MAIN_NAV_CLASS;
            $this->storage = $storage;

            $remoteAddress = $_SERVER['REMOTE_ADDR'];
            $this->checkWhiteList($remoteAddress);
            $this->checkBlackList($remoteAddress);

            $this->checkLogin();

            $this->parameters[self::PARAMETER_USER_RIGHTS] = $_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL]->rights;

            $this->routing();
        }

        /**
         * See if a user is logged or wants to log in and
         * takes appropriate actions.
         *
         * @throws \Exception
         */
        protected function checkLogin()
        {
            $request = $this->request;

            if (!isset($_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL])) {
                if (isset($request::$post[self::POST_PARAMETER_USERNAME], $request::$post[self::POST_PARAMETER_PASSWORD])) {
                    $user = $this->storage->getUserByUsername($request::$post[self::POST_PARAMETER_USERNAME]);
                    $crypt = new Crypt();
                    if (empty($user)) {
                        $crypt->encrypt($request::$post[self::POST_PARAMETER_PASSWORD], 16); // Buy time, to avoid brute forcing
                        $this->parameters[self::PARAMETER_ERROR_MESSAGE] = self::INVALID_CREDENTIALS_MESSAGE;
                        $this->showLogin();
                    } else {
                        $salt = $user->salt;
                        $password = $user->password;

                        $passwordCorrect = $crypt->compare($request::$post[self::POST_PARAMETER_PASSWORD], $password, $salt);

                        if ($passwordCorrect) {
                            $_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL] = $user;
                        } else {
                            $this->parameters[self::PARAMETER_ERROR_MESSAGE] = self::INVALID_CREDENTIALS_MESSAGE;
                            $this->showLogin();
                        }
                    }
                } else {
                    $this->showLogin();
                }
            }
        }

        /**
         * Overrides normal behaviour and only renders the
         * login screen
         *
         * @throws \Exception
         */
        protected function showLogin()
        {
            $loginTemplatePath = self::LOGIN_TEMPLATE_PATH;
            $this->renderTemplate($loginTemplatePath);
            ob_end_flush();
            exit;
        }

        /**
         * As an exception, to keep the initial file structure simple
         * the cms implements it's own routing, apart from the regular sitemap functionality
         *
         * @throws \Exception
         */
        protected function routing()
        {
            $relativeCmsUri = $this->getRelativeCmsUri($this->request);

            $userRights = $_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL]->rights;

            if ($relativeCmsUri == '' || $relativeCmsUri == '/') {
                $this->subTemplate = 'cms/dashboard';
            }

            $this->logOffRouting($this->request, $relativeCmsUri);

            $this->apiRouting($relativeCmsUri);

            if (in_array(self::PARAMETER_DOCUMENTS, $userRights)) {
                new DocumentRouting($this->request, $relativeCmsUri, $this);
            }

            if (in_array(self::PARAMETER_SITEMAP, $userRights)) {
                new SitemapRouting($this->request, $relativeCmsUri, $this);
            }

            if (in_array(self::PARAMETER_IMAGES, $userRights)) {
                new ImagesRouting($this->request, $relativeCmsUri, $this);
            }

            if (in_array(self::PARAMETER_FILES, $userRights)) {
                new FilesRouting($this->request, $relativeCmsUri, $this);
            }

            if (in_array('configuration', $userRights)) {
                $this->configurationRouting($this->request, $relativeCmsUri);
            }

            if ($this->subTemplate !== null) {
                $this->parameters[self::PARAMETER_BODY] = $this->renderTemplate($this->subTemplate);
            }
        }

        /**
         * @param $remoteAddress
         * @throws \Exception
         */
        private function checkWhiteList($remoteAddress)
        {
            if (isset($this->parameters[self::PARAMETER_WHITELIST_IPS])) {
                $whitelistIps = explode(',', $this->parameters[self::PARAMETER_WHITELIST_IPS]);
                $whitelistIps = array_map("trim", $whitelistIps);
                if (!in_array($remoteAddress, $whitelistIps)) {
                    throw new \Exception('Ip address ' . $remoteAddress . ' is not on whitelist');
                }
            }
        }

        /**
         * @param $remoteAddress
         * @throws \Exception
         */
        private function checkBlackList($remoteAddress)
        {
            if (isset($this->parameters[self::PARAMETER_BLACKLIST_IPS])) {
                $blacklistIps = explode(',', $this->parameters[self::PARAMETER_BLACKLIST_IPS]);
                $blacklistIps = array_map("trim", $blacklistIps);
                if (in_array($remoteAddress, $blacklistIps)) {
                    throw new \Exception('Ip address ' . $remoteAddress . ' is on blacklist');
                }
            }
        }

        /**
         * @param $request
         * @return mixed|string
         */
        private function getRelativeCmsUri($request)
        {
            // TODO Use regex match parameter instead of calculating relative uri
            $pos = strpos($request::$relativeUri, $this->parameters[self::PARAMETER_CMS_PREFIX]);
            $relativeCmsUri = '/';
            if ($pos !== false) {
                $relativeCmsUri = substr_replace($request::$relativeUri, '', $pos, strlen($this->parameters[self::PARAMETER_CMS_PREFIX]));
            }
            return $relativeCmsUri;
        }

        /**
         * @param $relativeCmsUri
         */
        private function apiRouting($relativeCmsUri)
        {
            if ($relativeCmsUri == '/images.json') {
                header(self::CONTENT_TYPE_APPLICATION_JSON);
                die(json_encode($this->storage->getImages()));
            } elseif ($relativeCmsUri == '/files.json') {
                header(self::CONTENT_TYPE_APPLICATION_JSON);
                die(json_encode($this->storage->getFiles()));
            } elseif ($relativeCmsUri == '/documents.json') {
                header(self::CONTENT_TYPE_APPLICATION_JSON);
                die(json_encode($this->storage->getDocuments()));
            }
        }

        /**
         * @param $request
         * @param $relativeCmsUri
         */
        private function configurationRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/configuration') {
                $this->subTemplate = 'cms/configuration';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
            }

            $this->usersRouting($request, $relativeCmsUri);
            $this->documentTypesRouting($request, $relativeCmsUri);
            $this->bricksRouting($request, $relativeCmsUri);
            $this->imageSetRouting($request, $relativeCmsUri);
            $this->applicationComponentRouting($request, $relativeCmsUri);
        }


        /**
         * @param $request
         * @param $relativeCmsUri
         */
        private function usersRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/configuration/users') {
                $this->subTemplate = 'cms/configuration/users';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters[self::PARAMETER_USERS] = $this->storage->getUsers();
            } elseif ($relativeCmsUri == '/configuration/users/new') {
                $this->subTemplate = 'cms/configuration/users-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                if (isset($_POST[self::POST_PARAMETER_USERNAME])) {
                    $this->storage->addUser($request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/users');
                    exit;
                }
            } elseif ($relativeCmsUri == '/configuration/users/delete' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->storage->deleteUserBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/users');
                exit;
            } elseif ($relativeCmsUri == '/configuration/users/edit' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->subTemplate = 'cms/configuration/users-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters[self::PARAMETER_USER] = $this->storage->getUserBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                if (isset($_POST[self::POST_PARAMETER_USERNAME])) {
                    $this->storage->saveUser($request::$get[self::GET_PARAMETER_SLUG], $request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/users');
                    exit;
                }
            }
        }

        /**
         * @param $request
         * @param $relativeCmsUri
         */
        private function documentTypesRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/configuration/document-types') {
                $this->subTemplate = 'cms/configuration/document-types';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters[self::PARAMETER_DOCUMENT_TYPES] = $this->storage->getDocumentTypes();
            } elseif ($relativeCmsUri == '/configuration/document-types/new') {
                $this->subTemplate = 'cms/configuration/document-types-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $bricks = $this->storage->getBricks();
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->addDocumentType($request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/document-types');
                    exit;
                }
                $this->parameters[self::PARAMETER_BRICKS] = $bricks;
            } elseif ($relativeCmsUri == '/configuration/document-types/edit' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->subTemplate = 'cms/configuration/document-types-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $documentType = $this->storage->getDocumentTypeBySlug($request::$get[self::GET_PARAMETER_SLUG], false);
                $bricks = $this->storage->getBricks();
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->saveDocumentType($request::$get[self::GET_PARAMETER_SLUG], $request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/document-types');
                    exit;
                }
                $this->parameters[self::PARAMETER_DOCUMENT_TYPE] = $documentType;
                $this->parameters[self::PARAMETER_BRICKS] = $bricks;
            } elseif ($relativeCmsUri == '/configuration/document-types/delete' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->storage->deleteDocumentTypeBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/document-types');
                exit;
            }
        }

        /**
         * @param $request
         * @param $relativeCmsUri
         */
        private function bricksRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/configuration/bricks') {
                $this->subTemplate = 'cms/configuration/bricks';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters[self::PARAMETER_BRICKS] = $this->storage->getBricks();
            } elseif ($relativeCmsUri == '/configuration/bricks/new') {
                $this->subTemplate = 'cms/configuration/bricks-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->addBrick($request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/bricks');
                    exit;
                }
            } elseif ($relativeCmsUri == '/configuration/bricks/edit' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->subTemplate = 'cms/configuration/bricks-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $brick = $this->storage->getBrickBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->saveBrick($request::$get[self::GET_PARAMETER_SLUG], $request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/bricks');
                    exit;
                }
                $this->parameters[self::PARAMETER_BRICK] = $brick;
            } elseif ($relativeCmsUri == '/configuration/bricks/delete' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->storage->deleteBrickBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/bricks');
                exit;
            } elseif ($relativeCmsUri == '/configuration/image-set') {
                $this->subTemplate = 'cms/configuration/image-set';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters[self::PARAMETER_IMAGE_SET] = $this->storage->getImageSet();
            }
        }

        /**
         * @param $request
         * @param $relativeCmsUri
         */
        private function imageSetRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/configuration/image-set') {
                $this->subTemplate = 'cms/configuration/image-set';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters[self::PARAMETER_IMAGE_SET] = $this->storage->getImageSet();
            } elseif ($relativeCmsUri == '/configuration/image-set/edit' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->subTemplate = 'cms/configuration/image-set-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $imageSet = $this->storage->getImageSetBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->saveImageSet($request::$get[self::GET_PARAMETER_SLUG], $request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/image-set');
                    exit;
                }
                $this->parameters[self::PARAMETER_IMAGE_SET] = $imageSet;
            } elseif ($relativeCmsUri == '/configuration/image-set/new') {
                $this->subTemplate = 'cms/configuration/image-set-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->addImageSet($request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/image-set');
                    exit;
                }
            } elseif ($relativeCmsUri == '/configuration/image-set/delete' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->storage->deleteImageSetBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/image-set');
                exit;
            }
        }

        /**
         * @param $request
         * @param $relativeCmsUri
         */
        private function applicationComponentRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/configuration/application-components') {
                $this->subTemplate = 'cms/configuration/application-components';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $this->parameters['applicationComponents'] = $this->storage->getApplicationComponents();
            } elseif ($relativeCmsUri == '/configuration/application-components/new') {
                $this->subTemplate = 'cms/configuration/application-components-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->addApplicationComponent($request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/application-components');
                    exit;
                }
            } elseif ($relativeCmsUri == '/configuration/application-components/edit' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->subTemplate = 'cms/configuration/application-components-form';
                $this->parameters[self::PARAMETER_MAIN_NAV_CLASS] = 'configuration';
                $applicationComponent = $this->storage->getApplicationComponentBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                if (isset($request::$post[self::POST_PARAMETER_TITLE])) {
                    $this->storage->saveApplicationComponent($request::$get[self::GET_PARAMETER_SLUG], $request::$post);
                    header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/application-components');
                    exit;
                }
                $this->parameters['applicationComponent'] = $applicationComponent;
            } elseif ($relativeCmsUri == '/configuration/application-components/delete' && isset($request::$get[self::GET_PARAMETER_SLUG])) {
                $this->storage->deleteApplicationComponentBySlug($request::$get[self::GET_PARAMETER_SLUG]);
                header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX] . '/configuration/application-components');
                exit;
            }
        }

        private function logOffRouting($request, $relativeCmsUri)
        {
            if ($relativeCmsUri == '/log-off') {
                $_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL] = null;
                unset($_SESSION[self::SESSION_PARAMETER_CLOUD_CONTROL]);
                header('Location: ' . $request::$subfolders . $this->parameters[self::PARAMETER_CMS_PREFIX]);
                exit;
            }
        }

        public function setParameter($parameterName, $parameterValue)
        {
            $this->parameters[$parameterName] = $parameterValue;
        }

        public function getParameter($parameterName)
        {
            return $this->parameters[$parameterName];
        }
    }
}