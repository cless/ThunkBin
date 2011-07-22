<?php
    /**
     * Common error codes
     */
    class ErrorCodes
    {
        /**
         * Generic error
         */
        const E_UNKNOWN     = 0;
        
        /**
         * 404 not found error
         */
        const E_404         = 1;

        /**
         * Generic database error
         */
        const E_DATABASE    = 2;

        /**
         * generic acess denied error
         */
        const E_ACCESS      = 3;

        /**
         * Chained error, used when a different exception is re-created as a FramelessException
         */
        const E_CHAINED     = 4;

        /**
         * Frameless Runtime error
         */
        const E_RUNTIME        = 5;
    }
?>
