<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageChangeStatus
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageChangeStatus.php 2014-19-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Status" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ManageChangeStatus
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageChangeStatus extends AbstractHelper {
	/**
	 * Create button "Status" on the gridview
	 * 
	 * @param string $href
	 *        	Đường dẫn đến link thay đổi trạng thái
	 * @param string $title
	 *        	Tiêu đề nút
	 * @param array $attribs
	 *        	Thuộc tính html
	 *        	
	 * @return string
	 */
	public function __invoke($href, $status, $attribs = []) {
		// Trường hợp: Kích hoạt
		if ($status) {
			$icon = 'fa-check';
			$title = $this->view->translate ( "Bỏ kích hoạt" );
		} 		
		// Trường hợp: Không kích hoạt
		else {
			$icon = 'fa-circle';
			$title = $this->view->translate ( "Kích hoạt" );
		}
		
		// Tạo thuộc tính cho nút
		$attribs ['href'] = $href;
		$attribs ['title'] = $title;
		$attribs ['aclass'] = 'change-status ' . ($attribs ['aclass'] ??'');
		
		// Khoi tao manageIcon.
		return $this->view->manageIcon($icon, $attribs);
	}
}
?>