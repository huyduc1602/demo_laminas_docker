<?php
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for making easy links and getting urls that depend on the routes and router
 *
 * @uses Laminas\View\Helper\AbstractHelper
 * @package ZF_View
 * @subpackage Helper
 */
class HeadStyleAction extends BaseHeadAction {
    
	/**
	 * Hang so dinh dang file
	 * 
	 * @var string
	 */
	const _suffixFile = '.css';
	/**
	 * Hang so ten folder chua file
	 * 
	 * @var string
	 */
	const _folderSrc = 'css';
	
	/**
	 * Hang so ten view folder
	 *
	 * @var string
	 */
	const _folderView = 'view';
	
	/**
	 * Hang so ten assets folder
	 *
	 * @var string
	 */
	const _folderAssets = 'assets';
	
	protected static $cssMiner = null;
	/**
	 * Lay noi dung file .
	 * css
	 * 
	 * @param Zend_Controller_Request_Abstract || string $request        	
	 * @param array $params        	
	 * @param array $options        	
	 * @return mixed
	 */
	public function __invoke( $request, $params = [], $options = []) {
	    
	    $fileContent = $this->getFileContent($request, $params, $options);
	    
	    // Get resource content only
	    if ( true === ($options ['getContent'] ?? false)) {
	        return $fileContent;
	    }
		
		// -- minify
		/* if ( !class_exists('CSSmin') ) require LIBRARY_PATH .'/../MinifyHtml/lib/Minify/CSSmin.php';
		
		if ( empty(self::$cssMiner) ){
		    self::$cssMiner = new \CSSmin();
		}*/
		
		// Append to layout
		$this->view->headStyle()->appendStyle ( 
		    $fileContent, //self::$cssMiner->run($fileContent), 
		    ['media' => 'all'], 
		    ['getContent' => true]
		);
	}
}
?>