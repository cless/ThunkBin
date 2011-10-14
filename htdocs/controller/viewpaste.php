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
            $this->base .= '://' . $_SERVER['HTTP_HOST'] . $config->GetVector('thunkbin')->AsString('basedir');
            
            // Create view
            $this->view = new SmartyView;
            $this->view->SetVar('base', $this->base);
            
            // Derp model
            $this->model = new PasteModel($this->config->GetVector('database')->AsString('host'),
                                          $this->config->GetVector('database')->AsString('user'),
                                          $this->config->GetVector('database')->AsString('pass'),
                                          $this->config->GetVector('database')->AsString('db'));
            
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
            $pagination = new Pagination($this->model->CountPublicPastes(), 50, $this->args->AsInt(2), $this->base . 'view/list/{page}/');
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
            $data = $this->model->ReadClearPaste($this->args->AsString(2), $state);

            // Prepare header
            $header =& $data[0];
            $header['author'] = htmlspecialchars($header['author']);
            $header['title'] = htmlspecialchars($header['title']);
            if($state == 0)
                $header['link'] = $this->base . 'view/pub/' . htmlspecialchars($this->get->AsDefault('args'));
            else
                $header['link'] = $this->base . 'view/pri/' . htmlspecialchars($this->get->AsDefault('args'));
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
            $this->view->SetVar('pastelink', $this->args->AsString(2));
            $this->view->Draw();
        }

        public function decrypted()
        {
            // TODO verify form, maybe
            $this->model->ExpirePastes();
            $data = $this->model->ReadCryptPaste($this->args->AsString(2));

            // Create array we can use to translate id -> langname
            $langs = $this->model->GetLanguages();
            foreach ($langs as $lang)
                $langids[(int)$lang['id']] = $lang['name'];
            
            // Recreate keys
            $aeskey  = PBKDF2::GetKey('hmac-sha256', $this->post->AsString('passphrase'), substr($data['salts'], 0, 32), 4096, 32);
            $hmackey = PBKDF2::GetKey('hmac-sha256', $this->post->AsString('passphrase'), substr($data['salts'], 32), 4096, 32);            
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
            $header['link'] = $this->base . 'view/enc/' . htmlspecialchars($this->get->AsDefault('args'));
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
