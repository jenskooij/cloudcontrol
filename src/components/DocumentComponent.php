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
        protected $documentParameterName = 'document';

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
            if (isset($this->parameters['documentParameterName'])) {
                $this->documentParameterName = $this->parameters['documentParameterName'];
            }
        }

        /**
         * Run as application component
         *
         * @throws \Exception
         */
        protected function runLikeApplicationComponent()
        {
            if (isset($this->parameters['document'])) {
                $this->parameters[$this->documentParameterName] = $this->storage->getDocuments()->getDocumentBySlug($this->parameters['document']);
                unset($this->parameters['document']);
            } else {
                throw new \Exception('When used as application component, you need to specify a document.');
            }
        }

        /**
         * Run as regular component
         *
         * @throws \Exception
         */
        protected function runLikeRegularComponent()
        {
            if ($this->matchedSitemapItem->regex == false || isset($this->parameters['document'])) {
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
            if (isset($this->parameters['document'])) {
                $this->runByDocumentParameter();
            } else {
                throw new \Exception('When not using a regex, you need to set the parameter `document` with the path to the document in this sitemap item: ' . $this->matchedSitemapItem->title);
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

            $document = $this->storage->getDocuments()->getDocumentBySlug($relativeDocumentUri);

            if ($document instanceof Document && $document->state == 'published' && $document->type != 'folder') {
                $this->parameters[$this->documentParameterName] = $document;
            } else {
                $this->set404Header();
                $this->set404Template();
            }
        }

        /**
         * Run using the given `document` parameter
         */
        protected function runByDocumentParameter()
        {
            $document = $this->storage->getDocuments()->getDocumentBySlug($this->parameters['document']);
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
            if (isset($this->parameters['folder'])) {
                if (substr($this->parameters['folder'], -1) !== '/') {
                    $this->parameters['folder'] = $this->parameters['folder'] . '/';
                }
                $relativeDocumentUri = $this->parameters['folder'] . $relativeDocumentUri;
            }
            return $relativeDocumentUri;
        }
    }
}