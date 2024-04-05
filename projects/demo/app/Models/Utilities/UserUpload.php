<?php
namespace Models\Utilities;
use \Zf\Ext\Utilities\Image;

class UserUpload
{
    /**
     * Create folder for user after register
     * @param string $userCode
     * @param string $site
     */
    public static function createUserFolder( $userCode = '', $site = APPLICATION_SITE ){
        // folder to save image
        $basePath = \Models\Entities\User::getUploadFolder();
        $dirs = [
            'user' => $userDir = implode(DIRECTORY_SEPARATOR, [
                $basePath,
                $userCode
            ]),
            'img' => implode(DIRECTORY_SEPARATOR, [
                $userDir,
                \Models\Entities\User::FOLDER_UPLOAD_IMG
            ]),
            'file' => implode(DIRECTORY_SEPARATOR, [
                $userDir,
                \Models\Entities\User::FOLDER_UPLOAD_FILE
            ]),
            /* 'order' => implode(DIRECTORY_SEPARATOR, [
                DATA_PATH,
                \Models\Entities\User::ORDER_FOLDER,
                $userCode
            ]),
            'token' => implode(DIRECTORY_SEPARATOR, [
                DATA_PATH,
                \Zf\Ext\Utilities\CsrfToken::CSRF_TOKEN_FOLDER,
                $site,
                $userCode
            ]) */
        ];
        foreach ( $dirs as $dir ){
            // -- create employer dir if not exist
            if ( !is_dir($dir) ) {
                @mkdir($dir, 0755, true);
                //@chmod($dir, 0755);
            }
        }
    }

    /**
     * Upload avatar
     *
     * @param $_FILE $file
     * @param string $userCode
     * @return Array
     */
    public static function delImage($file, $userCode)
    {
        // folder to save image
        $basePath = \Models\Entities\User::getUploadFolder();
        $userDir = implode(DIRECTORY_SEPARATOR, array(
            $basePath,
            $userCode
        ));
        $imgDir = implode(DIRECTORY_SEPARATOR, array(
            $userDir,
            \Models\Entities\User::FOLDER_UPLOAD_IMG,
            $file
        )); @unlink($imgDir);
        return true;
    }

    /**
     * General user upload folder
     * @param string $userCode
     * @return string
     */
    public static function getUserUploadFileFolder($userCode){
        return implode(DIRECTORY_SEPARATOR, [
            \Models\Entities\User::getUploadFolder(),
            $userCode,
            \Models\Entities\User::FOLDER_UPLOAD_FILE
        ]);
    }
    /**
     * Upload avatar
     *
     * @param file $file
     * @param integer $userId
     * @param integer $width
     * @param integer $height
     * @return array
     *  <p>name: string</p>
     *  <p>path: string, full path</p>
     */
    public static function file($file, $userId, $opts = ['filename' => ''] )
    {
        // File name
        $filename = ($opts['filename'] ? $opts['filename'] : $file['name']);

        // folder to save image
        $fileDir = self::getUserUploadFileFolder($userId);

        // -- create image dir if not exist
        if (! is_dir($fileDir)) {
            @mkdir($fileDir, 0755, true);
            @chmod($fileDir, 0755);
        }
        // -- Check duplicate filename
        $imgNameSrc = Image\Image::normalize(
            Image\Image::getInexistantFilename(implode(DIRECTORY_SEPARATOR, [
                $fileDir, $filename
            ]))
        );
        // -- Upload
        $success = @move_uploaded_file($file['tmp_name'], $imgNameSrc );

        if ( $success ){
            $names = preg_split('/[\/\\\]/', $imgNameSrc);
            return [
                'name' => array_pop($names),
                'path' => $fileDir
            ];
        }
        return false;
    }

