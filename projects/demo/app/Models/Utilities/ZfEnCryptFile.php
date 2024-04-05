<?php
namespace Models\Utilities;
use \Laminas\Math\Rand;

class ZfEnCryptFile{
    const RAND_CHAR = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const RAND_LENGTH = 16;

    /**
     * General random string with length
     * @param number $length
     * @return string
     */
    public static function getRandStr( $length = self::RAND_LENGTH ){
        $strRand = base_convert(time(), 10, 16); $randCount = strlen($strRand);
        if ( $randCount < $length ){
            $strRand.= Rand::getString(
                $length - $randCount,
                self::RAND_CHAR, true
            );
        }

        return $strRand;
    }

    /**
     * Encrypt password
     * @param string $str
     * @return string
     */
    public static function encryptPass( $str = '' ){
        return base64_encode(gzcompress($str, 9, ZLIB_ENCODING_DEFLATE));
    }

    /**
     * Upload file
     * @param integer $userId
     * @param array $file
     * @return string | false
     */
    public static function uploadFile( $userId, $file = [] ){
        // -- get file extension
        $ext = array_pop(explode('.', $file['name']));

        $upload = \Models\Utilities\UserUpload::file($file, $userId, [
            'filename' => self::getRandStr() . ".{$ext}"
        ]);
        if ( is_array($upload) ) return implode(DIRECTORY_SEPARATOR, [
            $upload['path'], $upload['name']
        ]);

        return false;
    }
    
    /**
     * Encrypt file 
     * @param string $file
     * @param string $toPath
     * @param string $fileExt
     * @param string $pass
     * @return array
     */
    public static function encryptFile($file, $toPath, $fileExt, $pass = null){
        $fileExt = $fileExt ?? 'zfen';
        // --- auto random password --------
        $originPass = $passKey = $pass;
        // -- save password to path
        $passFileName = self::getRandStr();
        // -- random password
        if ( false === $pass || null === $pass ) $originPass = $passKey = self::getRandStr();
        
        $passKey = self::encryptPass($passKey);
        $pass = implode(DIRECTORY_SEPARATOR, [ $toPath, "{$passFileName}.key" ]);
        $writeSuccess = @file_put_contents($pass, $passKey);
        if ( !$writeSuccess ) return false;
        
        // -- Encrypt save path
        $toPath = implode(DIRECTORY_SEPARATOR, [
            $toPath, ($fileName = self::getRandStr() . ".{$fileExt}")
        ]);
        
        $cmd = "openssl enc -e -aes-256-cbc -in {$file} -out {$toPath} -pass file:{$pass}";
        $resp = null;
        $rs = system($cmd, $resp );
        
        // -- Clear password
        @unlink($pass);
        
        return [
            'cmdResult' => $resp,
            'originPass'=> $originPass,
            'fileName'  => $fileName,
            'filePath'  => $file
        ];
    }
    
    /**
     * Encypt file from path
     * @param string $userId
     * @param string || array $file
     * <p>
     *  $file: string, full path
     *  or $file: array, upload file from post
     * </p>
     * @param string $toPath
     * <p>
     *  $toPath: path to save file after encrypt
     * </p>
     * @param string $pass
     * <p>
     *  $pass: pathname to password file for encrypt
     * </p>
     * @return false || array
     * <p>
     *  Return false if fail. Return array [ pass: string, name: string ] if success
     * </p>
     */
    public static function enCrypt( $userId, $file = '', $toPath = '', $pass = false, $fileExt = 'zfen', $autoFile = true ){
        // --- Case: Upload file from user
        if ( is_array($file) ){
            $file = self::uploadFile($userId, $file);
        }
        
        if ( !$file || !file_exists($file) ) return false;
        if ( !is_dir($toPath) ) return false;

        $resp = self::encryptFile($file, $toPath, $fileExt, $pass);

        if ( $autoFile ) @unlink($resp['filePath']);

        return [
            'password' => $resp['originPass'],
            'filename' => $resp['fileName'],
            'success' => (0 === $resp['cmdResult'])
        ];
    }

    /**
     * Decrypt file from path
     * @param integer $userId
     * @param string $file
     * <p>
     *  $file: full path
     * </p>
     * @param string $toPath
     * <p>
     *  $toPath: path to save file after encrypt
     * </p>
     * @param string $pass
     * <p>
     *  $pass: pathname to password file for decrypt
     * </p>
     * @param string $fileExt
     * <p>
     *  $fileExt: extension of file after decrypt
     * </p>
     * @return string
     * <p>
     *  Return false if fail. Return string path of file after decrypt if success
     * </p>
     */
    public static function deCrypt( $userId, $file = '', $toPath = '', $pass = false, $fileExt = 'png' ){
        $autoDel = false;

        // --- Case: Upload file from user
        if ( is_array($file) ){
            $autoDel = true;
            $file = self::uploadFile($userId, $file);
        }

        if ( false === $file || !file_exists($file) ) return false;
        if ( !is_dir($toPath) ) return false;
        if ( false === $pass || null === $pass ) return false;

        $fileExt = $fileExt ?? 'png';
        // --- auto random password --------
        $passKey = self::encryptPass($pass);
        // -- save password to path
        $passFileName = self::getRandStr();
        $pass = implode(DIRECTORY_SEPARATOR, [ $toPath, "{$passFileName}.key" ]);
        $writeSuccess = @file_put_contents($pass, $passKey);
        if ( !$writeSuccess ) return false;

        // ------ Decrypt path
        $toPath = implode(DIRECTORY_SEPARATOR, [
            $toPath, ($filename = self::getRandStr() . "_zfde.{$fileExt}")
        ]);

        $cmd = "openssl enc -d -aes-256-cbc -in {$file} -out {$toPath} -pass file:{$pass}";
        system($cmd, $resp); @unlink($pass);

        if ( $autoDel) @unlink($file);
        return [
            'filename' => $filename,
            'success' => (0 === $resp)
        ];
    }
}
