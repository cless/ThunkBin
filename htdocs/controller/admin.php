<?php

    // TODO:
    // Currently the admin login and password are simply saved as plaintext in the config database
    // Eventually a more sophisticated login system that integrates with ThunkBin useraccounts should be
    // Developed.

    class Admin extends BaseController
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
            $this->view->SetTemplate('admin.tpl');
            
            $this->cfgmodel = new ConfigModel($this->config->GetVector('database')->AsString('host'),
                                              $this->config->GetVector('database')->AsString('user'),
                                              $this->config->GetVector('database')->AsString('pass'),
                                              $this->config->GetVector('database')->AsString('db'));
            
            // Set actions we handle
            $this->actions = array('default'    => 'login',
                                   'login'      => 'login',
                                   'settings'   => 'settings');
        }

        // Creates a new token if it doesnt exist OR creates a new token
        // if the token is older than 5 minutes. Tokens are valid for 10 minutes.
        private function CreateToken()
        {
            if(!$this->session->Exists('token') || (time() - $this->session->AsInt('token-expire')) > 300)
            {
                $this->session->Set('token', 'aaaa');
                $this->session->Set('token-expire', time());
            }
            return $this->session->AsString('token');
        }
        
        // Verify if the token exists and was issued at most 10 minutes ago
        private function VerifyToken()
        {
            if($this->post->Exists('token') &&
               $this->session->Exists('token') &&
               $this->post->AsString('token') == $this->session->AsString('token') &&
               (time() - $this->session->AsInt('expire')) > 600)
            {
                return true;
            }
            
            return false;
        }

        public function login()
        {
            // Check if already logged in
            if($this->session->AsInt('admin') == 1)
            {
                $base = $this->config->GetVector('thunkbin')->AsString('basedir');
                header('Location: ' . $base . 'admin/settings/');
                return;
            }
            // Describe the form
            $form = new Form;
            $form->AddField('username', array($this->cfgmodel->GetValue('ADMIN_USERNAME')));
            $form->AddField('password', array($this->cfgmodel->GetValue('ADMIN_PASSWORD')));
            $form->AddField('submit');

            // Verify everythng, throw some errors or accept the login
            if($form->VerifyField('submit') && !$this->VerifyToken())   // invalid token
                $this->view->SetVar('error', 'Invalid form, do you have cookies enabled?');
            else if($form->VerifyField('submit') && !$form->Verify())   // invalid username
                $this->view->SetVar('error', 'Invalid username or password');
            else if($form->VerifyField('submit'))                       // fuck yeah
            {
                $this->session->Set('admin', 1); 
                $base = $this->config->GetVector('thunkbin')->AsString('basedir');
                header('Location: ' . $base . 'admin/settings/');
                return;
            }
            
            $this->view->SetVar('token', $this->CreateToken());
            $this->view->SetVar('title', 'ThunkBin Admin Login');
            $this->view->SetVar('login', true);
            $this->view->Draw(); 
        }

        public function settings()
        {
            // Verify if logged in
            if($this->session->AsInt('admin') != 1)
            {
                $base = $this->config->GetVector('thunkbin')->AsString('basedir');
                header('Location: ' . $base . 'admin/');
                return;
            }

            // Describe the form
            $form = new Form;
            $form->AddField('username', Form::CreateVerification(Form::CHARSET_ALPHA | Form::CHARSET_BIGALPHA, 255, 1), 'A-Z only, minimum 1 character, maximum 255');
            $form->AddField('password', Form::CreateVerification(Form::CHARSET_ANY, 255, 1), 'minimum 1 character, maximum 255');
            $form->AddField('maxfiles', range('1', '10'), 'Must be between 1 and 10');
            $form->AddField('spamtime', Form::CreateVerification(Form::CHARSET_NUMBERS, 5, 1), 'Numbers only, maximum 99999');
            $form->AddField('spamwarn', Form::CreateVerification(Form::CHARSET_NUMBERS, 2, 1), 'Numbers only, maximum 99');
            $form->AddField('spamfinal', Form::CreateVerification(Form::CHARSET_NUMBERS, 2, 1), 'Numbers only, maximum 99');
            $form->AddField('submit');
            
            // Verify the submitted form (if submitted)
            if($form->VerifyField('submit') && $form->Verify() && $this->VerifyToken())
            {
                $this->view->SetVar('success', true);
                $this->cfgmodel->SetValue('ADMIN_USERNAME', $this->post->AsString('username'));
                $this->cfgmodel->SetValue('ADMIN_PASSWORD', $this->post->AsString('password'));
                $this->cfgmodel->SetValue('MAX_FILES', $this->post->AsString('maxfiles'));
                $this->cfgmodel->SetValue('SPAM_TIME', $this->post->AsString('spamtime'));
                $this->cfgmodel->SetValue('SPAM_WARN', $this->post->AsString('spamwarn'));
                $this->cfgmodel->SetValue('SPAM_FINAL', $this->post->AsString('spamfinal'));
            }
            else if($form->VerifyField('submit') && !$form->Verify())
            {
                $this->view->SetVar('errors', $form->GetErrors());
            }
            else if($form->VerifyField('submit') && !$this->VerifyToken())
            {
                $this->view->SetVar('errors', array('token' => 'Invalid form, please try again'));
            }
            
            //Set default values
            $values = array('username'  => $this->cfgmodel->GetValue('ADMIN_USERNAME'),
                            'password'  => $this->cfgmodel->GetValue('ADMIN_PASSWORD'),
                            'maxfiles'  => $this->cfgmodel->GetValue('MAX_FILES'),
                            'spamtime'  => $this->cfgmodel->GetValue('SPAM_TIME'),
                            'spamwarn'  => $this->cfgmodel->GetValue('SPAM_WARN'),
                            'spamfinal' => $this->cfgmodel->GetValue('SPAM_FINAL'));
            $this->view->SetVar('token', $this->CreateToken());
            $this->view->SetVar('values', $values);
            $this->view->SetVar('settings', true);
            $this->view->SetVar('title', 'ThunkBin Admin Panel');
            $this->view->Draw(); 
        }
    }
?>
