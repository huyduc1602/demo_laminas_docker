<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageUpdate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageUpdate.php 2014-18-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Update" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ManageUpdate
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageUpdate extends AbstractHelper {
	/**
	 * Create button "Update" on the gridview
	 * 
	 * @param string $href Url
	 * @param string $title Title
	 * @param array $attribs        	
	 */
	public function __invoke($href, $title = "", $attribs = []) {
	    
		// TH: khong co title.
		if (trim ( $title ?? '' ) == "") {
			$title = $this->view->translate ( "Cập nhật" );
		}
		// Them vao mang attribs.
		$attribs ["href"] = $href;
		$attribs ["title"] = $title;
		$attribs['aclass'] = 'btn-success ' . ($attribs['aclass'] ?? '');
		// Icon cua button.
		$icon = "fa-pencil-square-o";
		
		// Khoi tao manageIcon.
		return $this->view->manageIcon($icon, $attribs);
	}
}
?>