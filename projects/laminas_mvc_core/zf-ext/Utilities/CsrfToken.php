<?php
namespace Zf\Ext\Utilities;
/**
 * Create and valid token
 * @author PhapIt
 *
 */
class CsrfToken{
	/**
     * Check expired
     * @var bool
     */
	public $_useOneTime = true;
    public function __construct()
    {}
    
    /**
     * Folder contain csrf token
     * @var string
     */
    const CSRF_TOKEN_FOLDER = 'csrf_tokens';
    /**
     * Csrf token
     * @var string
     */
    const CSRF_TOKEN_EXT = 'csrf';
    const RAND_STRING = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    /**
     * Only accept letters and number
     * @param string $str
     * @return string
     */
    protected static function normalStr( $str = '' ){
        return preg_replace('/[^a-zA-Z0-9]/', '', $str );
    }
    
    /**
     * Create key
     * @param array $opts
     * @return string
     */
    protected static function createCsrkKey ($opts = [] ){
        return self::normalStr(md5(json_encode(array_merge([
                $_SERVER['REMOTE_ADDR'] ?? '',
                getHostByName(getHostName())
            ], $opts
        ))));
    }
    
    /**
     * Create csrf file path
     * @param string $userFolder
     * @param string $filename
     * @return string
     */
    protected static function generalCsrkPath ($userFolder, $filename, $site = null ){
        if ( defined('CSRF_TOKEN_DIR') )
            return implode('/', [
                CSRF_TOKEN_DIR,
                "{$userFolder}_{$filename}." . self::CSRF_TOKEN_EXT
            ]);
        else 
            return implode('/', [
                DATA_PATH,
                self::CSRF_TOKEN_FOLDER,
                $site ?? APPLICATION_SITE,
                "{$userFolder}_{$filename}." . self::CSRF_TOKEN_EXT
            ]);
    }
    /**
     * Get redis cache core
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected function getRedisCache(string $namespace, int $lifetime = null){
        return \Zf\Ext\CacheCore::_getRedisCaches($namespace, [
            'lifetime' => $lifetime,
            'namespace'=> "CSRF_TOKEN_{$namespace}"
        ]);
    }
    /**
     * Save token to redis
     * @param string $namespace
     * @param string $key
     * @param string $token
     * @param number $lifetime
     * @return boolean
     */
    protected function saveTokenByRedisAdapter(string $namespace, string $key, string $token, int $lifetime = 86400){
        return $this->getRedisCache($namespace, $lifetime)->setItem($key, $token);
    }
    /**
     * Check token is valid by redis adapter
     * @param string $namespace
     * @param string $key
     * @param string $token
     * @param int $lifetime
     * @return boolean
     */
    protected function isValidTokenByRedisAdapter(string $namespace, string $key, int $lifetime = 86400){
        $redis = $this->getRedisCache($namespace, $lifetime);
        $hasItem = $redis->hasItem($key);
        $isValid = ($hasItem
        && ($time = $redis->getItem($key)) > 0
        && (time() - $time) < $lifetime);
        
        if ($hasItem && $this->_useOneTime ) $redis->removeItem($key);
        
        unset($redis);
        return $isValid;
    }
    /**
     * Check token is valid by redis adapter
     * @param string $namespace
     * @param string $key
     * @param string $token
     * @param int $lifetime
     * @return boolean
     */
    protected function clearTokenByRedisAdapter(string $namespace, string $key, int $lifetime = 86400){
        return $this->getRedisCache($namespace, $lifetime)
        ->removeItem($key);
    }
    
    /**
     * Create CSRF token
     * @param string $userFolder
     * @param array $unique
     * @param string $site
     * @param number $lifetime
     * @return string
     */
    public function generalCsrfToken( $userFolder, $unique = [], $site = null, int $lifetime = 86400 ){
        $key = self::createCsrkKey($unique); $length = strlen($key);
        
        if ( defined('REDIS_CONFIG') ){
            $this->saveTokenByRedisAdapter(
                $site ?? APPLICATION_SITE, "{$userFolder}_{$key}", time(), $lifetime
            );
        }else{
            @file_put_contents(
                self::generalCsrkPath($userFolder, $key, $site), 
                base64_encode(gzcompress(time()))
            );
        }
        
        $strList = self::RAND_STRING;
        // ---- Format token
        $char = $key[rand(0, $length-1)]; $charCode = ord($char);
        $key = str_replace($char, ':', $key);
    
        return $length . $strList[rand(0, 51)]. strrev($key) . $charCode;
    }
    
    /**
     * Check token string is valid
     * @param string $token
     * @return boolean|string
     */
    public function validToken ( $token = '' ){
        if ( strlen($token) < 36 ) return false;
        
        if( 1 !== preg_match('/^(\d+)/', $token, $matchs) )
            return false;
            
        $tkLength = (int)$matchs[0]; $length = strlen($tkLength) + 1;
        // --- Parse token
        $key = chr((int)mb_substr($token, $length + $tkLength));
        $token = self::normalStr(str_replace(':', $key,
            strrev(mb_substr($token, $length, $tkLength))
        ));
        
        return $token;
    }
    
    /**
     * Check CSRF token
     * @param string $userFolder
     * @param string $token
     * @param integer $lifetime
     * @return bool, true if token is valid
     */
    public function isValidCsrfToken( $userFolder, $token = '', $lifetime = 86400, $site= null ){
        $token = $this->validToken($token);
        
        if( false === $token) return false;
        if ( defined('REDIS_CONFIG') ){
            return $this->isValidTokenByRedisAdapter(
                $site ?? APPLICATION_SITE, "{$userFolder}_{$token}", $lifetime
            );
        }
        else{
            $filePath = self::generalCsrkPath($userFolder, $token);
            // --- Valid token
            if ( file_exists($filePath) ){
    			if( $this->_useOneTime ){
    				$lastTime = filemtime($filePath);
    				@unlink($filePath);
    				
    				if ( is_numeric($lastTime) 
    				    && $lastTime <= (time() - $lifetime )
				    )
					   return false;
    			}
    			
                return true;
            }
        }
        return false;
    }
    
    /**
     * Clear CSRF token
     * @param string $userFolder
     * @param string $token
     * @return bool true on success or false on failure 
     */
    public function clearCsrfToken( $userFolder, $token = null, $site= null ){
        $token = $this->validToken($token);
        
        if( false === $token) return false;
        
        if ( defined('REDIS_CONFIG') ){
            return $this->clearTokenByRedisAdapter(
                $site ?? APPLICATION_SITE, "{$userFolder}_{$token}"
            );
        }else{
            $filePath = realpath(self::generalCsrkPath($userFolder, $token));
            if ( $filePath && file_exists($filePath) ){
                return @unlink($filePath);
            }
        }
        return false;
    }
}