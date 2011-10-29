<?php

    class ViewPaste extends BaseController
    {
        private $view;
        private $model;

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
            $this->model->ExpirePastes();
            $source = new SourceFormat;
            $pastelink = isset($this->args[2]) ? $this->args[2] : '';
            $data = $this->model->ReadClearPaste($pastelink, $state);
            if($data === false)
                throw new FramelessException('', ErrorCodes::E_404);
            
            // Prepare header
            $header =& $data[0];
            $header['author'] = htmlspecialchars($header['author']);
            $header['title'] = htmlspecialchars($header['title']);
            if($state == 0)
                $header['link'] = $this->base . 'view/pub/' . htmlspecialchars($pastelink);
            else
                $header['link'] = $this->base . 'view/pri/' . htmlspecialchars($pastelink);
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
                $file['contents'] = $source->Render($file['contents'], $file['langid']);
            }
            
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
            // TODO verify form, maybe
            $this->model->ExpirePastes();
            $pastelink = isset($this->args[2]) ? $this->args[2] : '';
            $data = $this->model->ReadCryptPaste($pastelink);
            if($data === false)
                throw new FramelessException('', ErrorCodes::E_404);

            // Create array we can use to translate id -> langname
            $langs = $this->model->GetLanguages();
            foreach ($langs as $lang)
                $langids[(int)$lang['id']] = $lang['name'];
            
            // Recreate keys
            $passphrase = isset($_POST['passphrase']) ? $_POST['passphrase'] : '';
            $aeskey  = PBKDF2::GetKey('hmac-sha256', $passphrase, substr($data['salts'], 0, 32), 4096, 32);
            $hmackey = PBKDF2::GetKey('hmac-sha256', $passphrase, substr($data['salts'], 32), 4096, 32);

            // Verify the hmac
            if(hash_hmac('sha256', $data['contents'], $hmackey, true) !== $data['hmac'])
            {
                $this->view->SetVar('error', 'Incorrect passphrase');
                $this->encrypted();
                return;
            }

            // decrypt the data
            $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
            mcrypt_generic_init($td, $aeskey, $data['iv']);
            $jdata = mdecrypt_generic($td, $data['contents']);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            
            $decrypted = json_decode(rtrim($jdata, "\0"), true);
            $source = new SourceFormat;
            
            // Set header
            $header['author'] = htmlspecialchars($decrypted['author']);
            $header['title'] = htmlspecialchars($decrypted['title']);
            $header['link'] = $this->base . 'view/enc/' . htmlspecialchars($pastelink);
            $header['created'] = date('Y-m-d H:i:s', $data['created']);
            if($data['expires'])
                $header['expires'] = date('Y-m-d H:i:s', $data['expires']);
            
            $files =& $decrypted['files'];
            foreach($files as &$file)
            {
                $file['filename'] = htmlspecialchars($file['filename']);
                $file['contents'] = $source->Render($file['contents'], $file['lang']);
                $file['lang'] = $langids[$file['lang']]; 
            }
            
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
