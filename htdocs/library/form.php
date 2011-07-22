<?php
    /**
     * Form represents a form and its fields and allows you to easily verify the submitted contents.
     */
    class Form
    {
        private $fields;
        private $post;
        private $errors;
        private $tokenname;
        
        /**
         * No verification is done using this type, verification data and verfication error are ignored
         */
        const VTYPE_NONE        = 0;
        
        /**
         * The variable has to exist using this type, verification data is ignored
         */
        const VTYPE_EXISTS      = 1;
        
        /**
         * The variable has to exist using this type and in addition its value can't be empty
         */
        const VTYPE_VALUE       = 2;

        /**
         * The variable is verified against an array with valid values, verification data is an array of values
         */
        const VTYPE_ARRAY       = 3;
        
        /**
         * The variable is verified against a regular expression, verification data is a regular expression
         * and can optionally be created with the Form::CreateVerification helper function
         */
        const VTYPE_REGEX       = 4;
        
        /**
         * The variable has to be equal to another variable in the form
         * verification data is the name of the other form variable
         */
        const VTYPE_EQUAL       = 5;
        
        /**
         * The variable is verified using a user supplied function
         */
        const VTYPE_FUNCTION    = 6;

        /**
         * The variable is verified as a token, verification data is ignored. Usually this form field will be a
         * hidden field in your input type that is assigned the token value as returned by Form::CreateToken.
         * Before Form::Verify or Form::VerifyField or Form::CreateToken is called you must set a token name using
         * Form::TokenName
         */
        const VTYPE_TOKEN       = 7;

        /**
         * The variable is verified using CryptHash::Verify. Verification data is the expected password hash.
         */
        const VTYPE_CRYPTHASH   = 8;

        /**
         * All characters are contained in this set, this set can NOT be combined with other sets
         */
        const CHARSET_ANY           = 0x00;
        
        /**
         * Lower case characters a-z are contained in this set
         */
        const CHARSET_ALPHA         = 0x01;
        
        /**
         * Upper case characters A-Z are contained in this set
         */
        const CHARSET_BIGALPHA      = 0x02;
        
        /**
         * All characters are allowed
         */
        const CHARSET_NUMBERS       = 0x04;

        /**
         * Space (' ') is contained in this set
         */
        const CHARSET_SPACE         = 0x08;

        /**
         * The following punctuation characters are contained in this set: . , ? ! : ;
         */
        const CHARSET_PUNCTUATION   = 0x10;
        
        /**
         * This helper function creates a regular expression to be used as verification
         * argument for Form::AddField. You can create your own regexes just fine, this function
         * is merely there to make it easier.
         *
         * \param charset Any of the charset variables can be combined with bitwise or except
         *                CHARSET_ANY (e.g. Form::CHARSET_ALPHA | Form::CHARSET_SPACE
         * \param maxlength Maximum length of the field, if this is set to 0 then there is no
         *                  limitation. Must be larger or equal to minlength.
         * \param minlength Minimum length of the field, if this is set to 0 then there is no
         *                  limitation. Must be smaller or equal to maxlength.
         */
        static function CreateVerification($charset, $maxlength = 0, $minlength = 0)
        {
            if ($charset === self::CHARSET_ANY)
                $regexSet = '.';
            else
            {
                $regexSet = '[';
                
                if ($charset & self::CHARSET_ALPHA)
                    $regexSet .= 'a-z';
                if ($charset & self::CHARSET_BIGALPHA)
                    $regexSet .= 'A-Z';
                if ($charset & self::CHARSET_NUMBERS)
                    $regexSet .= '0-9';
                if ($charset & self::CHARSET_SPACE)
                    $regexSet .= ' ';
                if ($charset & self::CHARSET_PUNCTUATION)
                    $regexSet .= '.,!?:;';

                $regexSet .= ']';
            }
            
            if($maxlength == 0 && $minlength == 0)
                $regexLength = '*';
            elseif($maxlength == 0 && $minlength == 1)
                $regexLength = '+';
            elseif($maxlength == 0 && $minlength > 0)
                $regexLength = '{' . $minlength . ',}';
            else
                $regexLength = '{' . $minlength . ',' . $maxlength .'}';

            return '/^' . $regexSet . $regexLength . '$/s';
        }

        public function __construct()
        {
            $this->fields = array();
            $this->errors = array();
            $this->post = new Vector($_POST);
            $this->tokenname = false;
        }
        
        /**
         * Creates a security token to prevent cross site request forgery, the token is verified by Form::Verify
         * if you create a field description using Form::AddField with the type Form::VTYPE_TOKEN
         * Note that you MUST have initiated a php session for token creation and verification to work.
         *
         * \param expire Number of seconds after which the token expires
         * \param refresh Number of seconds after which the token will be refreshed
         * \return The value of the calculated token, you should assign this to a (hidden) input field in your form
         *         and verify that field with the Form::VTYPE_TOKEN
         */
        public function CreateToken($expire = 900, $refresh = 600)
        {
            // Read already existing token data for verification
            if(isset($_SESSION[$this->tokenname]))
            {
                $tokendata = explode(':', $_SESSION[$this->tokenname]);
                $tokendata[1] = $expire;
                $tokendata[2] = $refresh;
            }
            
            // Refresh token if required
            if(!isset($_SESSION[$this->tokenname]) || (time() - $tokendata[3]) > $refresh)
            {
                $tokendata = array(sha1($_SERVER['REMOTE_ADDR'] . mt_rand(10000,99999) . microtime() . time()),
                                   $expire,
                                   $refresh,
                                   time());
            }
            $_SESSION[$this->tokenname] = implode(':', $tokendata);
            return $tokendata[0];
        }

        /**
         * Adds a field description to the form, these are then used to verify if the form was submitted correctly
         *
         * \param name Name of the field that is to be verified
         * \param vtype This should be one of he VTYPE constants in the form class
         * \param vdata How this argument is handled depends on the VTYPE, see the desription of the VTYPE constants for help
         * \param error This is the error string that will be set when this field fails verification
         */
        public function AddField($name, $vtype = Form::VTYPE_EXISTS, $vdata = null, $error = 'Invalid value')
        {
            // Todo: stricter verification of vdata
            if($vtype == Form::VTYPE_ARRAY && !is_array($vdata))
                throw new FramelessException(array('Invalid Type', "Form field $name should have type array"), ErrorCodes::E_RUNTIME);
            elseif(($vtype == Form::VTYPE_REGEX ||
                    $vtype == Form::VTYPE_EQUAL ||
                    $vtype == Form::VTYPE_FUNCTION || 
                    $vtype == Form::VTYPE_CRYPTHASH) &&
                    !is_string($vdata))
                throw new FramelessException(array('Invalid Type', "Form field $name should have type string"), ErrorCodes::E_RUNTIME);

            $this->fields[$name] = array('vtype' => $vtype,
                                         'vdata' => $vdata,
                                         'error' => $error);
        }
        
        /**
         * Sets the token name for this form, the name has to be set before Form::CreateToken is called
         * If any form field is described with Form::VTYPE_TOKEN then this function must also be called
         * before Form::Verify or Form::VerifyField
         *
         * \param name Name of the token (This name is used as the name of a session variable ($_SESSION[$name]))
         */
        public function TokenName($name)
        {
            $this->tokenname = $name;
        }


        /**
         * Verify a single field known to the form. You most likely need Form::Verify instead of Form::VerifyField.
         * \param name The name of the field to verify. This field has to be described by Form::AddField before you can verify it
         * \return true when the field verification succeeds, false otherwise.
         */
        public function VerifyField($name)
        {
            if (!isset($this->fields[$name]))
                throw FramelessException(array('Form Verification Error', "Form field $name has no description"), ErrorCodes::E_RUNTIME); 

            $field =& $this->fields[$name];
            if ($field['vtype'] == Form::VTYPE_NONE)
                return true;
            
            // EXISTS
            if($field['vtype'] == Form::VTYPE_EXISTS && $this->post->Exists($name))
                return true;
            elseif(!$this->post->Exists($name))
                return false;
            
            // VALUE
            if($field['vtype'] == Form::VTYPE_VALUE && strlen($this->post->AsDefault($name)))
                return true;
            elseif($field['vtype'] == Form::VTYPE_VALUE)
                return false;
            
            // ARRAY
            if($field['vtype'] == Form::VTYPE_ARRAY && in_array($this->post->AsDefault($name), $field['vdata']))
                return true;
            else if($field['vtype'] == Form::VTYPE_ARRAY)
                return false;
            
            // REGEX
            if($field['vtype'] == Form::VTYPE_REGEX)
                return preg_match($field['vdata'], $this->post->AsDefault($name)) === 1 ? true : false;

            // EQUAL
            if($field['vtype'] == Form::VTYPE_EQUAL &&
               $this->post->Exists($field['vdata']) &&
               $this->post->AsDefault($field['vdata']) == $this->post->AsDefault($name)
              )
                return true;
            elseif($field['vtype'] == Form::VTYPE_EQUAL)
                return false;

            // FUNCTION
            if($field['vtype'] == Form::VTYPE_FUNCTION)
            {
                // Split vdata into class and function (if applicable)
                $parts = explode(':', $field['vdata']);

                if(count($parts) == 3)
                    return $parts[0]::$parts[2]($this->post->AsDefault($name));
                
                return $field['vdata']($this->post->AsDefault($name));
            }

            // TOKEN
            if($field['vtype'] == Form::VTYPE_TOKEN)
            {
                if(!isset($_SESSION[$this->tokenname]))
                    return false;

                $tokendata = explode(':', $_SESSION[$this->tokenname]);
                if($this->post->AsDefault($name) != $tokendata[0])
                    return false;

                if((time() - $tokendata[3]) < $tokendata[1])
                    return true;
                else
                    return false;
            }

            // CRYPTHASH
            if($field['vtype'] == Form::VTYPE_CRYPTHASH)
               return CryptHash::Verify($this->post->AsDefault($name), $field['vdata']);
            
            // Someone was a real fag if we get here
            return false;
        }

        /**
         * Verify all fields known to the form and set the error values (if any). You can call
         * Form::GetErrors after this function te get the actual error values.
         * \return true when all fields pass verification, false otherwise.
         */
        public function Verify()
        {
            $verdict = true;
            $this->errors = array();
            foreach ($this->fields as $name => $field)
            {
                if($this->VerifyField($name) === false)
                {
                    $verdict = false;
                    $this->errors[$name] = $field['error'];
                }
            }
            return $verdict;
        }

        /**
         * Fetch the submitted values (if any) of all fields described with Form::AddField
         * \return Returns an array of key=>value pairs where key is the form field name and value is the contents.
         */
        public function GetValues()
        {
            $values = array();
            foreach ($this->fields as $name => $field)
            {
                if ($this->post->Exists($name))
                    $values[$name] = $this->post->AsDefault($name);
            }
            return $values;
        }

        /**
         * Fetch all errors, note that if you haven't called Form::Verify that no errors will be returned.
         * \return an array of all errors, key => value is form field name => error  (e.g. array('age' => 'Invalid age range'))
         */
        public function GetErrors()
        {
            return $this->errors;
        }

    }
?>
