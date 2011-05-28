<?php

    /**
     *  Read ini configuration files from the data directory and provide read-only access to them via a Vector
     */
    class IniFile
    {
        private $data;
        private $basevector;
        private $emptyvector;
        private $cache;
        
        /**
         * Initialize the IniFile object
         * \param file The filename or path of the configuration file. This path is relative to /data/
         */
        public function __construct($file)
        {
            $this->data = parse_ini_file(dirname(__FILE__) . '/../data/' . $file, true);
            
            // Create the basic vector
            $this->vector = new Vector($this->data, true);

            // Empty vector to return on non existing sections, easier and cleaner than throwing exceptions or error values
            $emptyarray = array();
            $this->emptyvector = new Vector($emptyarray, true);

            // empty cache
            $this->cache = array();
        }
        
        /**
         * Get the ini file data as a Vector
         * \param section string representation of the ini section, if no section is given then a global Vector will be returned.
         *                the global Vector also includes all sections as array values. If the section does not exist an empty
         *                Vector will be returned.
         * \return A read only Vector representing the data is the configuration file
         */
        public function &GetVector($section = false)
        {
            if($section && isset($this->data[$section]) && is_array($this->data[$section]))
                return $this->GetSectionVector($section);
            else
                return $this->emptyvector;

            return $this->vector;
        }
        

        // Create a section vector and cache it
        private function &GetSectionVector($section)
        {
            if (!isset($this->cache[$section]))
                $this->cache[$section] = new Vector($this->data[$section], true);

            return $this->cache[$section];
        }
    }
?>
