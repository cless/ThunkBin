<?php
    /**
     * Exactly the same as the FramelessException with different default values. Please
     * See the FramelessException documentation for more info.
     */
    class AccessException extends FramelessException
    {
        public function __construct($message = 'Access denied. You do not have the correct privileges to view this page.',
                                    $code = 0, Exception $chained = null)
        {
            parent::__construct($message, $code, $chained);
        }
    }
?>
