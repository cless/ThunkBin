<?php

    class NewPaste extends BaseController
    {
        private $view;
        private $model;
        private $base;

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
                $this->Create();
                return;
            }

            if ($_POST['state'] == 2)
                $this->SaveCryptPaste();
            else
                $this->SaveClearPaste();
        }

        // Encrypt paste and pass it into the DB
        private function SaveCryptPaste()
        {
            // Derp files, this is redundant code I'll make it prettier when it works
            $files = array();
            for ($i = 0; $i < $this->cfgmodel->GetValue('MAX_FILES') && strlen($_POST['contents' . $i]); $i++)
            {
                $files[] = array('filename' => $_POST['filename' . $i],
                                 'lang'     => (int)$_POST['lang' . $i],
                                 'contents' => $_POST['contents' . $i]);
            }

            // Create the data array and encode it
            $data = array('title'  => $_POST['title'],
                          'author' => $_POST['author'],
                          'files'  => $files);
            $jdata = json_encode($data);
            
            // Create keys
            $f = fopen('/dev/urandom', 'r');
            if($f === false)
                throw new FramelessException('Internal error', ErrorCodes::E_RUNTIME);
            $aessalt = fread($f, 32);
            $hmacsalt = fread($f, 32);
            $iv = fread($f, 16);
            fclose($f);
            
            $aeskey  = PBKDF2::GetKey('hmac-sha256', $_POST['passphrase'], $aessalt, 4096, 32);
            $hmackey = PBKDF2::GetKey('hmac-sha256', $_POST['passphrase'], $hmacsalt, 4096, 32);
            
            // Encrypt the with AES-256 bit
            $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
            if($aeskey === false || $hmackey === false || $td === false)
                throw new FramelessException('Internal error', ErrorCodes::E_RUNTIME);
            $ret = mcrypt_generic_init($td, $aeskey, $iv);
            if($ret < 0 || $ret === false)
                throw new FramelessException('Internal error', ErrorCodes::E_RUNTIME);
            $crypted = mcrypt_generic($td, $jdata);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            // Authenticate the ciphertext
            $hmac = hash_hmac('sha256', $crypted, $hmackey, true);
            $link = $this->pastemodel->NewCryptPaste((int)$_POST['expiration'],
                                                     $iv,
                                                     $aessalt . $hmacsalt,
                                                     $hmac,
                                                     $crypted,
                                                     $_SERVER['REMOTE_ADDR']);
            
            // More redundant code
            header('Location: ' . $this->base . 'view/enc/' . $link);
        }

        // Pass clearpaste into te db
        private function SaveClearPaste()
        {
            // Create header and files ino
            $header = array('title'         =>  $_POST['title'],
                            'author'        =>  $_POST['author'],
                            'state'         =>  $_POST['state'],
                            'expiration'    =>  $_POST['expiration'],
                            'ip'            =>  $_SERVER['REMOTE_ADDR']);
            $files = array();
            for ($i = 0; $i < $this->cfgmodel->GetValue('MAX_FILES') && strlen($_POST['contents' . $i]); $i++)
            {
                $files[] = array('filename' => $_POST['filename' . $i],
                                 'lang'     => (int)$_POST['lang' . $i],
                                 'contents' => $_POST['contents' . $i]);
            }
            
            $link = $this->pastemodel->NewClearPaste($header, $files);
            if((int)$_POST['state'] == 0)
                header('Location: ' . $this->base . 'view/pub/' . $link);
            elseif((int)$_POST['state'] == 1)
                header('Location: ' . $this->base . 'view/pri/' . $link);
        }

        // Verifies the form
        private function VerifyForm()
        {
            // Describe all header fields
            $form = new Form;
            $form->AddField('title', Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_ANY, 128),
                            'Title is limited to 128 characters.');
            $form->AddField('author', Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_ANY, 20),
                            'Author is limited to 20 characters.');
            $form->AddField('state', Form::VTYPE_ARRAY, array(0, 1, 2),
                            'You selected an invalid state (wut, haxxor!)');
            $form->AddField('expiration', Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_NUMBERS, 7),
                            'Invalid Expiraton time');
            $form->AddField('passphrase', Form::VTYPE_FUNCTION, 'NewPaste::VerifyPassphrase',
                            'You must specify a passphrase when creating an encrypted paste.');
            
            // Describe all file fields
            for($i = 0; $i < $this->cfgmodel->GetValue('MAX_FILES'); $i++)
            {
                $form->AddField('filename' . $i, Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_ANY, 64),
                                'Filename is limited to 64 characters.');
                $form->AddField('lang'     . $i, Form::VTYPE_ARRAY, $this->pastemodel->GetLanguageIds());
                if($i == 0) // First file is mandatory
                    $form->AddField('contents' . $i, Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_ANY, 0, 1),
                                    'The first file must have contents.');
                else
                    $form->AddField('contents' . $i, Form::VTYPE_EXISTS);
            }
            
            // Verify the form and return accordingly
            if(!$form->Verify())
            {
                $this->view->SetVar('errors', $form->GetErrors());
                $values = $form->GetValues();

                // Restructure individual files values to make them accessible in smarty
                $max = $this->cfgmodel->GetValue('MAX_FILES');
                for ($i = 0; $i < $max; $i++)
                {
                    if (isset($values['contents' . $i]))
                        $values['contents'][$i] = $values['contents' . $i];
                    else
                        $values['contents'][$i] = '';

                    if (isset($values['filename' . $i]))
                        $values['filename'][$i] = $values['filename' . $i];
                    else
                        $values['filename'][$i] = '';
                    
                    if (isset($values['lang' . $i]))
                        $values['lang'][$i] = $values['lang' . $i];
                    else
                        $values['lang'][$i] = '';
                }

                $this->view->SetVar('values', $values);
                return false;
            }

            return true;
        }

        public static function VerifyPassphrase($value)
        {
            if(strlen($value) == 0 && $_POST['state'] == 2)
                return false;
            else
                return true;
        }
    }
?>
