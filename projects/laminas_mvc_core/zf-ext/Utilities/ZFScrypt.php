<?php
namespace Zf\Ext\Utilities;

class ZFScrypt
{

    /**
     * Random string
     * 
     * @param number $length            
     * @param bool $useSpecialChar            
     * @param bool $usNum            
     * @return string
     */
    public static function getRandomString($length = 10, $useSpecialChar = false, $usNum = true)
    {
        $specialChar = '~`!@#$%^&*()-_+={}[]|;"<>,.?';
        $number = '0123456789';
        $string = ($usNum ? $number : '') . "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" . ($useSpecialChar ? $specialChar : '');
        return substr(str_shuffle($string), 0, $length);
    }

    /**
     * Length of salt string
     * 
     * @var integer
     */
    const SALT_LENGTH = 5;

    /**
     *
     * @param string $pass            
     * @param string $salt            
     * @return string md5
     */
    public static function createPassword($pass = '', $salt = '')
    {
        // md5 string
        return md5(self::formatPassword($pass, $salt));
    }

    /**
     * Replace key
     * 
     * @var string
     */
    const SALT_SEPARATOR = '_[.]_{salt_key}_[.]_';

    /**
     *
     * @param string $pass            
     * @param string $salt            
     * @return string
     */
    public static function formatPassword($pass = '', $salt = null)
    {
        if (null === $salt)
            $salt = self::SALT_SEPARATOR;
        $length = intval(strlen($pass) / 2);
        return substr($pass, 0, $length) . $salt . substr($pass, $length);
    }

    /**
     *
     * @param string $salt            
     * @param string $secur            
     * @return string
     */
    protected static function enScrypSalt($salt = '', $secur = '')
    {
        $salt = str_split($salt);
        $secur = str_split($secur);
        $string = '';
        foreach ($salt as $index => $char) {
            $string .= $char . $secur[$index];
        }
        return $string;
    }

    /**
     *
     * @param string $pass            
     * @param string $salt            
     * @return string
     */
    public static function enScrypt($pass = '', $salt = '', $secur = '')
    {
        // -- enscrypt salt string
        $salt = self::enScrypSalt($salt, $secur);
        
        // -- string to array
        $salt = str_split(base64_encode($salt), 1);
        $strLengthSalt = count($salt);
        $pass = str_split($pass, 1);
        $passLength = count($pass);
        // -- get max length
        $maxLength = max(array(
            $strLengthSalt,
            $passLength
        ));
        
        // result
        $string = '';
        $no = 0;
        while ($no < $maxLength) {
            if (isset($salt[$no])) {
                $string .= $salt[$no];
            }
            if (isset($salt[$no])) {
                $string .= $pass[$no];
            }
            $no ++;
        }
        $length = intval(strlen($string) / 2);
        return base64_encode(substr($string, 0, $length) . "p@L{$passLength}p@R" . substr($string, $length));
    }

    /**
     *
     * @param string $salt            
     * @return string
     */
    public static function deScrypt($pass = '', $salt = '')
    {
        // decode
        $pass = base64_decode($pass);
        preg_match('/p\@L\d+p\@R/', $pass, $matchs);
        // string to array
        $pass = str_replace($matchs[0], '', $pass);
        $pass = str_split($pass, 1);
        // -- get pass length
        $passLength = (int) str_replace(array(
            'p@L',
            'p@R'
        ), '', $matchs[0]);
        // -- get password
        $strPass = '';
        $no = 1;
        $i = 0;
        while ($no <= $passLength) {
            if (($i % 2) !== 0) {
                $strPass .= $pass[$i];
                $no ++;
            }
            $i ++;
        }
        return $strPass;
    }

    /**
     * Private key
     * 
     * @var string
     */
    const SALT_ACTIVE = 'x@KebgRw9H';

    /**
     * Private key
     * 
     * @var integer
     */
    const SALT_ACTIVE_LENGTH = 3;

    /**
     * account
     * 
     * @var string
     */
    const ACTIVE_TYPE_USER = 'USER';

    /**
     * EMPLOYER
     * 
     * @var string
     */
    const ACTIVE_TYPE_COMPANY = 'COMPANY';

    /**
     * Ma hoa chuoi active
     * 
     * @param string $str            
     * @return string
     */
    public static function endScryptActive($str = '')
    {
        // Step 1
        $str = base64_encode($str);
        $lengthStr = strlen($str);
        $length = (int) ($lengthStr / 2);
        $str = str_replace('==', '', $str);
        $str = strrev($str);
        
        // Step 2
        $splitItems = str_split($str, $length);
        $length = strlen($splitItems[1]);
        
        // Create random private key
        $salt1 = self::getRandomString(self::SALT_ACTIVE_LENGTH, true);
        $salt2 = self::getRandomString(self::SALT_ACTIVE_LENGTH, true, false);
        $salt3 = self::getRandomString(self::SALT_ACTIVE_LENGTH, true, false);
        $salt4 = self::getRandomString(self::SALT_ACTIVE_LENGTH, true, false);
        
        // Step 3
        return base64_encode(implode('', array(
            $salt1,
            $length,
            $salt2,
            $lengthStr,
            $salt3,
            $splitItems[1],
            $salt4,
            $splitItems[0]
        )));
    }

    /**
     * Ma hoa chuoi active
     * 
     * @param string $str            
     * @return string
     */
    public static function decodeScryptActive($str = '')
    {
        // Step 1: remove salt1
        $str = substr(base64_decode($str), self::SALT_ACTIVE_LENGTH);
        
        // -- Get length of firt part
        $length = (int) $str;
        $str = substr($str, strlen("{$length}") + self::SALT_ACTIVE_LENGTH);
        $lengthStr = (int) $str;
        
        // Step 2: remove length & salt2
        $str = substr($str, (strlen("{$lengthStr}") + self::SALT_ACTIVE_LENGTH));
        
        // Step 3: [part1, salt3, part0]
        $part1 = substr($str, 0, $length);
        $part0 = substr($str, ($length + self::SALT_ACTIVE_LENGTH));
        
        $str = strrev($part0 . $part1);
        
        if (strlen($str) != $lengthStr)
            $str .= '==';
            
            // Step 4
        return base64_decode($str);
    }
}