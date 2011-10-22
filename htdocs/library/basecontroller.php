<?php
    /**
     * BaseController provides the most basic functionality that every almost every controller needs.
     * You have easy access to arguments passed to the controller/action pair and it cleans $_GET $_POST
     * and $_COOKIE from magic quotes litter.
     */
    abstract class BaseController implements ControllerInterface
    {
        /**
         * Array with parsed contents of the config file at /data/config.ini
         */
        protected $config;

        /**
         * Array of action => function pairs. Each key is a valid action that can be accessed via
         * http://www.example.com/controller/action/
         * the bootstrap then calls the function that corresponds to $actions['action']
         * derived classes should set this member in the constructor
         * If no action is not given in the url then $actions['default'] will be used
         * If the array has ONLY a 'default' member and no other members at all then the action
         * passed in the URL will be ignored and repurposed as a normal argument (see BaseController::args)
         */
        protected $actions;

        /**
         * array with all 'file variables' passed to the framework. The contents of this array are all
         * the filenames the client is requesting from the server split on the '/' character.
         * for example if the client visits http://www.example.org/controller/action/some/data
         * then $args[0] would be "controller" and $args[3] would be "data" and so on.
         */
        protected $args;

        /**
         * Initializes the controller.
         * \param config  a reference to an array that contains all configuration variables from /data/config.ini,
         *                The array is created and passed into the controllers by the bootstrap and derivative
         *                classes should pass it into the base controller.
         * \param bootargs a reference to an array, see BaseController::args for more info
         * \param session When set to false the BaseController will not create a session
         *                To create a named session pass a string with the name to this parameter.
         *                pass true (default) to start an unnamed session.
         */
        public function __construct(&$config, &$args, $session = true)
        {
            $this->config   =& $config;
            $this->args     =& $args; 
            $this->ScrubGlobals();
            $this->SessionInit($session);
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

        /**
         * This function is called by the bootstrap to translate a virtual action into a function
         * eg if you visit /controller/action/ then ActionToFunction('action') is called. If this
         * function then returns 'test' then $controler->test(); is called
         * \param action the name of the virutal action that is to be executed
         * \return the name of the function that is linked to the action
         */
        public function ActionToFunction($action)
        {
            if(count($this->actions) == 1 && isset($this->actions['default']))
                return $this->actions['default'];
            else if(isset($this->actions[$action]))
                return $this->actions[$action];
            else
                return false;
        }
    }
?>
