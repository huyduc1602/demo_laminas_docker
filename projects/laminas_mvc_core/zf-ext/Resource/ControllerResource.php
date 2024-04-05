<?php
namespace Zf\Ext\Resource;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class ControllerResource extends AbstractPlugin{
	// File upload của người dùng
	const TYPE_UPLOAD_USER = 'UPLOAD_USER';
	// File upload của hệ thống
	const TYPE_UPLOAD_SYSTEM = 'UPLOAD_SYSTEM';
	// File cache
	const TYPE_CACHE = 'CACHE';
	//
	protected $_publicPath;
	//
	protected $_skinDirectory;
	//
	protected $_uploadDirectory;
	//
	protected $_cacheDirectory;
	//
	protected $_siteName;
	//
	protected $_types;
	//
	protected static $_instance;
	public function setPublicPath($publicPath) {
		$this->_publicPath = $publicPath;
	}
	public function getPublicPath() {
		return $this->_publicPath;
	}
	public function setSkinDirectory($skin) {
		$this->_skinDirectory = $skin;
	}
	public function getSkinDirectory() {
		return $this->_skinDirectory;
	}
	public function setUploadDirectory($uploadDirectory) {
		$this->_uploadDirectory = $uploadDirectory;
	}
	public function getUploadDirectory() {
		return $this->_uploadDirectory;
	}
	public function setCacheDirectory($uploadDirectory) {
		$this->_cacheDirectory = $uploadDirectory;
	}
	public function getCacheDirectory() {
		return $this->_cacheDirectory;
	}
	public function setSiteName($siteName) {
		$this->_siteName = $siteName;
	}
	public function getSiteName() {
		return $this->_siteName;
	}
	public function setTypes($types) {
		$this->_types = ( array ) $types;
	}
	public function getTypes() {
		return $this->_types;
	}
	private function getType($type) {
		$types = $this->getTypes ();
		if (is_null ( $types [$type] )) {
			throw new \Exception ( "Don`t supported type: {$type}", 500 );
		}
		return $types [$type];
	}
	
	public static function getInstance( $opts = array() ) {
	    if (null === self::$_instance) {
	         
	        self::$_instance = new self ();
	    
	        if ( $opts && is_array($opts)){
	            foreach ($opts as $key => $val){
	                self::$_instance->{"set" . ucfirst($key)}($val);
	            }
	        }
	    }
		return self::$_instance;
	}
	
	/**
	 * Lấy đường dẫn của file
	 * 
	 * @param string $type
	 *        	thư mục (upload, cache, file)
	 * @param bool $getSystemPath
	 *        	đường dẫn hệ thống hoặc tương đối
	 * @param bool $useSite
	 *        	đường dẫn theo tên site
	 * @return string
	 */
	private function getPathOfType($type, $getSystemPath, $useSite) {
		// Kiểm tra lấy theo đường dẫn hệ thống hay tương đối
		$publicPath = $getSystemPath ? $this->_publicPath : "";
		
		switch ($type) {
			// TH: Lấy file upload của người dùng
			case self::TYPE_UPLOAD_USER :
				return $publicPath . '/' . $this->getUploadDirectory();
				break;
			
			// TH: Lấy file upload của hệ thống
			case self::TYPE_UPLOAD_SYSTEM :
				return realpath ( $publicPath . '/' . $this->getUploadDirectory () . '/..' );
				break;
			
			// TH: Lấy file cache
			case self::TYPE_CACHE :
				return realpath ( $publicPath . '/' . $this->getCacheDirectory () );
				break;
			
			// TH: Mặc định
			default :
			    $opts = [
			         '',
			         'skin' => $this->getSkinDirectory (),
			         'site' => $this->getSiteName (),
			         'type' => $this->getType ( $type )
		        ];
			    if (!$useSite) unset($opts['site']);
				return implode('/', $opts);
				break;
		}
	}
	private function normalizePath($pathDirectory) {
		return str_replace ( array (
				"\\",
				"\\\\",
				"//",
				"///" 
		), '/', $pathDirectory );
	}
	
	/**
	 * Lấy đường dẫn files
	 *
	 * @param string|array $filenames
	 *        	Tên file
	 * @param string $type
	 *        	mục (upload, cache, file)
	 * @param bool $getSystemPath
	 *        	đường dẫn hệ thống hoặc tương đối
	 * @param bool $useSite
	 *        	theo tên site không
	 *        	
	 * @return string|array
	 */
	public function files($filenames, $type = "", $getSystemPath = false, $useSite = false) {
		if (is_string ( $filenames )) {
			if ((strpos ( $filenames, 'http://' ) === 0) || strpos ( $filenames, 'https://' ) === 0) {
				return $filenames;
			}
			return $this->normalizePath ( $this->getPathOfType ( $type, $getSystemPath, $useSite ) . ($filenames ? "/{$filenames}" : '') );
		}
		if (is_array ( $filenames )) {
			$result = array ();
			foreach ( $filenames as $value ) {
				$result [] = $this->files ( $value, $type, $getSystemPath, $useSite );
			}
			return $result;
		}
	}
	
	/**
	 * Lấy file upload của người dùng
	 * 
	 * @param string $filenames
	 *        	file
	 * @param bool $getSystemPath
	 *        	theo đường dẫn hệ thống hay tương đối
	 * @return string|array
	 */
	public function uploadFiles($filenames, $getSystemPath = false) {
		return $this->files ( $filenames, self::TYPE_UPLOAD_USER, $getSystemPath );
	}
	
	/**
	 * Lấy file upload của hệ thống
	 * 
	 * @param string $filenames
	 *        	file
	 * @param bool $getSystemPath
	 *        	theo đường dẫn hệ thống hay tương đối
	 * @return string|array
	 */
	public function uploadSystemFiles($filenames, $getSystemPath = false) {
		return $this->files ( $filenames, self::TYPE_UPLOAD_SYSTEM, $getSystemPath );
	}
	
	/**
	 * Lấy file cache của hệ thống
	 * 
	 * @param string $filenames
	 *        	file
	 * @param bool $getSystemPath
	 *        	theo đường dẫn hệ thống hay tương đối
	 * @return string|array
	 */
	public function cacheFiles($filenames, $getSystemPath = false) {
		return $this->files ( $filenames, self::TYPE_CACHE, $getSystemPath );
	}
}
?>