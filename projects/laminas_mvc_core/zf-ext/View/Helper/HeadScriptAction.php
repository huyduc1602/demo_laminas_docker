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
class HeadScriptAction extends BaseHeadAction {
    
	/**
	 * Hang so dinh dang file
	 * 
	 * @var string
	 */
	const _suffixFile = '.js';
	/**
	 * Hang so ten folder chua file
	 * 
	 * @var string
	 */
	const _folderSrc = 'js';
	
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
	
	protected static $globalItemCount = 0;
	/**
	 * Lay noi dung file .
	 * js
	 * 
	 * @param Zend_Controller_Request_Abstract || string $request   	
	 * @param array $params        	
	 * @param array $options        	
	 * @return mixed
	 */
	public function __invoke( $request, $params = [], $options = [], $isPrepend = true, $type = null ) {
	    
	    $fileContent = $this->getFileContent($request, $params, $options);
	    
	    // Get resource content only
	    if ( true === ($options ['getContent'] ?? false)) {
	        return $fileContent;
	    }
	    
	    if ( empty($options['is_global'] ?? false) ){
    	    // prepend to layout
    		if ( true === $isPrepend )
    		    $this->getView()->inlinescript ()->prependScript ( 
        		    $fileContent, $type, 
        		    ['getContent' => true] 
    		    );
    		else // Append to layout
    		    $this->getView()->inlinescript ()->appendScript (
        	        $fileContent, $type, 
        	        ['getContent' => true] 
    	        );
	    }else{
	        $this->getView()
	        ->placeholder('globalScript')
	        ->{self::$globalItemCount} = $fileContent;
	        
	        self::$globalItemCount++;
	    }
	}
}
?>