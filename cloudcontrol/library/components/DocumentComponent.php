<?php
namespace library\components {

	use library\storage\Storage;

	/**
	 * Class DocumentComponent
	 *
	 * Has optional parameter `folder` to prefix the relative url with a folder
	 * Has optional parameter `document` to select a given document
	 * Has optional parameter `documentParameterName` to select the parametername to be used
	 * 		to set the found document to.
	 *
	 * @package library\components
	 */
	class DocumentComponent extends BaseComponent
	{
		protected $documentParameterName = 'document';

		public function run(Storage $storage)
		{
			parent::run($storage);

			if (isset($this->parameters['documentParameterName'])) {
				$this->documentParameterName = $this->parameters['documentParameterName'];
			}

			if ($this->matchedSitemapItem->regex == false) {
				if (isset($this->parameters['document'])) {
					$this->parameters[$this->documentParameterName] = $storage->getDocumentBySlug($this->parameters['document']);
				} else {
					throw new \Exception('When not using a regex, you need to set the parameter `document` with the path to the document in this sitemap item: ' . $this->matchedSitemapItem->title);
				}
			} else {
				if (isset($this->parameters['document'])) {
					$this->parameters[$this->documentParameterName] = $storage->getDocumentBySlug($this->parameters['document']);
				} else {
					$relativeDocumentUri = current($this->matchedSitemapItem->matches[1]);
					if (isset($this->parameters['folder'])) {
						if (substr($this->parameters['folder'], -1) !== '/') {
							$this->parameters['folder'] = $this->parameters['folder'] . '/';
						}
						$relativeDocumentUri = $this->parameters['folder'] . $relativeDocumentUri;
					}

					$document = $storage->getDocumentBySlug($relativeDocumentUri);

					if ($document->type == 'folder') {
						throw new \Exception('The found document is a folder.');
					}

					if ($document->state != 'published') {
						throw new \Exception('Found document is unpublished.');
					}
					$this->parameters[$this->documentParameterName] = $document;
				}
			}
		}
	}
}