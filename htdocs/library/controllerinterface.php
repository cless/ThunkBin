<?php
    interface ControllerInterface
    {
        /**
         * Each Controller has to implement a constructor that takes a reference to a IniFile as argument.
         * The IniFile is passed by the bootstrap and contains all configuration variables from /data/config.ini
         * \param config Reference to IniFile, used to pass the configuration variables from the bootstrap into the controller
         */
        public function __construct(&$config);

        /**
         * This function is called by the bootstrap to translate a virtual action into a function
         * eg if you visit /controller/action/ then ActionToFunction('action') is called. If this
         * function then returns 'test' then $controler->test(); is called
         * \param action the name of the virutal action that is to be executed
         * \return the name of the function that is linked to the action
         */
        public function ActionToFunction($action);
    }
