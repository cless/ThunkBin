<?php
    /**
     * Exactly the same as the FramelessException with different default values. Please
     * See the FramelessException documentation for more info.
     */
    class DatabaseException extends FramelessException
    {
        public function __construct($message = 'An internal database error occured. Please try again later.',
                                    $code = 0, Exception $chained = null)
        {
            parent::__construct($message, $code, $chained);
        }
    }
?>
