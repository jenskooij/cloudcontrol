<?php
/**
 * Created by jensk on 4-9-2017.
 */

namespace CloudControl\Cms\components\cms\document {


    use CloudControl\Cms\cc\Request;
    use CloudControl\Cms\components\cms\CmsConstants;
    use CloudControl\Cms\components\cms\CmsRouting;
    use CloudControl\Cms\components\CmsComponent;

    class FolderRouting extends CmsRouting
    {

        protected static $routes = array(
            '/documents/new-folder' => 'newFolderRoute',
            '/documents/edit-folder' => 'editFolderRoute',
            '/documents/delete-folder' => 'deleteFolderRoute',
        );

        /**
         * CmsRouting constructor.
         *
         * @param Request $request
         * @param string $relativeCmsUri
         * @param CmsComponent $cmsComponent
         * @throws \Exception
         */
        public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
        {
            $this->doRouting($request, $relativeCmsUri, $cmsComponent);
        }

        /**
         * @param Request $request
         * @param CmsComponent $cmsComponent
         * @throws \Exception
         */
        protected function newFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->subTemplate = 'documents/folder-form';
            $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_DOCUMENTS);
            $path = $request::$get[CmsConstants::GET_PARAMETER_PATH];
            if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $path)) {
                $cmsComponent->storage->getDocuments()->addDocumentFolder($request::$post);
                $cmsComponent->storage->getActivityLog()->add('created folder ' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . ' in path ' . $path,
                    'plus');
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?path=' . $path);
                exit;
            }
        }

        /**
         * @param Request $request
         * @param CmsComponent $cmsComponent
         * @throws \Exception
         */
        protected function editFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->subTemplate = 'documents/folder-form';
            $folder = $cmsComponent->storage->getDocuments()->getDocumentFolderBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);

            $path = $request::$get[CmsConstants::GET_PARAMETER_SLUG];
            $path = explode('/', $path);
            array_pop($path);
            $path = implode('/', $path);

            $request::$get[CmsConstants::GET_PARAMETER_PATH] = '/' . $path;

            if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$post['content'])) {
                $cmsComponent->storage->getDocuments()->addDocumentFolder($request::$post);
                $cmsComponent->storage->getActivityLog()->add('edited folder ' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . ' in path ' . $request::$get[CmsConstants::GET_PARAMETER_PATH],
                    'pencil');
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }

            $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_DOCUMENTS);
            $cmsComponent->setParameter(CmsConstants::PARAMETER_FOLDER, $folder);
        }

        /**
         * @param Request $request
         * @param CmsComponent $cmsComponent
         * @throws \Exception
         */
        protected function deleteFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->storage->getDocuments()->deleteDocumentFolderBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
            $cmsComponent->storage->getActivityLog()->add('deleted folder /' . $request::$get[CmsConstants::GET_PARAMETER_SLUG],
                'trash');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?folder-delete');
            exit;
        }
    }
}