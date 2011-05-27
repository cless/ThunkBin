<?php

    /*!
     *  Read ini configuration files from the data directory and provide read-only access to them via a Vector
     */
    class IniFile
    {
        private $data;
        private $vector;
        
        /*!
         * Initialize the IniFile object
         * \param file The filename or path of the configuration file. This path is relative to /data/
         */
        public function __construct($file)
        {
            $this->data = parse_ini_file(dirname(__FILE__) . '/../data/' . $file);
            $this->vector = new Vector($this->data);
        }
        
        /*!
         * Get a the ini file data as a Vector
         * \return A Vector representing the data is the configuration file
         */
        public function &GetVector()
        {
            return $this->vector;
        }
    }
?>
