<?php

    // TODO:
    // Currently the admin login and password are simply saved as plaintext in the config database
    // Eventually a more sophisticated login system that integrates with ThunkBin useraccounts should be
    // Developed.

    class Admin extends BaseController
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
            $this->view->SetTemplate('admin.tpl');
            
            $this->cfgmodel = new ConfigModel($this->config['database']['host'],
                                              $this->config['database']['user'],
                                              $this->config['database']['pass'],
                                              $this->config['database']['db']);
            
            // Set actions we handle
            $this->actions = array('default'    => 'login',
                                   'login'      => 'login',
                                   'settings'   => 'settings');
        }

        public function login()
        {
            // Check if already logged in
            if(isset($_SESSION['admin']) && $_SESSION['admin'] === 1)
            {
                header('Location: ' . $this->base . 'admin/settings/');
                return;
            }

            // Describe the form
            $form = new Form;
            $form->TokenName('token');
            $form->AddField('token', Form::VTYPE_TOKEN, NULL, 'invalid security token');
            $form->AddField('username', Form::VTYPE_ARRAY, array($this->cfgmodel->GetValue('ADMIN_USERNAME')),
                            'invalid username or password');
            $form->AddField('password', Form::VTYPE_CRYPTHASH, $this->cfgmodel->GetValue('ADMIN_PASSWORD'),
                            'invalid username or password');
            $form->AddField('submit');
           
            if($form->VerifyField('submit'))
            {
                if($form->Verify())
                {
                    $_SESSION['admin'] = 1; 
                    header('Location: ' . $this->base . 'admin/settings/');
                    return;
                }

                // Failed login:
                $errors = $form->GetErrors();
                if(isset($errors['username']) && isset($errors['password']))
                    unset($errors['username']);
                $this->view->SetVar('errors', $errors);
            }
            
            $this->view->SetVar('token', $form->CreateToken());
            $this->view->SetVar('title', 'ThunkBin Admin Login');
            $this->view->SetVar('login', true);
            $this->view->Draw(); 
        }

        public function settings()
        {
            // Verify if logged in
            if(isset($_SESSION['admin']) && $_SESSION['admin'] !== 1)
            {
                header('Location: ' . $this->base . 'admin/');
                return;
            }
            
            $form = new Form;
            $form->TokenName('token');
            $form->AddField('token', Form::VTYPE_TOKEN, NULL, 'Invalid security token');
            $form->AddField('logout');
            if($form->VerifyField('logout'))
            {
                if($form->Verify())
                {
                    unset($_SESSION['admin']);
                    header('Location: ' . $this->base . 'admin/');
                    return;
                }
                else
                {
                    $this->view->SetVar('errors', $form->GetErrors());
                }
            }

            $form = new Form;
            $form->TokenName('token');
            $form->AddField('token', Form::VTYPE_TOKEN, NULL, 'Invalid security token');
            $form->AddField('username', Form::VTYPE_REGEX,
                            Form::CreateVerification(Form::CHARSET_ALPHA | Form::CHARSET_BIGALPHA, 255, 1),
                            'a-Z only, minimum 1 character, maximum 255');
            $form->AddField('password', Form::VTYPE_FUNCTION, 'admin::VerifyPassword', 'minimum 1 character, maximum 255');
            $form->AddField('password2', Form::VTYPE_EQUAL, 'password', 'Must be the same as password');
            $form->AddField('updatepass', Form::VTYPE_NONE);
            $form->AddField('maxfiles', Form::VTYPE_ARRAY, range('1', '10'), 'Must be between 1 and 10');
            $form->AddField('spamtime', Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_NUMBERS, 5, 1),
                            'Numbers only, maximum 99999');
            $form->AddField('spamwarn', Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_NUMBERS, 2, 1),
                            'Numbers only, maximum 99');
            $form->AddField('spamfinal', Form::VTYPE_REGEX, Form::CreateVerification(Form::CHARSET_NUMBERS, 2, 1),
                            'Numbers only, maximum 99');
            $form->AddField('submit');
            
            // Verify the submitted form (if submitted)
            if($form->VerifyField('submit'))
            {
                if($form->Verify())
                {
                    $values = $form->GetValues();
                    if(isset($values['updatepass']))
                    {
                        $hash = CryptHash::Create($values['password']);
                        if($hash === false)
                            throw new FramelessException('Error updating the password (hash failed)', ErrorCodes::E_RUNTIME);
                        $this->cfgmodel->SetValue('ADMIN_USERNAME', $values['username']);
                        $this->cfgmodel->SetValue('ADMIN_PASSWORD', $hash);
                    }
                    $this->cfgmodel->SetValue('MAX_FILES', $values['maxfiles']);
                    $this->cfgmodel->SetValue('SPAM_TIME', $values['spamtime']);
                    $this->cfgmodel->SetValue('SPAM_WARN', $values['spamwarn']);
                    $this->cfgmodel->SetValue('SPAM_FINAL', $values['spamfinal']);
                    
                    $this->view->SetVar('success', true);
                }
                else
                {
                    $this->view->SetVar('errors', $form->GetErrors());
                }
            }
            
            //Set default values
            $values = array('username'  => $this->cfgmodel->GetValue('ADMIN_USERNAME'),
                            'password'  => '',
                            'password2'  => '',
                            'maxfiles'  => $this->cfgmodel->GetValue('MAX_FILES'),
                            'spamtime'  => $this->cfgmodel->GetValue('SPAM_TIME'),
                            'spamwarn'  => $this->cfgmodel->GetValue('SPAM_WARN'),
                            'spamfinal' => $this->cfgmodel->GetValue('SPAM_FINAL'));
            $this->view->SetVar('token', $form->CreateToken());
            $this->view->SetVar('values', $values);
            $this->view->SetVar('settings', true);
            $this->view->SetVar('title', 'ThunkBin Admin Panel');
            $this->view->Draw(); 
        }

        // Only verify the password if we have to update it
        public static function VerifyPassword($value)
        {
            if(isset($_POST['updatepass']))
                return preg_match(Form::CreateVerification(Form::CHARSET_ANY, 255, 1), $value) === 1 ? true : false;
            else
                return true;
        }
    }
?>
