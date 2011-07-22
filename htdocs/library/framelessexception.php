<?php
    /**
     * Slight expansion of the default Exception that allows multiple messages
     * The idea is that some exceptions might want to display one message to the user
     * and log another message in an error log (e.g. 'a database error occured' vs the actual mysql error)
     */
    class FramelessException extends Exception
    {
        private $msglist;
        
        /**
         * \param $message Array of string messages OR a single string message
         * \param $code Error code for the exception (see php Exception for more info)
         */
        public function __construct($message, $code = 0, Exception $chained = null)
        {
            if(is_array($message))
                $this->msglist = $message;
            else
                $this->msglist = array($message);

            parent::__construct($this->msglist[0], $code, $chained);
        }
        
        /**
         * Fetches all the messages associated with this exception. 
         * \return An array of all the messages passed into this exception. If the exception was created with
         *         a string message instead of an array then this function will still return an array (with 1 member)
         */
        public function GetAllMessages()
        {
            return $this->msglist;
        }
    }
?>
