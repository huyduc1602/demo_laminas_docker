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
class GlobalScriptAction extends AbstractHelper {
    protected static $itemCount = 0;
    
	/**
	 * Lay noi dung file .
	 * js
	 * 
	 * @param string $scriptContent        	
	 * @param array $name        	
	 * @return mixed
	 */
	public function __invoke($scriptContent, $name = 'globalScript' ) {
	    
	    $this->getView()->placeholder($name)->{self::$itemCount} = $scriptContent;
	    
        self::$itemCount ++;
	}
}
?>