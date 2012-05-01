<?php
    /**
     * Basic example of an error controller
     * Make sure that the error handling class does not itself throw exceptions
     */
    class Error implements ErrorInterface
    {
        private $exception;
        private $view;

        public function __construct(Exception $e)
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
            $this->view->SetVar('title', 'Error');
        }
        
        // Decide what error handler to run
        public function Handle()
        {
            try
            {
                if($this->exception instanceof NotFoundException)
                    $this->Handle404();
                elseif($this->exception instanceof DatabaseException)
                    $this->HandleDb();
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
        
        // database handler
        private function HandleDb()
        {
            $error = 'Internal database error, please try again later.';
            $this->view->SetVar('error', $error);
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
