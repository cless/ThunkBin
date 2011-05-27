<?php

    class ViewPaste extends BaseController
    {
        private $view;
        private $model;

        public function __construct(&$config)
        {
            parent::__construct($config);
            $this->view = new SmartyView;
            
            $this->model = new PasteModel($this->config->AsString('mysqlhost'),
                                          $this->config->AsString('mysqluser'),
                                          $this->config->AsString('mysqlpass'),
                                          $this->config->AsString('mysqldb'));
        }
        
        public function DefaultAction()
        {
            return 'all';
        }

        // should become list but this is a Frameless limitation, upstream will fix it soonish
        public function all()
        {
            $pastes = $this->model->ListPublicPastes(10);
            foreach ($pastes as &$paste)
            {
                $paste['author'] = htmlspecialchars($paste['author']);
                $paste['title'] = htmlspecialchars($paste['title']);
            }
            $this->view->SetTemplate('viewpaste-list.tpl');
            $this->view->SetVar('pastes', $pastes);
            $this->view->Draw();
        }

        private function clearview($state)
        {
            $source = new SourceFormat;
            $data = $this->model->ReadClearPaste($this->get->AsDefault('args'), $state);

            // Prepare header
            $header =& $data[0];
            $header['author'] = htmlspecialchars($header['author']);
            $header['title'] = htmlspecialchars($header['title']);
            $header['created'] = date('Y-m-d H:i:s', $header['created']);
            if($header['expires'])
                $header['expires'] = date('Y-m-d H:i:s', $header['expires']);
            else
                unset($header['expires']);

            // Prepare files
            $files =& $data[1];
            foreach($files as &$file)
            {
                $file['filename'] = htmlspecialchars($file['filename']);
                $file['contents'] = $source->Render($file['contents']);
            }
            
            $this->view->SetTemplate('viewpaste.tpl');
            $this->view->SetVar('title', $header['title']);
            $this->view->SetVar('header', $header);
            $this->view->SetVar('files', $files);
            $this->view->Draw();
        }


        // Should become private
        public function priv()
        {
            $this->clearview(1);
        }

        // should become public
        public function pub()
        {
            $this->clearview(0);
        }

        public function encrypted()
        {

        }
    }
