<?php
namespace Zf\Ext\Utilities;

use Zf\Ext\Utilities\ZFFilterSpecialChar;
use Zf\Ext\Utilities\ZFFilterUnicode;

class ZFHelper
{

    /**
     * @var ServerRequestInterface
     */
    public $_request = null;
    
    /**
     *
     * @var ZF_Filter_SpecialChar
     */
    protected $noSpecialChar;

    /**
     *
     * @var ZF_Filter_Unicode
     */
    protected $noUnicode;

    public function __construct()
    {
        $this->noSpecialChar = new ZFFilterSpecialChar();
        
        $this->noUnicode = new ZFFilterUnicode();
    }

    /**
     * Check hour format: [hh:ss a|A]
     * @param string $strHour
     * @return bool
     */
    public function isValidHour( $strHour = '', $isFull = false ){
        
        if( $strHour ){
            $pattern = '(^[0-9]{1,2}\:[0-9]{1,2})(\s(am|pm)){0,1}$';
            if ( true === $isFull )
                $pattern = '(^[0-9]{1,2}\:[0-9]{1,2}\s(am|pm))$';
            return (preg_match('/'.$pattern.'/i', $strHour, $matchs ) === 1);
        }
        return false;
    }
    
    /**
     * Remove all special character
     *
     * @param
     *            $value
     * @return string
     */
    public function noSpecialChar($value)
    {
        return $this->noSpecialChar->filter($value);
    }

    /**
     * Create string alias
     *
     * @param string $value            
     * @return string
     */
    public function stringToAlias($value)
    {
        $alias = $this->noSpecialChar($this->noUnicode->setCharSpecical("-")
            ->filter($value));
        
        // Return
        return strtolower($alias);
    }

    /**
     * Thoi gian bat dau cua ngay
     * 
     * @param int|string $time            
     */
    public function timeStart($time)
    {
        if (is_string($time)) {
            $time = strtotime($time);
        }
        
        return mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    }

