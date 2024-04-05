<?php
namespace Zf\Ext;
use \Laminas\Mail\Storage\Message as MessageCore;
/**
 * Customize Laminas redis cache 
 * @author Jilv006
 */
class LaminasMailMessage extends MessageCore{
    const PATTERN_CHECK_HEADER = '/\;[\s\r\n]+[a-z0-9\-\*]+\=[\"\']+(.*)[\"\']+/i';
    const PATTERN_FROM_HEADER = '/(^(From|To|Reply-To)\:\h(.*)\s)/im';

    /**
     * Public constructor
     *
     * In addition to the parameters of Part::__construct() this constructor supports:
     * - file  filename or file handle of a file with raw message content
     * - flags array with flags for message, keys are ignored, use constants defined in \Laminas\Mail\Storage
     *
     * @param array $params
     * @throws Exception\RuntimeException
     */
    public function __construct(array $params)
    {
        if ( !empty($params['headers']) ){
            $reCheckHeaders = ['content-description' => 1, 'content-type' => 2];
            
            //fwrite(STDOUT, date('\[Y/m/d H:i:s\] '));
            switch ( gettype($params['headers']) ){
                case 'array':
                    //fwrite(STDOUT, 'From Array'. PHP_EOL. PHP_EOL);
                    $params['headers'] = $this->fixHeaderArray($params['headers']);
                    break;
                    
                case 'string':
                    //fwrite(STDOUT, 'From String'. PHP_EOL. PHP_EOL);
                    $params['headers'] = $this->fixHeaderFromString($params['headers']);
                    break;
                    
                default:
                    //fwrite(STDOUT, 'From Object'. PHP_EOL. PHP_EOL);
                    
                    $objHeaders = clone $params['headers'];
                    $objHeaders->clearHeaders();
                    $arrHeaders = $params['headers']->toArray();
                    
                    
                    //fwrite(STDOUT, "Debug-array: " . json_encode($arrHeaders) . PHP_EOL. PHP_EOL);
                    $matchs = [];
                    foreach ( $arrHeaders as $key => $header ){
                        if ( preg_match(self::PATTERN_CHECK_HEADER, $header, $matchs) ){
                            $tmpl = trim($matchs[1]);
                            if ( $this->hasJapaneseChar($header) ){
                                $arrHeaders[$key] = str_replace(
                                    $matchs[1],
                                    $this->base64Encode($tmpl),
                                    $header
                                );
                            }elseif ( $tmpl !== $matchs[1] ){
                                $arrHeaders[$key] = str_replace(
                                    $matchs[1], $tmpl, $header
                                );
                            }
                        }else {
                            if (isset($reCheckHeaders[strtolower($key)]) &&
                                $this->hasJapaneseChar($header)
                            ){
                                $arrHeaders[$key] = $this->base64Encode($header);
                            } else $arrHeaders[$key] = trim($header);
                        }
                    }
                    
                    //fwrite(STDOUT, 'Debug-class: ' . json_encode($arrHeaders) . PHP_EOL. PHP_EOL . PHP_EOL. PHP_EOL);
                    $params['headers'] = $objHeaders->addHeaders($arrHeaders);
                    
                    break;
            }
        }
        parent::__construct($params);
    }

