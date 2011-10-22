<?php
    /**
     * Basic example of an error controller
     * Make sure that the error handling class does not itself throw exceptions
     */
    class Error implements ErrorInterface
    {
        private $exception;
        private $view;

        public function __construct(FramelessException $e)
        {
            $this->exception =& $e;
            $config = parse_ini_file('data/config.ini', true);
            // create base url
            $this->base = 'http';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
                $this->base .= 's';
            $this->base .= '://' . $_SERVER['HTTP_HOST'] . $config['thunkbin']['basedir'];
            
            // Create view
            $this->view = new SmartyView;
            $this->view->SetVar('base', $this->base);
            $this->view->SetTemplate('error.tpl');
        }
        
        // Decide what error handler to run
        public function Handle()
        {
            try
            {
                if($this->exception->GetCode() == ErrorCodes::E_404)
                    $this->Handle404();
                else
                   $this->HandleUnknown();
            }
            catch (Exception $e)
            {
                // Derp double error
                echo 'Oops... something went seriously wrong here.';
            }
        }
        
        // 404 handler
        private function Handle404()
        {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            $this->view->SetVar('error', '404 file not found.');
            $this->view->Draw();
        }
            
        // Generic handler
        private function HandleUnknown()
        {
            $this->view->SetVar('error', $this->exception->GetMessage());
            $this->view->Draw();
        }
    }
?>