    /**
     * Upload avatar
     *
     * @param file $file
     * @param string $userCode
     * @param integer $width
     * @param integer $height
     * @return false || array
     *  <p>name: string</p>
     *  <p>path: string, full path</p>
     */
    public static function image($file, $userCode, $opts = [ 'width' => 120, 'height' => 120, 'filename' => ''] )
    {
        // -- get Image info
        $imgInfo = new Image\FastImage($file['tmp_name']);
        $imgSize = $imgInfo->getSize();
        $imgType = $imgInfo->getType();
        $imgInfo->close();
        if ( !$imgType ) return false;

        // File name
        $filename = ($opts['filename'] ? $opts['filename'] : $file['name']);

        // folder to save image
        $basePath = \Models\Entities\User::getUploadFolder();

        $userDir = implode(DIRECTORY_SEPARATOR, [
            $basePath,
            $userCode
        ]);
        $imgDir = implode(DIRECTORY_SEPARATOR, [
            $userDir,
            \Models\Entities\User::FOLDER_UPLOAD_IMG
        ]);

        // -- create employer dir if not exist
        if (! is_dir($userDir)) {
            @mkdir($userDir, 0755, true);
            //@chmod($userDir, 0755);
        }
        // -- create image dir if not exist
        if (! is_dir($imgDir)) {
            @mkdir($imgDir, 0755, true);
            //@chmod($imgDir, 0755);
        }

        // derectory separator
        $directorySepar = DIRECTORY_SEPARATOR;
        $imgNameSrc = Image\Image::normalize(
            Image\Image::getInexistantFilename($imgDir . DIRECTORY_SEPARATOR . $filename)
        );$name = str_replace(' ', '_', basename($imgNameSrc));

        $imgNameSrc = implode(DIRECTORY_SEPARATOR, [
            $imgDir, $name
        ]);

        $driver = Image\Image::getDriver(['bd']);

        $img = Image\Image::factory($driver, $file['tmp_name']);

        if ( !$img->initError ) {
            $width = $opts['width'] ? $opts['width'] : 120;
            $height = $opts['height'] ? $opts['height'] : 120;
            // -- resize to 800x600
            if ( (isset($opts['is_real']) && true === $opts['is_real'])
                || ($imgSize[0] < $width && $imgSize[1] < $height) ) {
                $rs = @move_uploaded_file($file['tmp_name'], $imgNameSrc );
            }else {
              // -- option to create image
              $options = [ 'file' => $imgNameSrc ];
              if ($imgType == "gif") $imgType = "jpeg";
              if ($imgType == "jpeg") $options['quality'] = 95;
                  // -- Resize
              $img->resizeFit($width, $height);
              // -- Save image
              $rs = $img->output($imgType, $options);
            }
        }else return false;

        unset($file);
        if ( $rs && file_exists($imgNameSrc) ){
            return [
                'name' => $name, 'path' => $imgDir
            ];
        }
        return false;
    }

    /**
     * Folder thumbnail
     * @var string
     */
    const FRONT_END_THUMB_PATH = '/resize';
    /**
     * Accept mime type
     * @var array
     */
    const ACCEPT_IMG_TYPE = ['image/x-icon', 'image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
    /**
     * Size of thumbnail
     * @var array
     */
    const THUMB_IMG_SIZE = ['xxs' => 100, 'xs' => 150, 'xl' => 200, 'sm' => 300, 'sl' => 350, 'md' => 400, 'ml' => 450, 'lg' => 550];
    /**
     * @return string
     */
    public static function formatImgName( $opts = [] ){
        return preg_replace('/\.?(jpg|jpeg|png|icon|bmp|gif)$/i', "_w{$opts['size']}.$1", $opts['img']);
    }
    /**
     * @param array $opts
     * @return string
    */
    public static function formatImgSize($opts = []) {
        $img = self::formatImgName($opts);
        return $opts['baseUrl'] . self::FRONT_END_THUMB_PATH . "/{$img}";
    }

    /**
     * Delete thumbnail
     * @param string $path
     */
    public static function delThumb( $path = '') {
        $basePath = implode(DIRECTORY_SEPARATOR, [
            PUBLIC_PATH, 'uploads'
        ]);
        if (!file_exists($filePath = $basePath . DIRECTORY_SEPARATOR . $path )) return false;

        // --- parse and delete file
        $imgsSize = self::THUMB_IMG_SIZE;
        @unlink($filePath);

        $basePath .= '/img';
        foreach ($imgsSize as $size){
            $img = self::formatImgSize([
                'baseUrl' => $basePath,
                'size'    => $size,
                'img'     => $path
            ]);
            @unlink($img);
        }
        return true;
    }

    /**
     * Get file info
     * @param string $token
     * @return array
     */
    public static function parseFileInfoByToken( $token = '') {
        $token = substr($token, 1, strlen($token) - 2);
        $token = base64_decode(strrev($token));
        return @json_decode(
            gzuncompress(substr($token, 1, strlen($token) - 2)), true
        );
    }

    /**
     * Encode token
     * @param array $data
     * @return string
     */
    public static function createFileToken( $data = [] ) {
        $json = str_replace('=', '', base64_encode(
            chr(rand(65, 89)) . gzcompress(json_encode($data)) . chr(rand(65, 89))
        ));

        return chr(rand(65, 89))
        . strrev($json)
        . chr(rand(65, 89));
    }

    /**
     * Create subdomain to load file
     * @param string $serverUrl
     * @param string $subDomain
     * @return string
     */
    public static function getSubDomainLink( $serverUrl = '', $subDomain = '' ){
        // Sub domain
        if ( preg_match('/(([a-z0-9]+\.)(askbe\.).*)/i', $serverUrl, $matchs) ){
            // Server test
            if ( $matchs[2] == 'test.' )
                return rtrim($serverUrl, '/') . "/{$subDomain}";
            // Other server: life, ...
            return str_replace($matchs[2], "{$subDomain}.", $serverUrl);
        }
        // Release server
        return str_replace('https://', "https://{$subDomain}.", $serverUrl);
    }
}
