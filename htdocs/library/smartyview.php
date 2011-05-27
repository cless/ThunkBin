<?php
    /**
     * Implement a Smarty view with the desired interface. To use SmartyView /data/compile has to be
     * writable by the webserver.
     */
    class SmartyView implements ViewInterface
    { 
        private $smarty;
        private $template;
        
        /**
         * Initializes the SmartyView object
         */
        function __construct()
        {
            $this->smarty = new Smarty;
            $this->smarty->setTemplateDir(dirname(__FILE__) . '/../view/');
            $this->smarty->setCompileDir(dirname(__FILE__)  . '/../data/compile');
            $this->smarty->setCacheDir(dirname(__FILE__)    . '/../data/cache');
            $this->smarty->setConfigDir(dirname(__FILE__)   . '/../data/config');
        }
        
        /**
         * Sets a template to use for rendering
         *
         * \param template This is the filename or path of a template. template
         *                 directory is /data/view/
         */
        public function SetTemplate($template)
        {
            $this->template = $template;
        }
        
        /**
         * Assign a variable in the template (pretty much the same as the Smarty::Assign function)
         * \param name Variable name
         * \param value Desired value, see the smarty manual for more info
         */
        public function SetVar($name, $value)
        {
            $this->smarty->Assign($name, $value);
        }
        
        /**
         * Renders the output based on the template and assigned variables
         */
        public function Draw()
        {
            $this->smarty->Display($this->template);
        }
    }
?>
