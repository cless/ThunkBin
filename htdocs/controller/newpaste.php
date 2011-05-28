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
            $this->model = new PasteModel($this->config->GetVector('database')->AsString('host'),
                                          $this->config->GetVector('database')->AsString('user'),
                                          $this->config->GetVector('database')->AsString('pass'),
                                          $this->config->GetVector('database')->AsString('db'));

            // Set all actions we handle
            $this->actions = array('default' => 'create',
                                   'create'  => 'create',
                                   'save'    => 'save');
        }
        
        public function Create()
        {
            $this->view->SetVar('title', 'Create New Paste');
            $this->view->SetVar('maxfiles', $this->config->GetVector('thunkbin')->AsInt('maxfiles'));
            $this->view->SetVar('languages', $this->model->GetLanguages());

            $this->view->Draw();
        }

        public function Save()
        {
            // Verify and possibly die with error
            if (!$this->VerifyForm())
            {
                $this->view->Draw();
                return;
            }
            
            // Create header and files ino
            $header = array('title'         =>  $this->post->AsDefault('title'),
                            'author'        =>  $this->post->AsDefault('author'),
                            'state'         =>  $this->post->AsInt('state'),
                            'expiration'    =>  $this->post->AsInt('expiration'));
            $files = array();
            for ($i = 0; $i < $this->config->GetVector('thunkbin')->AsInt('maxfiles') && strlen($this->post->AsString('contents' . $i)); $i++)
            {
                $files[] = array('filename' => $this->post->AsDefault('filename' . $i),
                                 'lang'     => $this->post->AsDefault('lang' . $i),
                                 'contents' => $this->post->AsDefault('contents' . $i));
            }
            
            $link = $this->model->NewClearPaste($header, $files);
            
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
            $form->AddField('state', array(0, 1), 'You selected an invalid state (wut, haxxor!)');
            $form->AddField('expiration', Form::CreateVerification(Form::CHARSET_NUMBERS, 7), 'Invalid Expiraton time');
            
            // Describe all file fields
            for($i = 0; $i < $this->config->GetVector('thunkbin')->AsInt('maxfiles'); $i++)
            {
                $form->AddField('filename' . $i, Form::CreateVerification(Form::CHARSET_ANY, 64), 'Filename is limited to 64 characters.');
                $form->AddField('lang'     . $i, $this->model->GetLanguageIds());
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
            return true;
        }
    }
?>