    // Fix: From is group email. Ex: From: =?ISO-2022-JP?B?GyRCJV4lKCUrJW8lMSUiJTUhPCVTJTkbKEI=?= <info@abc.jp, sale@abc.jp>
    protected function fixFromWithGroupEmail($headers){
        $matchs = [];
        if( preg_match_all(self::PATTERN_FROM_HEADER, $headers, $matchs) ){
            $emailPatttern = '/^((?P<name>.*)<(?P<email>[^\>]+)>|(?P<name1>[^\@]+)\h(?P<email1>.+))|\h(?P<email2>.*)$/';
            $validator = new \Laminas\Validator\EmailAddress(
                \Laminas\Validator\Hostname::ALLOW_DNS| 
                \Laminas\Validator\Hostname::ALLOW_LOCAL
            );
            
            $subMatchs = [];
            foreach ($matchs[3] as $idx => $header){
                if ( preg_match($emailPatttern, $header, $subMatchs) ){
                    $name = rtrim(trim($subMatchs['name'] ?? ($subMatchs['name1'] ?? '')), ',');
                    
                    $email = trim($subMatchs['email'] ?? (
                        $subMatchs['email1'] ?? ($subMatchs['email2'] ??'')
                    ));
                    
                    $isValid = false;
                    foreach ( preg_split('/([\;\,\h]+)/', str_replace(
                        ['"', "'", '＠'], ['', '', '@'], $email
                    )) as $checkEmail ){
                        $checkEmail = trim($checkEmail);
                        if( $validator->isValid($checkEmail) ){
                            $email = $checkEmail;
                            $isValid = true;
                            break;
                        }
                    }
                    
                    if ( empty($isValid) ){
                        $baseEmail = [];
                        if ( preg_match('/[\w\@\.\-]+/', $email, $baseEmail) ){
                            $email = $baseEmail[0];
                        }else $email = 'unknow@hostname.com';
                    }
                    
                    if ( empty($name) ) $name = 'Unknow';
                    //elseif ( $this->hasJapaneseChar($name) ) $name = $this->base64Encode($name);
                    $name = str_replace('"', '', $name);
                    
                    $headers = str_replace(
                        $matchs[1][$idx],
                        "{$matchs[2][$idx]}: \"{$name}\" <{$email}>" . \Laminas\Mail\Headers::EOL,
                        $headers
                    );
                }
            }
        }

        return $headers;
    }
    /**
     * Fix subject/sender not encoded
     * @param string $headers
     * @return mixed
     */
    protected function fixSubject($headers){
        $matchs = [];
        if( preg_match_all('/(((Subject\:|Sender\:)\h(.*))\s)/i', $headers, $matchs) ){
            foreach($matchs[4] as $idx => $subject){
                $header = trim($matchs[1][$idx]);
                if ( $this->hasJapaneseChar($subject) ){
                    $header = str_replace(
                        $subject, $this->base64Encode($subject), $header
                    ) . \Laminas\Mail\Headers::EOL;
                    
                    $headers = str_replace(
                        $matchs[1][$idx], $header, $headers
                    );
                }  
            }
        }

        return $headers;
    }

    /**
     * Fix header from string
     * @param string $headers
     * @return string
     */
    protected function fixHeaderFromString($headers){
        
        //fwrite(STDOUT, 'Header string: ' . $headers . PHP_EOL. PHP_EOL);
        foreach (['fixFromWithGroupEmail', 'fixSubject'] as $method ) {
            $headers = $this->{$method}($headers);
        }

        $matchs = [];
        if ( preg_match_all(
            self::PATTERN_CHECK_HEADER,
            $headers, $matchs
        )){
            foreach ( ($matchs[1] ?? []) as $idx => $name ){
                $tmpl = trim($name);
                if ( $this->hasJapaneseChar($name) ){
                    $tmpl = str_replace(
                        $name, $this->base64Encode($tmpl),
                        $matchs[0][$idx]
                    );
                    $headers = str_replace(
                        $matchs[0][$idx], $tmpl,
                        $headers
                    );
                }elseif( $tmpl !== $name ){
                    $tmpl = str_replace(
                        $name, $tmpl,
                        $matchs[0][$idx]
                    );
                    
                    $headers = str_replace(
                        $matchs[0][$idx], $tmpl,
                        $headers
                    );
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Fix header of mail from array
     * @param array $headers
     * @return array
     */
    protected function fixHeaderArray($headers){
        foreach ($headers as $idx => $val){
            if ( is_array($val) ){
                
                $matchs = []; $isMatch = false;
                if ( strtolower($val[0] ?? '') == 'subject' ){
                    $matchs = [$val[1], $val[1]];
                    $isMatch = true;
                }else{
                    $isMatch = preg_match(self::PATTERN_CHECK_HEADER, $val[1], $matchs);
                }
                
                if ( $isMatch ){
                    $tmpl = trim($matchs[1]);
                    if ( $this->hasJapaneseChar($tmpl) ){
                        $val[1] = str_replace(
                            $matchs[1],
                            $this->base64Encode($tmpl),
                            $val[1]
                        );
                        $headers[$idx] = $val;
                    }elseif ( $tmpl !== $matchs[1] ){
                        $headers[$idx][1] = str_replace(
                            $matchs[1], $tmpl, $val[1]
                        );
                    }
                }
            }
        }
        return $headers;
    }
    
    /**
     * Convert string to base64
     * @param string $str
     * @return string
     */
    protected function base64Encode($str){
        return \Laminas\Mime\Mime::encodeBase64Header($str, 'UTF-8');
    }
    
    /**
     * Check string has japanese keyword
     * @param string $str
     * @return boolean
     */
    protected function hasJapaneseChar($str){
        if (preg_match(
            '/([\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}])/u', 
            $str
        )){
            return true;
        }
        return false;
    }
}
