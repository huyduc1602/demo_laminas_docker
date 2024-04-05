<?php
use \Eventviva\ImageResize;
use \Models\Utilities\UserUpload;

error_reporting(E_ALL & ~ E_NOTICE & ~ E_WARNING);
// Time zone
date_default_timezone_set ( "Asia/Ho_Chi_Minh" );

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
$dirName = dirname ( __FILE__ );
chdir($dirName);

include realpath ( $dirName . '/../../../lib_zend_mvc/vendor/eventviva/php-image-resize/lib/ImageResize.php' );
include realpath ( $dirName . '/../../app/models/Utilities/UserUpload.php' );
try {
    $acceptMime = UserUpload::ACCEPT_IMG_TYPE;
    $sizeImgs = UserUpload::THUMB_IMG_SIZE;
    $base = $dirName . '/../uploads/';
    $url = $_GET['url'];
    $size = strtolower(escapeshellcmd($_GET['size']));
    $info = getimagesize($base . $url);
    // ---- Valid file
    if ( !$info || !$info['mime'] 
        || !in_array($info['mime'], $acceptMime)
        || !array_key_exists($size, $sizeImgs) ){
        header('"HTTP/1.0 404 Not Found', null, 404);
        die();
    }
    // ---- Parse file
    $pathFulls = preg_split('/[\/\\\]/', $url);
    $fileName = UserUpload::formatImgName([
        'img' => array_pop($pathFulls),
        'size' => $size
    ]); $baseFolder = UserUpload::FRONT_END_THUMB_PATH;
    
    $filePath = $dirName . "{$baseFolder}/" . implode('/', $pathFulls);
    
    header('Cache-Control:public, max-age=604800'); // 604800 = 7 days
    header('Content-Type:' . $info['mime']);
    
    if ( !file_exists($pathImg = $filePath . "/{$fileName}") ){
        
        if ( !is_dir($filePath) ){
            $imgCreatePath = $dirName . $baseFolder;
            
            foreach ($pathFulls as $dir){
                $imgCreatePath .= "/{$dir}";
                if ( !is_dir($imgCreatePath) )
                    @mkdir($imgCreatePath, 0755);
            }
        }
        
        $image = new ImageResize($base . $url);
        $width = $sizeImgs[$size];
        
        /* switch ($size){
            case 'xs': $width = 150; break;
            case 'xl': $width = 200; break;
            case 'sm': $width = 300; break;
            case 'sl': $width = 350; break;
            case 'md': $width = 400; break;
            case 'ml': $width = 450; break;
            case 'lg': $width = 550; break;
            default: $width = 780; break;
        } */
        
        $image->resizeToWidth($width);
        $image->save($pathImg);
        header('Last-Modified:' . date('D, d M Y H:i:s e', time()));
        echo $image->getImageAsString();
        exit();
    }else{
        header('Last-Modified:' . date('D, d M Y H:i:s e', filemtime($pathImg)));
        echo file_get_contents($pathImg);
        exit();
    }
}catch (\Exception $e){
    header('HTTP/1.0 404 Not Found', null, 404);
    die();
}
