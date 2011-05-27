<?php
    interface ControllerInterface
    {
        /**
         * Each Controller has to implement a constructor that takes a reference to a vector as argument.
         * The vector is passed by the bootstrap and contains all configuration variables from /data/config.ini
         */
        public function __construct(&$config);

        /**
         * has to return a string containing the default action for this controller
         */
        public function DefaultAction();
    }