    /**
     * Thoi gian ket thuc cua ngay
     * 
     * @param int|string $time            
     */
    public function timeEnd($time)
    {
        if (is_string($time)) {
            $time = strtotime($time);
        }
        
        return mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));
    }

    /**
     * Get client IP
     * 
     * @return string
     */
    public function getClientIp()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        
        $ip = filter_var($ipaddress, FILTER_VALIDATE_IP);
        if ( $ip ) return $ip;
        return preg_replace('/[^a-z0-9\.\:]/i', '', $ipaddress);
    }
    
    /**
     * Random string
     * 
     * @param number $length            
     * @param bool $useSpecialChar            
     * @return string
     */
    public function getRandomString($length = 10, $useSpecialChar = false, $userNum = true)
    {
        if ($length <= 0)
            return '';
        
        $specialChar = '`!@#&$%^*-_+={}[];?<>,.|';
        $string = ($userNum ? '0123456789': ''). "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" . ($useSpecialChar ? $specialChar : '');
        return substr(str_shuffle($string), 0, $length);
    }

    /**
     * Render code
     * 
     * @param array $opts
     *            $id: number, Integer value
     *            
     *            $maxLen: integer, Length of string result
     *            
     *            $to: integer, Tobase integer value
     *            
     * @return string
     */
    public function getRandomCode($opts = array())
    {
        $opts = array_merge(array(
            'id' => memory_get_usage(true) + time(),
            'maxLen' => 15,
            'toBase' => 32,
            'isSpacialChar' => false
        ), $opts);
        
        if ($opts['maxLen'] <= 0)
            return '';
            
            // random by ID
        $str = base_convert($opts['id'], 10, $opts['toBase']);
        $strLeng = strlen($str);
        $length = $opts['maxLen'] - $strLeng;
        
        // Random string
        if ($length > 0) {
            
            $strRand = str_split($this->getRandomString($length, $opts['isSpacialChar']));
            $strArr = str_split($str);
            $min = min(array(
                $length,
                $strLeng
            ));
            
            $result = array();
            $no = 0;
            while ($no < $min) {
                $result[] = array_pop($strRand);
                $result[] = array_pop($strArr);
                $no ++;
            }
            ;
            $str = implode('', $result);
            
            if (count($strRand) > 0)
                $str .= implode('', $strRand);
            else
                $str .= implode('', $strArr);
        }
        return substr($str, 0, $opts['maxLen']);
    }

    /**
     * Escap numeric format
     * 
     * @param string $numeric            
     * @return string
     */
    public function escNumberFormat($numeric = '')
    {
        return str_replace(',', '', $numeric);
    }

    /**
     * Escap Vietnam char
     * 
     * @param string $string            
     * @return string
     */
    public function noMark($string)
    {
        if (! $string)
            return '';
        $utf8 = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            
            'd'=>'đ',
            
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            
            'i'=>'í|ì|ỉ|ĩ|ị',
            
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            
            'D'=>'Đ',
            
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach ($utf8 as $ascii => $uni)
            $string = preg_replace("/($uni)/", $ascii, $string);
        return $string;
    }

    /**
     * Lay ra bao nhieu ky tu
     * 
     * @param string $string            
     * @return string
     */
    public function splitWord($string = '', $length = 255, $lang = 'vi')
    {
        if ($lang != 'jp') {
            $strArr = preg_split('/\s+/', $string, $length);
            if (count($strArr) > 0) {
                array_pop($strArr);
                return implode(' ', $strArr);
            }
        } else {
            setlocale(LC_ALL, "ja_JP.utf8");
            $strArr = preg_split("//u", $string, $length + 1, PREG_SPLIT_NO_EMPTY);
            return array_splice($strArr, 0, $length);
        }
        return $string;
    }

    /**
     * Escap input mask format
     * 
     * @param string $strNum            
     * @return string
     */
    public function escMaskFormat($strNum = '', $lang = 'vi')
    {
        if ($lang == 'vi') {
            return str_replace(',', '', $strNum);
        }
        return str_replace('.', '', $strNum);
    }

    /**
     * Parse string to hex code
     * 
     * @param string $string            
     * @return string
     */
    function strToHex($str = '')
    {
        $arr = array(
            '{',
            '}',
            '[',
            ']',
            ';',
            '"',
            ',',
            '.',
            '=',
            ' '
        );
        $hex = '';
        $leng = strlen($str);
        for ($i = 0; $i < $leng; $i ++) {
            if (! in_array($str[$i], $arr))
                $hex .= '\x' . dechex(ord($str[$i]));
            else
                $hex .= $str[$i];
        }
        return $hex;
    }

    /**
     * Escap email, url
     * 
     * @param string $string            
     * @return string
     */
    public function escEmailPhoneUrl($string = '')
    {
        $no = $no1 = 0;
        $patterns = array();
        $patterns[$no ++] = '/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/';
        $patterns[$no ++] = '/([0-9]{5,10})/';
        // $patterns[$no++] = '((\'|\")\w{3,6}:(?:(?://)|(?:\\\\))[^\s].*\.(com|net|biz|jp|info|org|us)(\'|\"))';
        $patterns[$no ++] = '((\w|\-|\.|\/\/|\:){0,20}\.(com|net|biz|jp|info|org|us|top|in|edu|gov)(\/){0,1})';
        
        $replacements = array();
        $replacements[$no1 ++] = '';
        $replacements[$no1 ++] = '';
        // $replacements[$no1++] = '""';
        $replacements[$no1 ++] = '';
        
        // should use just one call of preg_replace for perfomance issues
        return preg_replace($patterns, $replacements, $string);
    }

    /**
     * Check is Kanji Character
     * 
     * @param string $str            
     * @return bool
     */
    public function isKanji($str)
    {
        return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
    }
    /**
     * Check is Rare Kanji Character
     *
     * @param string $str
     * @return bool
     */
    public function isRareKanji($str)
    {
        return preg_match('/[\x{3400}-\x{4dbf}]/u', $str) > 0;
    }
    /**
     * Check is Hiragana Character
     * 
     * @param string $str            
     * @return bool
     */
    public function isHiragana($str)
    {
        return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
    }
    /**
     * Check is Full-width Roman characters and half-width Katakana
     *
     * @param string $str
     * @return bool
     */
    public function isRomanHiragana($str)
    {
        return preg_match('/[\x{ff00}-\x{ff9f}]/u', $str) > 0;
    }
    /**
     * Japanese-style punctuation
     *
     * @param string $str
     * @return bool
     */
    public function isJapaneseStyle($str)
    {
        return preg_match('/[\x{3000}-\x{303f}]/u', $str) > 0;
    }
    /**
     * Check is Katakana Character
     * 
     * @param string $str
     * @return bool
     */
    public function isKatakana($str)
    {
        return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
    }

    /**
     * Check is Japanese Character
     * 
     * @param string $str            
     * @return bool
     */
    public function isJapaneseCharacters($str)
    {
        return
            $this->isKanji($str)
            || $this->isHiragana($str)
            || $this->isKatakana($str)
            || $this->isJapaneseStyle($str)
            || $this->isRareKanji($str)
            || $this->isRomanHiragana($str)
            ;
    }
    
    /**
     * Unserialize session data by php encoded
     * @param string $str
     * @return array
     */
    public function unserializePhp( $str = '' ){
        $variables = [];
        $arr = preg_split("/(\w+)\|/", $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $totalCount = count($arr);
        
        for($i=0; $i < $totalCount; $i = $i+2 ){
            $val = unserialize($arr[$i+1]);
            if (false !== $val ){
                $variables[$arr[$i]] = unserialize($arr[$i+1]);
            }else{
                $variables[$arr[$i]] = $arr[$i+1];
            }
        }
        return $variables;
    }
    
    /**
     * Unserialize session data by php binary encoded
     * @param string $str
     * @return array
     */
    public function unserializePhpbinary( $str = '' ) {
        $returnData = [];
        $offset = 0;
        while ( $offset < strlen($str) ) {
            $num = ord($str[$offset]);
            $offset += 1;
            $varname = substr($str, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($str, $offset));
            $returnData[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $returnData;
    }
    
    /**
     * PHP session decode
     * 
     * @param string $str            
     * @return array
     */
    public function sessionDecode( $str = '' ) {
        
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php":
                return self::unserializePhp($str);
                break;
            case "php_binary":
                return self::unserialize_phpbinary($str);
                break;
            default:
                // --- This method very dangerous ----
                $oldVals = $_SESSION;
                session_decode($str);
                $rsData = $_SESSION;
                $_SESSION = $oldVals;
                return $rsData;
                break;
        }
    }

    /**
     * PHP session encode
     * 
     * @param array $contents            
     * @return string
     */
    public function sessionEncode($contents)
    {
        $result = '';
        foreach ($contents as $key => $val) {
            $result .= $key . '|' . serialize($val);
        }
        return $result;
    }
    
    /**
     * @param String $filename
     * @return mixed
     */
    public function getAuthenFromSession( $filename = '', $isMy = false ){
        $rs = [];
        $authenData = $this->getSessionContent($filename, $isMy);
        
        if ( false == empty($authenData['Zend_Auth']) ){
            $rs = $authenData['Zend_Auth']->getArrayCopy();
            
            if ( $rs && false == empty($rs['session']) ){
                if ( is_object($rs['session']) )
                    $rs = get_object_vars($rs['session']);
                else $rs = $rs['session'];
                
                // --
                $filename = str_replace('\\', '/', $filename);
                if ( false !== strpos($filename, '/') )
                    $filename = array_pop(explode('/', $filename));
                
                // same with current session
                if ( session_id() == $filename && false === $isMy ){
                    return [];
                }
            }
        }
        return $rs;
    }
    
    /**
     * @param String $filename
     * @return mixed
     */
    public function getSessionContent( $filename = '', $isMy = false ){
        
        if ( file_exists($filename) ) {
            $path = $filename;
        }
        else{
            $basePath = SESSION_PATH;
            /* if( defined(APPLICATION_VERSION) ){
                $path = implode('/', [
                    $basePath, APPLICATION_SITE, APPLICATION_VERSION
                ]);
                if ( !is_dir($path) )
                    $path = "{$basePath}/" . APPLICATION_VERSION;
            }
            else $path = "{$basePath}/" . APPLICATION_SITE; */
            $path = "{$basePath}/sess_" . $filename;
        }
        
        $result = [];
        if ( file_exists($path) ) {
            
            // -- Get session content --
            $contents = file_get_contents($path);
            // -- Error to get file content --
            if ( $contents == ''){
                $isLocked = false;
                
                // -- Check state of current session --
                $fp = fopen($path, 'r');
                if ( !flock($fp, LOCK_EX|LOCK_NB, $wouldblock) ) {
                    if ( $wouldblock ) $isLocked = true;
                }else $isLocked = true;
                
                // --- Current session is locked --
                if ( $isLocked ) {
                    $result['Zend_Auth'] = new \Laminas\Stdlib\ArrayObject([
                        'last_active' => time()
                    ]);
                }
            }else{
                // -- Get Zend session content --
                $result = $this->sessionDecode($contents);
            }
        }
        return $result;
    }
    
    /**
     * Edit Zend_Auth
     *
     * @param string $filename
     *  <p>File is full directory or only name of file</p>
     * @param array $exact
     *            <p>col: name of column to check. Ex: account_id</p>
     *            <p>val: integer</p>
     * @return bool
     */
    public function editSession($filename = '', $exact = array(), $data = array() )
    {
        try {
            if ( file_exists($filename) ) $path = $filename;
            else{
                $path = SESSION_PATH . '/sess_'. $filename;
            }
            
            if ( file_exists($path) ) {
        
                // -- Change permission
                //if ( !is_writable($path) )
                @chmod($path, 0755);
        
                // -- Get Zend session content
                $contents = file_get_contents($path);
                
                // -- Unset Zend_Auth
                if ( '' !== $contents ) {
                    // Decode session content
                    $result = $this->sessionDecode($contents);
                    if ( false == empty($result['Zend_Auth']) ) { //&& $result['Zend_Auth']['storage']
                        $authData = $result['Zend_Auth']->getArrayCopy();
                        $isExact = count($exact) > 0; $isValid = true;
                        if ( $isExact ){
                            foreach ($exact as $key => $val)
                                $isValid = $isValid && ($authData['session']->{$key} == $val);
                        }
                        
                        if ( ($isExact && $isValid) || !$isExact) {
                            if ( !$data ){
                                // Destroy authentication value
                                unset($result['Zend_Auth'], $result['HasLogin']);
                            }else{
                                foreach ( $data as $key => $val )
                                    $authData['session']->{$key} = $val;
                                $result['Zend_Auth']->exchangeArray($authData);
                            }
                            
                            // Encode session content
                            $strSession = $this->sessionEncode($result);
        
                            // Update session cotent to file
                            @file_put_contents($path, $strSession);
                            @chmod($path, 0600);
                            unset($strSession);
                        }
                    }
                    unset($contents, $result);
                }
            }
            return true;
        } catch (\Exception $e) {}
        return false;
    }
    
    /**
     * Destroy Zend_Auth
     *
     * @param string $filename
     * @param array $exact
     *            <p>col: name of column to check. Ex: account_id</p>
     *            <p>val: integer</p>
     * @return bool
     */
    public function logOutSession($filename = '', $exact = array())
    {
        try {
            //$path = DATA_PATH .'/session/sess_'. $filename;

            if ( file_exists($filename) ) $path = $filename;
            else{
                $path = SESSION_PATH . '/sess_'. $filename;
            }

            if ( file_exists($path) ) {

                // -- Change permission
                //if ( !is_writable($path) ) 
                @chmod($path, 0755);

                // -- Get Zend session content
                $contents = file_get_contents($path);

                // -- Unset Zend_Auth
                if ( '' !== $contents ) {

                    // Decode session content
                    $result = $this->sessionDecode($contents);
                    $authen = null;
                    if ( $result['Zend_Auth'] ){
                        $zfAuthen = $result['Zend_Auth']->getArrayCopy();
						if ( $zfAuthen['session'] ){
							$authen = $zfAuthen['session'];
						}unset($zfAuthen);
                    }
                    
                    if ( $authen ) {
                        $isExact = count($exact) > 0;
                        
                        if (($isExact && $authen->{$exact['col']} == $exact['val']) || ! $isExact) {

                            // Destroy authentication value
                            unset($result['Zend_Auth'], $result['HasLogin']);

                            // Encode session content
                            $strSession = $this->sessionEncode($result);

                            // Update session cotent to file
                            $rs = @file_put_contents($path, $strSession);
                            unset($strSession);
                            return $rs;
                        }
                    }
                    unset($contents, $result);
                }else{
                    return false;
                }
            }
            return true;
        } catch (\Throwable $e) {}
        return false;
    }

    /**
     *
     * @param string $str            
     * @return integer
     */
    public function getCharCode($str = '')
    {
        $strArr = str_split($str);
        $result = array();
        foreach ($strArr as $idx => $char) {
            $result[] = ($idx + 1) * ord($char);
        }
        return $result;
    }

    /**
     * Chuyen doi string sang integer
     * 
     * @param string $str            
     * @return integer
     */
    public function strToUniqueInt($str = '')
    {
        return array_sum($this->getCharCode($str));
    }

    /**
     * Kiem tra nickname co hop le hay khong
     * 
     * @param string $str            
     * @return bool
     */
    public function isNotValidNickname($str = '')
    {
        $notAllow = [
            '',
            '`',
            '!',
            '@',
            '#',
            '$',
            '%',
            '^',
            '&',
            '*',
            '(',
            ')',
            '-',
            '+',
            '=',
            '{',
            '}',
            '[',
            ']',
            '|',
            '\\',
            ':',
            ';',
            '"',
            ',',
            '.',
            '<',
            '>',
            '/',
            '?',
            's'
        ];
        return preg_match('/[' . implode('\\', $notAllow) . ']/', $str);
    }
    
    /**
     * Rut gon so
     * @param number $num
     * @param number $length
     * @return string
     */
    public function shortNumeric( $num = 0, $length = 4 ){
        
        $min = doubleval('0.' . str_repeat( '0', max($length-1, 0) ) . '1');
        if ( $num < $min ) return '0';
        
        if ( $num ){
            preg_match('/(.*\.\d{0,'.$length.'})/', (string)$num, $maches);
            if ( $maches ) $num = $maches[0];
        }
        return (string)$num;
    }
    
    /**
     * Lam tron so
     * @param number $num
     * @param number $length
     * @return string
     */
    public function roundNumeric( $num = 0, $length = 4 ){
        if ( $num ) $num = round($num, $length, PHP_ROUND_HALF_UP);
        return self::shortNumeric($num);
    }
}