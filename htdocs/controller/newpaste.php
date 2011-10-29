<?php

    class NewPaste extends BaseController
    {
        private $view;
        private $model;
        private $base;
        private $pastewriter;

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
            $this->view->SetTemplate('newpaste.tpl');
            $this->view->SetVar('base', $this->base);
            
            // Derp model
            $this->pastemodel = new PasteModel($this->config['database']['host'],
                                               $this->config['database']['user'],
                                               $this->config['database']['pass'],
                                               $this->config['database']['db']);
            
            $this->cfgmodel = new ConfigModel($this->config['database']['host'],
                                              $this->config['database']['user'],
                                              $this->config['database']['pass'],
                                              $this->config['database']['db']);
            
            $this->PasteWriter = new PasteWriter($this->pastemodel, $this->cfgmodel);
            
            // Set all actions we handle
            $this->actions = array('default' => 'create',
                                   'create'  => 'create',
                                   'save'    => 'save');
        }
        
        public function Create()
        {
            if($this->PasteWriter->IsSpammer())
                $this->view->SetVar('spam', true);
            $this->view->SetVar('title', 'Create New Paste');
            $this->view->SetVar('maxfiles', $this->cfgmodel->GetValue('MAX_FILES'));
            $this->view->SetVar('languages', $this->pastemodel->GetLanguages());
            $this->view->Draw();
        }

        public function Save()
        {
            if($this->PasteWriter->IsSpammer())
            {
                $this->view->SetVar('spam', true);
                $this->view->Draw();
                return;
            }

            list($values, $errors) = $this->PasteWriter->VerifyData();
            if($errors != null)
            {
                $this->view->SetVar('errors', $errors);
                $this->view->SetVar('values', $values);
                return;
            }
            
            list($type, $link) = $this->PasteWriter->Save($values);
            
            header('Location: ' . $this->base . 'view/' . $type . '/' . $link);
        }
    }
?>
