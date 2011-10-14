<?php

class PBKDF2
{
    private static function GetHashLen($hashfunc)
    {
        switch($hashfunc)
        {
            case 'hmac-md5':
                return 16;
            case 'hmac-sha1':
                return 20;
            case 'hmac-sha224':
                return 28;
            case 'hmac-sha256':
                return 32;
            case 'hmac-sha384':
                return 48;
            case 'hmac-sha512':
                return 64;
            default:
                return false;
        }
    }

    private static function PseudoRandomFunction($hashfunc, $password, $salt)
    {
        // HMAC is currently the only supported mode so we can assume every
        // $hashfunc will be a hmac function becase it was validated earlier
        // by GetHashLen
        return hash_hmac(substr($hashfunc, 5), $salt, $password, true);
    }

    public static function GetKey($hashfunc, $password, $salt, $rounds, $keylen)
    {
        // The RFC allows keys up to 2^32-1 bytes in size but because php is
        // such a nice guy who uses signed 32 bit varables and because we don't
        // actually want to use 4 GB of memory I will limit the key length to a
        // more sane value (24 bits, 16 megabytes)
        if ($keylen >= 16*1024*1024)
            return false;
    
        $hlen = PBKDF2::GetHashLen($hashfunc);
        if($hlen === false)
            return false;

        $l = (int)ceil($keylen / $hlen);
        
        $dk = '';
        for($block = 1; $block <= $l; $block++)
        {
            $blockhash = PBKDF2::PseudoRandomFunction($hashfunc, $password, $salt . pack('N', $block));
            $xorhash = $blockhash;
            for($round = 1; $round < $rounds; $round++)
            {
                $blockhash = PBKDF2::PseudoRandomFunction($hashfunc, $password, $blockhash);
                $xorhash ^= $blockhash;
                
            }
            $dk .= $xorhash;
        }

        return substr($dk, 0, $keylen);
    }
};

?>
