<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarSaveAndNew
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarSaveAndNew.php 2014-18-01
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapToolbar\ToolbarIcon;

/**
 * Create a button "SaveAndNew" on the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ToolbarSaveAndNew
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarSaveAndNew extends AbstractHelper {
	/**
	 * Create button "SaveAndNew" on the toolbar
	 * 
	 * @param string $href
	 *        	Url
	 * @param string $label
	 *        	Label
	 * @param array $attribs        	
	 */
    public function __invoke($href = null, $label = null, $attribs = []) {
		// TH: khong co href.
        $href = $href ?? 'javascript:void(0);';
		
		// TH: khong co label.
	    $label = $label ?? $this->view->translate ( "Lưu & Tạo mới" );
		
		// TH: khong co onclick.
		if ( empty($attribs['onclick']) ) {
	        $attribs ["onclick"] = "jQuery('#adminForm').find('#adminAction').val('save-and-new').end().submit(); void(0);";
	    }
		// Them vao mang attribs.
		$attribs ['href'] = $href;
		$attribs ['label']= $label;
		
		// Icon cua button.
		$icon = 'fa-calendar-plus-o';
		
		// Khoi tao toolbarIcon.
		return (new ToolbarIcon ())->__invoke ( $icon, $attribs );
	}
}
?>