<?php
    /**
     * All views should implement this interface.
     */
    interface ViewInterface
    {
        /**
         * Set the template file for the view
         *
         * \param template The template file, standard template directory should be /view/
         */
        public function SetTemplate($template);
        
        /**
         * Assign variables to the view
         *
         * \param name Variable name
         * \param value Variable value
         */
        public function SetVar($name, $value);

        /**
         * Render the view based on assigned variables
         */
        public function Draw();
    }
?>
