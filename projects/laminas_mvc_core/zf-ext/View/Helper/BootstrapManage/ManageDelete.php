<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageDelete
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageDelete.php 2014-18-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Delete" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ManageDelete
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageDelete extends AbstractHelper {
	/**
	 * Create button "Delete" on the gridview
	 * 
	 * @param string $href
	 *        	Url
	 * @param string $title
	 *        	Title
	 * @param array $attribs        	
	 */
	public function __invoke($href, $title = null, $attribs = []) {
		// TH: khong co title.
		$title = $title ?? $this->view->translate ( "Xóa" );
		
		// Them vao mang attribs.
		$attribs ["href"]  = $href;
		$attribs ["title"] = $title;
		$attribs['aclass'] = 'btn-warning manage-delete ' . ($attribs['aclass'] ??'');
		$attribs ["data-confirm"] = $attribs ["data-confirm"] ?? $this->view->translate('Bạn có chắc muốn xóa dòng này?');
		// Icon cua button.
		$icon = 'fa-minus-circle';
		
		// Khoi tao manageIcon.
		return $this->view->manageIcon($icon, $attribs);
	}
}
?>