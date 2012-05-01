<?php
    /**
     * Exactly the same as the FramelessException with different default values. Please
     * See the FramelessException documentation for more info.
     */
    class NotFoundException extends FramelessException
    {
        public function __construct($message = '404 File Not Found', $code = 0, Exception $chained = null)
        {
            parent::__construct($message, $code, $chained);
        }
    }
?>
