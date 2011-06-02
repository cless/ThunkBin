<?php

    class ViewPaste extends BaseController
    {
        private $view;
        private $model;

        public function __construct(&$config)
        {
            parent::__construct($config);

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

        // should become list but this is a Frameless limitation, upstream will fix it soonish
        public function all()
        {
            $this->model->ExpirePastes();
            $pastes = $this->model->ListPublicPastes(50);
            foreach ($pastes as &$paste)
            {
                $paste['author'] = htmlspecialchars($paste['author']);
                if(strlen($paste['title']) && preg_match('/[^ \t\v]/', $paste['title']))
                    $paste['title'] = htmlspecialchars($paste['title']);
                else
                    $paste['title'] = $paste['link'];
            }
            $this->view->SetTemplate('viewpaste-list.tpl');
            $this->view->SetVar('title', 'Public Pastes');
            $this->view->SetVar('pastes', $pastes);
            $this->view->Draw();
        }

        private function clearview($state)
        {
            $this->model->ExpirePastes();
            $source = new SourceFormat;
            $data = $this->model->ReadClearPaste($this->get->AsDefault('args'), $state);

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
            $this->view->SetVar('pastelink', $this->get->AsString('args'));
            $this->view->Draw();
        }

        public function decrypted()
        {
            // TODO verify form, maybe
            $this->model->ExpirePastes();
            $data = $this->model->ReadCryptPaste($this->get->AsString('args'));

            // Create array we can use to translate id -> langname
            $langs = $this->model->GetLanguages();
            foreach ($langs as $lang)
                $langids[(int)$lang['id']] = $lang['name'];

            // decrypt the data
            $td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
            mcrypt_generic_init($td, $this->post->AsString('passphrase'), $data['iv']);
            $jdata = mdecrypt_generic($td, $data['contents']);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            
            if(substr($jdata, 0, 4) != 'TBIN')
            {
                $this->view->SetVar('error', 'Incorrect passphrase');
                $this->encrypted();
                return;
            }
            
            $decrypted = json_decode(rtrim(substr($jdata, 4), "\0"), true);
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
