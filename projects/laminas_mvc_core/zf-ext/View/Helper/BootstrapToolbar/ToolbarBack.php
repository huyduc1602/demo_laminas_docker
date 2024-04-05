<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarBack
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarBack.php 2014-29-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapToolbar\ToolbarIcon;

/**
 * Create a button "Back" on the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ToolbarBack
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarBack extends AbstractHelper {
	/**
	 * Create button "Back" on the toolbar
	 * 
	 * @param string $href
	 *        	Url
	 * @param string $label
	 *        	Label
	 * @param array $attribs        	
	 */
    public function __invoke($href = null, $label = null, $attribs = []) {
		// TH: khong co label.
		$label = $label ?? $this->view->translate ( "Trở lại" );
		
	    if ( isset($_SERVER ['HTTP_REFERER']) ) {
	        $href = $href ?? $_SERVER ['HTTP_REFERER'];
		} else {
			$onclick = 'window.history.back()';
		}
		
		// Them vao mang attribs.
		$attribs ["href"]     = $href ?? '';
		$attribs ["label"]    = $label;
		$attribs ['onclick']  = $onclick ?? '';
		
		// Icon cua button.
		$icon = 'fa-arrow-left';
		
		// Khoi tao toolbarIcon.
		return (new ToolbarIcon ())->__invoke($icon, $attribs);
	}
}
?>