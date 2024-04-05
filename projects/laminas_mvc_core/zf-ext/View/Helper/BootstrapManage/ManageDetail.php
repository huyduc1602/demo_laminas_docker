<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ManageDetail
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageDetail.php 2014-16-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapManage\ManageIcon;

/**
 * Create a button "Detail" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ManageDetail
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageDetail extends AbstractHelper {
	/**
	 * Create button "Detail" on the gridview
	 * 
	 * @param string $href
	 *        	Url
	 * @param string $title
	 *        	Title
	 * @param array $attribs        	
	 */
	public function __invoke($href, $title = null, $attribs = []) {
		// TH: khong co title.
	    $title = $title ?? $this->view->translate('Xem chi tiết');
		// Them vao mang attribs.
		$attribs ["href"]  = $href;
		$attribs ["title"] = $title;
		$attribs['aclass'] = 'btn-info ' . ($attribs['aclass'] ?? '');
		// Icon cua button.
		$icon = 'fa-info-circle';
		
		// Khoi tao manageIcon.
		return $this->view->manageIcon($icon, $attribs);
	}
}
?>