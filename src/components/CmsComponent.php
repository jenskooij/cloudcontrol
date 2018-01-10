<?php

namespace CloudControl\Cms\components {

    use CloudControl\Cms\components\cms\BaseRouting;
    use CloudControl\Cms\components\cms\CmsConstants;
    use CloudControl\Cms\crypt\Crypt;
    use CloudControl\Cms\storage\Storage;

    class CmsComponent extends BaseComponent
    {
        /**
         * @var \CloudControl\Cms\storage\Storage
         */
        public $storage;

        public $subTemplate;

        public $autoUpdateSearchIndex = true;


        /**
         * @param Storage $storage
         *
         * @return void
         * @throws \Exception
         */
        public function run(Storage $storage)
        {
            $this->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::MAIN_NAV_CLASS);
            $this->storage = $storage;

            $remoteAddress = $_SERVER['REMOTE_ADDR'];
            $this->checkWhiteList($remoteAddress);
            $this->checkBlackList($remoteAddress);

            $this->checkAutoUpdateSearchIndex();

            $this->checkLogin();

            $this->setParameter(CmsConstants::PARAMETER_USER_RIGHTS, $_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]->rights);

            $this->routing();

            $this->renderBody();
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

            if (!isset($_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL])) {
                if (isset($request::$post[CmsConstants::POST_PARAMETER_USERNAME], $request::$post[CmsConstants::POST_PARAMETER_PASSWORD])) {
                    $this->checkLoginAttempt($request);
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
            $loginTemplatePath = CmsConstants::LOGIN_TEMPLATE_PATH;
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
            $userRights = $_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]->rights;

            $baseRouting = new BaseRouting($this->request, $relativeCmsUri, $this);
            $baseRouting->setUserRights($userRights);
            $baseRouting->route();
        }

        /**
         * @param $remoteAddress
         *
         * @throws \Exception
         */
        protected function checkWhiteList($remoteAddress)
        {
            if (isset($this->parameters[CmsConstants::PARAMETER_WHITELIST_IPS])) {
                $whitelistIps = explode(',', $this->parameters[CmsConstants::PARAMETER_WHITELIST_IPS]);
                $whitelistIps = array_map("trim", $whitelistIps);
                if (!in_array($remoteAddress, $whitelistIps)) {
                    throw new \Exception('Ip address ' . $remoteAddress . ' is not on whitelist');
                }
            }
        }

        /**
         * @param $remoteAddress
         *
         * @throws \Exception
         */
        protected function checkBlackList($remoteAddress)
        {
            if (isset($this->parameters[CmsConstants::PARAMETER_BLACKLIST_IPS])) {
                $blacklistIps = explode(',', $this->parameters[CmsConstants::PARAMETER_BLACKLIST_IPS]);
                $blacklistIps = array_map("trim", $blacklistIps);
                if (in_array($remoteAddress, $blacklistIps)) {
                    throw new \Exception('Ip address ' . $remoteAddress . ' is on blacklist');
                }
            }
        }

        /**
         * @param $request
         *
         * @return mixed|string
         */
        protected function getRelativeCmsUri($request)
        {
            // TODO Use regex match parameter instead of calculating relative uri
            $pos = strpos($request::$relativeUri, $this->parameters[CmsConstants::PARAMETER_CMS_PREFIX]);
            $relativeCmsUri = '/';
            if ($pos !== false) {
                $relativeCmsUri = substr_replace($request::$relativeUri, '', $pos, strlen($this->parameters[CmsConstants::PARAMETER_CMS_PREFIX]));
            }

            return $relativeCmsUri;
        }

        /**
         * @param $parameterName
         * @param $parameterValue
         */
        public function setParameter($parameterName, $parameterValue)
        {
            $this->parameters[$parameterName] = $parameterValue;
        }

        /**
         * @param $parameterName
         * @return mixed
         */
        public function getParameter($parameterName)
        {
            return $this->parameters[$parameterName];
        }

        /**
         * @throws \Exception
         */
        protected function renderBody()
        {
            if ($this->subTemplate !== null) {
                $this->parameters[CmsConstants::PARAMETER_BODY] = $this->renderTemplate($this->subTemplate);
            }
        }

        /**
         * @param Crypt $crypt
         * @param Request $request
         * @throws \Exception
         */
        protected function invalidCredentials($crypt, $request)
        {
            $crypt->encrypt($request::$post[CmsConstants::POST_PARAMETER_PASSWORD], 16); // Buy time, to avoid brute forcing
            $this->parameters[CmsConstants::PARAMETER_ERROR_MESSAGE] = CmsConstants::INVALID_CREDENTIALS_MESSAGE;
            $this->showLogin();
        }

        /**
         * @param $user
         * @param Crypt $crypt
         * @param Request $request
         * @throws \Exception
         */
        protected function checkPassword($user, $crypt, $request)
        {
            $salt = $user->salt;
            $password = $user->password;

            $passwordCorrect = $crypt->compare($request::$post[CmsConstants::POST_PARAMETER_PASSWORD], $password, $salt);

            if ($passwordCorrect) {
                $_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL] = $user;
                $this->storage->getActivityLog()->add('logged in', 'user');
            } else {
                $this->parameters[CmsConstants::PARAMETER_ERROR_MESSAGE] = CmsConstants::INVALID_CREDENTIALS_MESSAGE;
                $this->showLogin();
            }
        }

        /**
         * @param $request
         * @throws \Exception
         */
        protected function checkLoginAttempt($request)
        {
            $user = $this->storage->getUsers()->getUserByUsername($request::$post[CmsConstants::POST_PARAMETER_USERNAME]);
            $crypt = new Crypt();
            if (empty($user)) {
                $this->invalidCredentials($crypt, $request);
            } else {
                $this->checkPassword($user, $crypt, $request);
            }
        }

        /**
         * @param $template
         * @param null $application
         * @return string
         */
        protected function getTemplateDir($template, $application = null)
        {
            return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.php';
        }

        private function checkAutoUpdateSearchIndex()
        {
            if (isset($this->parameters[CmsConstants::PARAMETER_AUTO_UPDATE_SEARCH_INDEX])) {
                $param = $this->parameters[CmsConstants::PARAMETER_AUTO_UPDATE_SEARCH_INDEX];
                if ($param === 'false') {
                    $this->autoUpdateSearchIndex = false;
                }
            }
        }

    public static function isCmsLoggedIn()
    {
        return isset($_SESSION[CmsConstants::SESSION_PARAMETER_CLOUD_CONTROL]);
    }

}
}