<?php

namespace CloudControl\Cms\components {

    use CloudControl\Cms\cc\Request;
    use CloudControl\Cms\storage\Storage;

    /**
     * Interface Component
     * @package CloudControl\Cms\components
     */
    interface Component
    {
        /**
         * Component constructor.
         *
         * @param                     $template
         * @param Request $request
         * @param                     $parameters
         * @param                     $matchedSitemapItem
         */
        public function __construct($template, Request $request, $parameters, $matchedSitemapItem);

        /**
         * @param Storage $storage
         */
        public function run(Storage $storage);

        /**
         * @return void
         */
        public function render();

        /**
         * @return mixed
         */
        public function get();

        /**
         * @return \stdClass
         */
        public function getParameters();
    }
}