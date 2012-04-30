<?php
    // Verify and create new pastes. Because both the newpaste and api class
    // Share largely the same code it is absracted in this class
    class PasteReader
    {
        private $pastemodel;

        function __construct(&$pastemodel)
        {
            $this->model =& $pastemodel;
        }

        public function ReadCiphertext($pastelink, $passphrase)
        {
            $this->model->ExpirePastes();
            $data = $this->model->ReadCryptPaste($pastelink);
            if($data === false)
                return false;
            
            $langs = $this->model->GetLanguages();
            foreach ($langs as $lang)
                $langids[(int)$lang['id']] = $lang['name'];
            
            $aeskey  = PBKDF2::GetKey('hmac-sha256', $passphrase, substr($data['salts'], 0, 32), 4096, 32);
            $hmackey = PBKDF2::GetKey('hmac-sha256', $passphrase, substr($data['salts'], 32), 4096, 32);

            if(hash_hmac('sha256', $data['contents'], $hmackey, true) !== $data['hmac'])
                return false;

            $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
            mcrypt_generic_init($td, $aeskey, $data['iv']);
            $jdata = mdecrypt_generic($td, $data['contents']);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            
            $decrypted = json_decode(rtrim($jdata, "\0"), true);
            $source = new SourceFormat;
            
            $header['author'] = htmlspecialchars($decrypted['author']);
            $header['title'] = htmlspecialchars($decrypted['title']);
            $header['link'] = 'view/enc/' . htmlspecialchars($pastelink);
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
            
            return array('header' => $header, 'files' => $files);
        }
        
        public function ReadPlaintext($pastelink, $state)
        {
            $this->model->ExpirePastes();
            $source = new SourceFormat;
            $data = $this->model->ReadClearPaste($pastelink, $state);
            if($data === false)
                return false;

            $header =& $data[0];
            $header['author'] = htmlspecialchars($header['author']);
            $header['title'] = htmlspecialchars($header['title']);
            if($state == 0)
                $header['link'] = 'view/pub/' . htmlspecialchars($pastelink);
            else
                $header['link'] = 'view/pri/' . htmlspecialchars($pastelink);
            $header['created'] = date('Y-m-d H:i:s', $header['created']);
            if($header['expires'])
                $header['expires'] = date('Y-m-d H:i:s', $header['expires']);
            else
                unset($header['expires']);

            $files =& $data[1];
            foreach($files as &$file)
            {
                $file['filename'] = htmlspecialchars($file['filename']);
                $file['contents'] = $source->Render($file['contents'], $file['langid']);
            }
            
            return array('header' => $header, 'files' => $files);
        }
    };
?>
