<?php
    class Info extends BaseController
    {
        private $view;

        public function __construct(&$config, &$args)
        {
            parent::__construct($config, $args);

            // create base url
            $this->base = 'http';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
                $this->base .= 's';
            $this->base .= '://' . $_SERVER['HTTP_HOST'] . $config['thunkbin']['basedir'];
            
            // Create view
            $this->view = new SmartyView;
            $this->view->SetVar('base', $this->base);
            
            // Set actions we handle
            $this->actions = array('default'    => 'faq',
                                   'faq'        => 'faq',
                                   'privacy'    => 'privacy',
                                   'terms'      => 'terms');
        }
        
        public function faq()
        {
            $this->view->SetTemplate('faq.tpl');
            $this->view->SetVar('title', 'FAQ');
            $this->view->Draw(); 
        }

        public function privacy()
        {
            $this->view->SetTemplate('privacy.tpl');
            $this->view->SetVar('title', 'Privacy Policy');
            $this->view->Draw();
        }

        public function terms()
        {
            $this->view->SetTemplate('terms.tpl');
            $this->view->SetVar('title', 'Terms of Use');
            $this->view->Draw();
        }
    }
