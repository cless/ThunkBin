<?php

    class NewPaste extends BaseController
    {
        private $view;
        private $model;
        private $base;

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
            $this->view->SetTemplate('newpaste.tpl');
            $this->view->SetVar('base', $this->base);
            
            // Derp model
            $this->pastemodel = new PasteModel($this->config->GetVector('database')->AsString('host'),
                                               $this->config->GetVector('database')->AsString('user'),
                                               $this->config->GetVector('database')->AsString('pass'),
                                               $this->config->GetVector('database')->AsString('db'));
            
            $this->cfgmodel = new ConfigModel($this->config->GetVector('database')->AsString('host'),
                                              $this->config->GetVector('database')->AsString('user'),
                                              $this->config->GetVector('database')->AsString('pass'),
                                              $this->config->GetVector('database')->AsString('db'));


            // Set all actions we handle
            $this->actions = array('default' => 'create',
                                   'create'  => 'create',
                                   'save'    => 'save');
        }
        
        // 0 means no, 1 means a little (show captcha), 2 means fuck yes (tell people to fuck off)!
        private function IsSpammer()
        {
            $count = $this->pastemodel->CountUserPastes($_SERVER['REMOTE_ADDR'], time() - $this->cfgmodel->GetValue('SPAM_TIME'));
            if($count < $this->cfgmodel->GetValue('SPAM_WARN'))
                return 0;
            elseif($count < $this->cfgmodel->GetValue('SPAM_FINAL'))
                return 1;
            else
                return 2;
        }

        public function Create()
        {
            if($this->IsSpammer())
                $this->view->SetVar('spam', true);
            $this->view->SetVar('title', 'Create New Paste');
            $this->view->SetVar('maxfiles', $this->cfgmodel->GetValue('MAX_FILES'));
            $this->view->SetVar('languages', $this->pastemodel->GetLanguages());
            $this->view->Draw();
        }

        public function Save()
        {
            if($this->IsSpammer())
            {
                $this->view->SetVar('spam', true);
                $this->view->Draw();
                return;
            }

            // Verify and possibly die with error
            if (!$this->VerifyForm())
            {
                $this->view->Draw();
                return;
            }

            if ($this->post->AsInt('state') == 2)
                $this->SaveCryptPaste();
            else
                $this->SaveClearPaste();
        }

        // Encrypt paste and pass it into the DB
        private function SaveCryptPaste()
        {
            // Derp files, this is redundant code I'll make it prettier when it works
            $files = array();
            for ($i = 0; $i < $this->cfgmodel->GetValue('MAX_FILES') && strlen($this->post->AsString('contents' . $i)); $i++)
            {
                $files[] = array('filename' => $this->post->AsDefault('filename' . $i),
                                 'lang'     => $this->post->AsInt('lang' . $i),
                                 'contents' => $this->post->AsDefault('contents' . $i));
            }

            // Create the data array and encode it
            $data = array('title'  => $this->post->AsDefault('title'),
                          'author' => $this->post->AsDefault('author'),
                          'files'  => $files);
            $jdata = 'TBIN' . json_encode($data);

            // Encrypt the data
            $td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
            mcrypt_generic_init($td, $this->post->AsString('passphrase'), $iv);
            $crypted = mcrypt_generic($td, $jdata);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            $link = $this->pastemodel->NewCryptPaste($this->post->AsInt('expiration'), $iv, $crypted, $_SERVER['REMOTE_ADDR']);
            
            // More redundant code
            $base = $this->config->GetVector('thunkbin')->AsString('basedir');
            header('Location: ' . $base . 'view/enc/' . $link);
        }

        // Pass clearpaste into te db
        private function SaveClearPaste()
        {
            // Create header and files ino
            $header = array('title'         =>  $this->post->AsDefault('title'),
                            'author'        =>  $this->post->AsDefault('author'),
                            'state'         =>  $this->post->AsInt('state'),
                            'expiration'    =>  $this->post->AsInt('expiration'),
                            'ip'            =>  $_SERVER['REMOTE_ADDR']);
            $files = array();
            for ($i = 0; $i < $this->cfgmodel->GetValue('MAX_FILES') && strlen($this->post->AsString('contents' . $i)); $i++)
            {
                $files[] = array('filename' => $this->post->AsDefault('filename' . $i),
                                 'lang'     => $this->post->AsInt('lang' . $i),
                                 'contents' => $this->post->AsDefault('contents' . $i));
            }
            
            $link = $this->pastemodel->NewClearPaste($header, $files);
            
            $base = $this->config->GetVector('thunkbin')->AsString('basedir');
            if($this->post->AsInt('state') == 0)
                header('Location: ' . $base . 'view/pub/' . $link);
            elseif($this->post->AsInt('state') == 1)
                header('Location: ' . $base . 'view/pri/' . $link);
        }

        // Verifies the form
        private function VerifyForm()
        {
            // Describe all header fields
            $form = new Form;
            $form->AddField('title', Form::CreateVerification(Form::CHARSET_ANY, 128), 'Title is limited to 128 characters.');
            $form->AddField('Author', Form::CreateVerification(Form::CHARSET_ANY, 20), 'Author is limited to 20 characters.');
            $form->AddField('state', array(0, 1, 2), 'You selected an invalid state (wut, haxxor!)');
            $form->AddField('expiration', Form::CreateVerification(Form::CHARSET_NUMBERS, 7), 'Invalid Expiraton time');
            
            // Describe all file fields
            for($i = 0; $i < $this->cfgmodel->GetValue('MAX_FILES'); $i++)
            {
                $form->AddField('filename' . $i, Form::CreateVerification(Form::CHARSET_ANY, 64), 'Filename is limited to 64 characters.');
                $form->AddField('lang'     . $i, $this->pastemodel->GetLanguageIds());
                if($i == 0) // First file is mandatory
                    $form->AddField('contents' . $i, Form::CreateVerification(Form::CHARSET_ANY, 0, 1), 'The first file must have contents.');
                else
                    $form->AddField('contents' . $i, false);
            }
            
            // Verify the form and return accordingly
            if(!$form->Verify())
            {
                echo '<pre>';
                var_dump($form->GetErrors());
                var_dump($_POST);
                echo '</pre>';
                $this->view->SetVar('error', 'Invalid paste');
                return false;
            }

            // Frameless form verification is too limited to verify this at the moment
            if($this->post->AsInt('state') == 2 && !strlen($this->post->AsString('passphrase')))
            {
                $this->view->SetVar('error', 'Missing passphrase');
                return false;
            }

            return true;
        }
    }
?>
