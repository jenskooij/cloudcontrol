<?php
namespace library\components {

	use library\storage\Storage;

	class DocumentComponent extends BaseComponent
	{
		public function run(Storage $storage)
		{
			parent::run($storage);

			if ($this->matchedSitemapItem->regex == false) {
				throw new \Exception('This component expects regex usage. For example: /^\/your-prefix\/(.*)/');
			}

			$relativeDocumentUri = current($this->matchedSitemapItem->matches[1]);
			if (isset($this->parameters['folder'])) {
				$relativeDocumentUri = $this->parameters['folder'] . $relativeDocumentUri;
			}

			$document = $storage->getDocumentBySlug($relativeDocumentUri);

			if ($document->type == 'folder') {
				throw new \Exception('The found document is a folder.');
			}

			if ($document->state != 'published') {
				throw new \Exception('Found document is unpublished.');
			}
			$this->parameters['document'] = $document;
		}
	}
}