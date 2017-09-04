<?php
/**
 * Created by jensk on 4-9-2017.
 */

namespace CloudControl\Cms\components\cms\document {


    use CloudControl\Cms\cc\Request;
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
            if ($relativeCmsUri == '/documents/new-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_PATH])) {
                $this->newFolderRoute($request, $cmsComponent);
            } else if ($relativeCmsUri == '/documents/edit-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
                $this->editFolderRoute($request, $cmsComponent);
            } else if ($relativeCmsUri == '/documents/delete-folder' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
                $this->deleteFolderRoute($request, $cmsComponent);
            }
        }

        /**
         * @param $request
         * @param CmsComponent $cmsComponent
         */
        private function newFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->subTemplate = 'documents/folder-form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::GET_PARAMETER_PATH])) {
                $cmsComponent->storage->getDocuments()->addDocumentFolder($request::$post);
                $cmsComponent->storage->getActivityLog()->add('created folder ' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . ' in path ' . $request::$get[CmsComponent::GET_PARAMETER_PATH], 'plus');
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }
        }

        /**
         * @param $request
         * @param CmsComponent $cmsComponent
         */
        private function editFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->subTemplate = 'documents/folder-form';
            $folder = $cmsComponent->storage->getDocuments()->getDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);

            $path = $request::$get[CmsComponent::GET_PARAMETER_SLUG];
            $path = explode('/', $path);
            array_pop($path);
            $path = implode('/', $path);

            $request::$get[CmsComponent::GET_PARAMETER_PATH] = '/' . $path;

            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post['content'])) {
                $cmsComponent->storage->getDocuments()->addDocumentFolder($request::$post);
                $cmsComponent->storage->getActivityLog()->add('edited folder ' . $request::$post[CmsComponent::POST_PARAMETER_TITLE] . ' in path ' . $request::$get[CmsComponent::GET_PARAMETER_PATH], 'pencil');
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents');
                exit;
            }

            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_DOCUMENTS);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_FOLDER, $folder);
        }

        /**
         * @param $request
         * @param CmsComponent $cmsComponent
         */
        private function deleteFolderRoute($request, $cmsComponent)
        {
            $cmsComponent->storage->getDocuments()->deleteDocumentFolderBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
            $cmsComponent->storage->getActivityLog()->add('deleted folder /' . $request::$get[CmsComponent::GET_PARAMETER_SLUG], 'trash');
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/documents?folder-delete');
            exit;
        }
    }
}