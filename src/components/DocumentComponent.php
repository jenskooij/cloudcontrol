<?php

namespace CloudControl\Cms\components {

    use CloudControl\Cms\storage\entities\Document;
    use CloudControl\Cms\storage\Storage;

    /**
     * Class DocumentComponent
     *
     * Has optional parameter `folder` to prefix the relative url with a folder
     * Has optional parameter `document` to select a given document
     * Has optional parameter `documentParameterName` to select the parametername to be used
     *        to set the found document to.
     *
     * @package CloudControl\Cms\components
     */
    class DocumentComponent extends NotFoundComponent
    {
        protected $documentParameterName = self::PARAMETER_DOCUMENT;
        const DOCUMENT_STATE_UNPUBLISHED = 'unpublished';
        const DOCUMENT_STATE_PUBLISHED = 'published';
        const DOCUMENT_TYPE_FOLDER = 'folder';

        const PARAMETER_DOCUMENT = 'document';
        const PARAMETER_DOCUMENT_PARAMETER_NAME = 'documentParameterName';


        /**
         * @param Storage $storage
         *
         * @return mixed|void
         * @throws \Exception
         */
        public function run(Storage $storage)
        {
            parent::run($storage);

            $this->checkParameters();

            if ($this->matchedSitemapItem === null) { // If no sitemapitem, its an application component
                $this->runLikeApplicationComponent();
            } else {
                $this->runLikeRegularComponent();
            }
        }

        /**
         * Checks to see if any parameters were defined in the cms and acts according
         */
        protected function checkParameters()
        {
            if (isset($this->parameters[self::PARAMETER_DOCUMENT_PARAMETER_NAME])) {
                $this->documentParameterName = $this->parameters[self::PARAMETER_DOCUMENT_PARAMETER_NAME];
            }
        }

        /**
         * Run as application component
         *
         * @throws \Exception
         */
        protected function runLikeApplicationComponent()
        {
            if (isset($this->parameters[self::PARAMETER_DOCUMENT])) {
                $this->parameters[$this->documentParameterName] = $this->storage->getDocuments()->getDocumentBySlug($this->parameters[self::PARAMETER_DOCUMENT]);
                unset($this->parameters[self::PARAMETER_DOCUMENT]);
            } else {
                throw new \RuntimeException('When used as application component, you need to specify a document.');
            }
        }

        /**
         * Run as regular component
         *
         * @throws \Exception
         */
        protected function runLikeRegularComponent()
        {
            if ($this->matchedSitemapItem->regex === false || isset($this->parameters[self::PARAMETER_DOCUMENT])) {
                $this->runWithoutRegex();
            } else {
                $this->runWithRegex();
            }
        }

        /**
         * Run without regex
         *
         * @throws \Exception
         */
        protected function runWithoutRegex()
        {
            if (isset($this->parameters[self::PARAMETER_DOCUMENT])) {
                $this->runByDocumentParameter();
            } else {
                throw new \RuntimeException('When not using a regex, you need to set the parameter `document` with the path to the document in this sitemap item: ' . $this->matchedSitemapItem->title);
            }
        }

        /**
         * Run with regex
         *
         * @throws \Exception
         */
        protected function runWithRegex()
        {
            $relativeDocumentUri = $this->checkForSpecificFolder();

            $state = $this->getState();

            $document = $this->storage->getDocuments()->getDocumentBySlug($relativeDocumentUri, $state);

            if ($document instanceof Document && $document->state === self::DOCUMENT_STATE_PUBLISHED && $document->type !== self::DOCUMENT_TYPE_FOLDER) {
                $this->parameters[$this->documentParameterName] = $document;
            } else {
                $this->set404Header();
                $this->set404Template();
            }
        }

        /**
         * Run using the given `document` parameter
         * @throws \Exception
         */
        protected function runByDocumentParameter()
        {
            $state = $this->getState();
            $document = $this->storage->getDocuments()->getDocumentBySlug($this->parameters[self::PARAMETER_DOCUMENT], $state);
            if ($document instanceof Document) {
                $this->parameters[$this->documentParameterName] = $document;
            } else {
                $this->set404Header();
                $this->set404Template();
            }
        }

        /**
         * @return mixed|string
         */
        protected function checkForSpecificFolder()
        {
            $relativeDocumentUri = current($this->matchedSitemapItem->matches[1]);
            if (isset($this->parameters[self::DOCUMENT_TYPE_FOLDER])) {
                if (substr($this->parameters[self::DOCUMENT_TYPE_FOLDER], -1) !== '/') {
                    $this->parameters[self::DOCUMENT_TYPE_FOLDER] .= '/';
                }
                $relativeDocumentUri = $this->parameters[self::DOCUMENT_TYPE_FOLDER] . $relativeDocumentUri;
            }
            return $relativeDocumentUri;
        }

        /**
         * @return string
         */
        protected function getState()
        {
            $state = CmsComponent::isCmsLoggedIn() ? self::DOCUMENT_STATE_UNPUBLISHED : self::DOCUMENT_STATE_PUBLISHED;
            return $state;
        }
    }
}