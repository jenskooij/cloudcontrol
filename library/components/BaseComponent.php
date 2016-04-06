<?php
namespace library\components
{
	class BaseComponent implements Component
	{
		protected $template;
		protected $request;
		
		protected $storage;
		
		protected $renderedContent;
		
		protected $parameters = array();
		
		public function __construct($template, \library\cc\Request $request, $parameters)
		{
			$this->template = $template;
			$this->request = $request;
			$this->parameters = (array) $parameters;
		}
		
		/*
		 * Hook for implementation in derived classes
		 */
		public function run(\library\storage\Storage $storage)
		{
			$this->storage = $storage;
		}
		
		public function render()
		{
			$templatePath = __DIR__ . '../../../templates/' . $this->template . '.php';
			$this->renderedContent = $this->renderTemplate($this->template);
		}
		
		public function get()
		{
			return $this->renderedContent;
		}
		
		public function renderTemplate($template)
		{
			$templatePath = __DIR__ . '../../../templates/' . $template . '.php';
			if (realpath($templatePath) !== false) {
				ob_clean();
				extract($this->parameters);
				include($templatePath);
				return ob_get_contents();
			} else {
				throw new \Exception('Couldnt find template ' . $templatePath);
			}
		}
	}
}
?>