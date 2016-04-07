<?php
namespace library\components
{

	use library\cc\Request;
	use library\storage\Storage;

	class BaseComponent implements Component
	{
		/**
		 * @var string
		 */
		protected $template;
		/**
		 * @var Request
		 */
		protected $request;
		/**
		 * @var Storage
		 */
		protected $storage;
		/**
		 * @var mixed
		 */
		protected $renderedContent;
		/**
		 * @var array
		 */
		protected $parameters = array();

		/**
		 * BaseComponent constructor.
		 *
		 * @param string              $template
		 * @param Request $request
		 * @param array               $parameters
		 */
		public function __construct($template='', Request $request, $parameters=array())
		{
			$this->template = $template;
			$this->request = $request;
			$this->parameters = (array) $parameters;
		}
		
		/**
		 * Hook for implementation in derived classes
		 *
		 * @param Storage $storage
		 */
		public function run(Storage $storage)
		{
			$this->storage = $storage;
		}

		/**
		 * Renders the template
		 *
		 * @throws \Exception
		 */
		public function render()
		{
			$this->renderedContent = $this->renderTemplate($this->template);
		}

		/**
		 * Returns the rendered content
		 *
		 * @return mixed
		 */
		public function get()
		{
			return $this->renderedContent;
		}

		/**
		 * Decoupled render method, for usage in derived classes
		 *
		 * @param string $template
		 *
		 * @return string
		 * @throws \Exception
		 */
		public function renderTemplate($template='')
		{
			$templatePath = __DIR__ . '../../../templates/' . $template . '.php';
			if (realpath($templatePath) !== false) {
				ob_clean();
				$this->parameters['request'] = $this->request;
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