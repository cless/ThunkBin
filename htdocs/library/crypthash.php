<?php
    /**
     * An easy interface to the php crypt() function to generate secure adaptive hashes
     * This class only implements the crypt hash types that can be increased in difficulty
     * as available cpu (and gpu) power rises. That means only Blowfish (bcrypt), SHA512 and SHA256
     * crypt hashes are available and not MD5, DES or EXTDES
     */
    class CryptHash
    {
        /**
         * Hashtype for bcrypt (see CryptHash::Create
         */ 
        const BLOWFISH = 1;
        /**
         * Hashtype for SHA256 crypt (see CryptHash::Create)
         */ 
        const SHA256 = 2;
        /**
         * Hashtype for SHA512 crypt (see CryptHash::Create)
         */ 
        const SHA512 = 3;

        private static function MakeSalt($len)
        {
            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
            $str = '';
            while($len--)
                $str .= $alphabet[mt_rand(0, 63)];

            return $str;
        }
        
        /**
         * Create new salted password crypt hashes
         *
         * \param password The password you want to hash, note that when using CryptHash::BLOWFISH
         *                 only the first 55 (72) bytes are used to generate the hash.
         * \param hashtype Must be one of the supported hash types, CryptHash::BLOWFISH,
         *                 CryptHash::SHA512 or CryptHash::SHA256. If this parameter is not provided
         *                 CryptHash::BLOWFISH will be used by default.
         * \param rounds If provided this parameter MUST be an integer, not a float and not a
         *               string representing an integer.
         *               For CryptHash::SHA512 and CryptHash::SHA256 this is the number of hash
         *               rounds that should be used. By default 5000 rounds are used and if you 
         *               specify a custom number of rounds it should be between 1000 and 999999999
         *               For CryptHash::BLOWFISH this is the base-2 logarithm of the number of rounds.
         *               By default it is set to 6, which means 2^6 or 64 rounds. The minimum difficulty
         *               is 4 and the maximum is 31 when using CryptHash::BLOWFISH.
         * \return The return value is false on failure or a string representing the hash on success.
         */
        public static function Create($password, $hashtype = self::BLOWFISH, $rounds = false)
        {
            if($rounds !== false && !is_int($rounds))
                return false;
                
            switch ($hashtype)
            {
                case self::BLOWFISH:
                    if($rounds === false)
                        $rounds = 6;
                    if($rounds < 4 || $rounds > 31)
                        return false;
                    $salt = '$2a$'. sprintf("%02d", $rounds) . '$' . self::MakeSalt(22);
                break;
                
                // SHA512 shares almost all code with SHA256, make sure the case falltrough
                // does not fuck up the hash type when changing things!
                case self::SHA512;
                    $salt = '$6$';
                case self::SHA256:
                    if(!isset($salt))
                        $salt = '$5$';
                    // Actual salt and rounds shit
                    if($rounds !== false && ($rounds < 1000 || $rounds > 999999999))
                        return false;
                    if($rounds !== false)
                        $salt .= 'rounds=' . $rounds . '$';
                    $salt .= self::MakeSalt(16);
                break;
                
                default:
                    return false;
            }
            $hash = crypt($password, $salt);
            // It should be impossible to fail on the crypt call, not verifying that would be stupid though
            if(strlen($hash) < 13 && $hash != $salt)
                return false;
            return $hash;
        }
        
        /**
         * Verify Sha512, Sha256 or Blowfish hashes created with phps crypt() function or CryptHash::Create
         * This function can not verify MD5, DES or EXTDES crypt hashes.
         * 
         * \param password The password you want to compare to the hash
         * \param hash the hash you want to compare to the password
         * \return Returns true if the hash is a correct hash for the password. If an error happens during
         *         hashing or if the hash is invalid for the given password this function returns false.
         */
        public static function Verify($password, $hash)
        {
            $parts = explode('$', $hash);
            if(count($parts) < 3)
                return false;
            
            // Extract the correct salt from the hash
            switch ($parts[1])
            {
                case '2a':      // blowfish
                    $salt = substr($hash, 0, 29);
                break;
                case '5':       // sha256
                case '6':       // sha512
                    $salt = '$' . $parts[1] . '$' . $parts[2];
                    if(count($parts) == 5)
                        $salt .= '$' . $parts[3];
                break;
                default:
                    return false;
            }
             
            // Verify the hash
            $verify = crypt($password, $salt);
            if($hash == $verify && $verify !== false)
                return true;
            else
                return false;
        }
    }
?>
