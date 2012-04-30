<?php

    class ViewPaste extends BaseController
    {
        private $view;
        private $model;
        private $PasteReader;

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
            
            // Derp model
            $this->model = new PasteModel($this->config['database']['host'],
                                          $this->config['database']['user'],
                                          $this->config['database']['pass'],
                                          $this->config['database']['db']);
            
            $this->PasteReader = new PasteReader($this->model);
            
            // Set actions we handle
            $this->actions = array('default'    => 'all',
                                   'list'       => 'all',
                                   'pub'        => 'pub',
                                   'pri'        => 'priv',
                                   'enc'        => 'encrypted',
                                   'dec'        => 'decrypted');
        }

        // View all pastes
        public function all()
        {
            $this->model->ExpirePastes();
            $pagenum = isset($this->args[2]) ? (int)$this->args[2] : 0;
            $pagination = new Pagination($this->model->CountPublicPastes(), 50, $pagenum, $this->base . 'view/list/{page}/');
            $pastes = $this->model->ListPublicPastes($pagination->GetLimits());
            foreach ($pastes as &$paste)
            {
                $paste['author'] = htmlspecialchars($paste['author']);
                // Forgot wtf the preg_match is supposed to achieve, remove it after I decide its not vital
                if(strlen($paste['title']) && preg_match('/[^ \t\v]/', $paste['title']))
                    $paste['title'] = htmlspecialchars($paste['title']);
                else
                    $paste['title'] = $paste['link'];
            }
            $this->view->SetTemplate('viewpaste-list.tpl');
            $pagelist = $pagination->GetList();
            if(count($pagelist) == 1)
                $pagelist = array();
            $this->view->SetVar('pagination', $pagelist);
            $this->view->SetVar('title', 'Public Pastes');
            $this->view->SetVar('pastes', $pastes);
            $this->view->Draw();
        }

        private function clearview($state)
        {
            $pastelink = isset($this->args[2]) ? $this->args[2] : '';
            $data = $this->PasteReader->ReadPlaintext($pastelink, $state);
            if($data === false)
                throw new FramelessException('', ErrorCodes::E_404);
            $header = $data['header'];
            $files  = $data['files'];
            
            $this->view->SetTemplate('viewpaste.tpl');
            if(strlen($header['title']) && preg_match('/[^ \t\v]/', $header['title']))
                $this->view->SetVar('title', $header['title']);
            else
                $this->view->SetVar('title', 'Untitled paste');
            $this->view->SetVar('header', $header);
            $this->view->SetVar('files', $files);
            $this->view->Draw();
        }


        public function priv()
        {
            $this->clearview(1);
        }

        public function pub()
        {
            $this->clearview(0);
        }

        public function encrypted()
        {
            // TODO: Verify if args is valid paste id
            $this->model->ExpirePastes();
            $this->view->SetTemplate('decryptpaste.tpl');
            $this->view->SetVar('title', 'Encrypted Paste');
            $pastelink = isset($this->args[2]) ? $this->args[2] : '';
            $this->view->SetVar('pastelink', $pastelink);
            $this->view->Draw();
        }

        public function decrypted()
        {
            $passphrase = isset($_POST['passphrase']) ? $_POST['passphrase'] : '';
            $pastelink = isset($this->args[2]) ? $this->args[2] : '';
            $data = $this->PasteReader->ReadCiphertext($pastelink, $passphrase);
            if($data === false)
            {
                $this->view->SetVar('error', 'Incorrect passphrase');
                $this->encrypted();
                return;
            }
            $header = $data['header'];
            $files  = $data['files'];
            
            $this->view->SetTemplate('viewpaste.tpl');
            if(strlen($header['title']) && preg_match('/[^ \t\v]/', $header['title']))
                $this->view->SetVar('title', $header['title']);
            else
                $this->view->SetVar('title', 'Untitled paste');
            $this->view->SetVar('header', $header);
            $this->view->SetVar('files', $files);
            $this->view->Draw();
        }
    }
?>
