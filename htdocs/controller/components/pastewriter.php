<?php
    
    // Verify and create new pastes. Because both the newpaste and api class
    // Share largely the same code it is absracted in this class
    class PasteWriter
    {
        private $pastemodel;
        private $cfgmodel;

        function __construct(&$pastemodel, &$cfgmodel)
        {
            $this->pastemodel =& $pastemodel;
            $this->cfgmodel =& $cfgmodel;
        }
        
        // 0 means no, 1 means a little (show captcha), 2 means fuck yes (tell people to fuck off)!
        public function IsSpammer()
        {
            $count = $this->pastemodel->CountUserPastes($_SERVER['REMOTE_ADDR'], time() - $this->cfgmodel->GetValue('SPAM_TIME'));
            if($count < $this->cfgmodel->GetValue('SPAM_WARN'))
                return 0;
            elseif($count < $this->cfgmodel->GetValue('SPAM_FINAL'))
                return 1;
            else
                return 2;
        }

        public static function VerifyPassphrase($value)
        {
            if(strlen($value) == 0 && $_POST['state'] == 2)
                return false;
            else
                return true;
        }
        
        // Verifies the POST data and returns values & errors
        public function VerifyData()
        {
            $errors = null;
            $values = array();

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
            $form->AddField('passphrase', Form::VTYPE_FUNCTION, 'PasteWriter::VerifyPassphrase',
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
                $errors = $form->GetErrors();
            
            // Restructure individual files values into an array
            $values = $form->GetValues();
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

            return array($values, $errors);
        }

        private function MakePasteData(&$values)
        {
            $header = array('title'         =>  $values['title'],
                            'author'        =>  $values['author'],
                            'state'         =>  $values['state'],
                            'expiration'    =>  $values['expiration'],
                            'ip'            =>  $_SERVER['REMOTE_ADDR']);

            $files = array();
            for ($i = 0; $i < count($values['contents']); $i++)
            {
                if(strlen($values['contents'][$i]))
                {
                    $files[] = array('filename' => $values['filename'][$i],
                                     'lang'     => (int)$values['lang'][$i],
                                     'contents' => $values['contents'][$i]);
                }
            }
            return array($header, $files);
        }
        
        public function Save(&$values)
        {
            if ($values['state'] == 2)
                return $this->SaveCryptPaste($values);
            else
                return $this->SaveClearPaste($values);
        }
        
        private function SaveClearPaste(&$values)
        {
            list($header, $files) = $this->MakePasteData($values);
            $link = $this->pastemodel->NewClearPaste($header, $files);

            if($values['state'] == 0)
                return array('pub', $link);
            else
                return array('pri', $link);
        }

        private function SaveCryptPaste(&$values)
        {
            list($header, $files) = $this->MakePasteData($values);

            // Create the data array and encode it
            $data = array('title'  => $header['title'],
                          'author' => $header['author'],
                          'files'  => $files);
            $jdata = json_encode($data);
            
            // Create keys
            $f = fopen('/dev/urandom', 'r');
            if($f === false)
                throw new FramelessException('Internal error', ErrorCodes::E_RUNTIME);
            $aessalt = fread($f, 32);
            $hmacsalt = fread($f, 32);
            $iv = fread($f, 16);
            fclose($f );
            
            $aeskey  = PBKDF2::GetKey('hmac-sha256', $values['passphrase'],  $aessalt, 4096, 32);
            $hmackey = PBKDF2::GetKey('hmac-sha256', $values['passphrase'], $hmacsalt, 4096, 32);
            
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
            return array('enc', $link);
        }
    }
?>
