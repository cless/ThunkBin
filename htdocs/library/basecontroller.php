<?php
    /**
     * BaseController provides the most basic functionality that every almost every controller needs.
     * It provides a members to access the post, get, config and session variables and makes sure these
     * variables are not affected by magic_quotes_gpc, magic_quotes_sybase and magic_quotes_runtime
     */
    abstract class BaseController implements ControllerInterface
    {
        /**
         * read only Vector that allows access the configuration in /data/config.ini
         */
        protected $config;
        
        /**
         * read only Vector that allows access the GET variables posted with the http request
         */
        protected $get;
        
        /**
         * read only Vector that allows access the POST variables posted with the http request
         */
        protected $post;

        /**
         * writable vector that gives you access to the session variables
         */
        protected $session;

        /**
         * Initializes the controller.
         * \param config 
         * \param session When set to false the BaseController will not create a session and the
         *                BaseController::session vector will NOT be accessible.
         *                To create a named session pass a string with the name to this parameter.
         *                pass true (default) to start an unnamed session.
         */
        public function __construct(&$config, $session = true)
        {
            $this->config   = $config;
            
            $this->ScrubGlobals();

            $this->get      = new Vector($_GET, true);
            $this->post     = new Vector($_POST, true);
        }

        // Clean all global data of magic_quotes litter
        private function ScrubGlobals()
        {
            if(get_magic_quotes_gpc() || ini_get('magic_quotes_sybase'))
            {
                foreach($_POST as &$value)
                    $value = stripslashes($value);
                
                foreach($_GET as &$value)
                    $value = stripslashes($value);
                
                foreach($_COOKIE as &$value)
                    $value = stripslashes($value);
            }
            ini_set('magic_quotes_runtime', 0);
        }
        
        // Initiate a session, possibly named
        private function SessionInit($session)
        {
            if($session == false)
                return;
            
            if($session !== true)
                session_name($session);

            session_start();
        }
    }
?>
