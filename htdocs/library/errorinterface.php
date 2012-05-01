<?php
    /**
     * The default error handler controller should implement this interface
     */
    interface ErrorInterface
    {
        /**
         *  \param $e The exception that was caught, causing this error handler to be created
         */
        public function __construct(Exception $e);

        /**
         * Handles the actual error, prints error messages or otherwise redirects the execution
         */
        public function Handle();
    }
?>
