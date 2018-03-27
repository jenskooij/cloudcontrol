<?php
/**
 * Created by jensk on 4-9-2017.
 */

namespace CloudControl\Cms\components\cms\document {


    use CloudControl\Cms\cc\Request;
    use CloudControl\Cms\components\cms\CmsConstants;
    use CloudControl\Cms\components\cms\CmsRouting;
    use CloudControl\Cms\components\CmsComponent;

    class FolderRouting implements CmsRouting
    {

        /**
         * CmsRouting constructor.
         *
         * @param Request $request
         * @param string $relativeCmsUri
         * @param CmsComponent $cmsComponent
         */
        public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
        {
            if ($relativeCmsUri == '/documents/new-folder' && isset($request::$get[CmsConstants::GET_PARAMETER_PATH])) {
                $this->newFolderRoute($request, $cmsComponent);
            } elseif ($relativeCmsUri == '/documents/edit-folder' && isset($request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
                $this->editFolderRoute($request, $cmsComponent);
            } elseif ($relativeCmsUri == '/documents/delete-folder' && isset($request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
                $this->deleteFolderRoute($request, $cmsComponent);
            }
        }

        /**
         * @param Request $request
         * @param CmsComponent $cmsComponent
         */
        private function newFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->subTemplate = 'documents/folder-form';
            $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_DOCUMENTS);
            if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$post[CmsConstants::GET_PARAMETER_PATH])) {
                $cmsComponent->storage->getDocuments()->addDocumentFolder($request::$post);
                $cmsComponent->storage->getActivityLog()->add('created folder ' . $request::$post[CmsConstants::POST_PARAMETER_TITLE] . ' in path ' . $request::$get[CmsConstants::GET_PARAMETER_PATH],
                    'plus');
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }
        }

        /**
         * @param Request $request
         * @param CmsComponent $cmsComponent
         */
        private function editFolderRoute($request, $cmsComponent)
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
         */
        private function deleteFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->storage->getDocuments()->deleteDocumentFolderBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
            $cmsComponent->storage->getActivityLog()->add('deleted folder /' . $request::$get[CmsConstants::GET_PARAMETER_SLUG],
                'trash');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/documents?folder-delete');
            exit;
        }
    }
}