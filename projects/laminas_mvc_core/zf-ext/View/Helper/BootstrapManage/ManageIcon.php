<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapManage
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageIcon.php 2014-16-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\View\Helper\AbstractHelper;

/**
 * Create a "Button" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapManage
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageIcon extends AbstractHelper {
	// Chuoi html de render button.
	private $_formatIcon = '<a class="btn btn-sm __aclass__" href="__href__" title="__title__" onclick="__onclick__" target="__target__" __attributes__>
			<span class="fa __sclass__"></span>
		</a>';
	
	/**
	 * Tao button tren gridview.
	 * 
	 * @param string $icon
	 *        	Icon cua button
	 * @param array $attribs
	 *        	Tham so tuy chon
	 *        	[+] aclass [+] href [+] onclick
	 *        	[+] target [+] sclass
	 * @return string The element XHTML
	 */
	public function __invoke($icon, $attribs = []) {
	    
		// TH: Khong render button.
		if ($attribs ["href"] == '#') {
			return '';
		}
		// Chuoi option cua button.
		$attributes = "";
		// Duyet vong lap.
		foreach ( $attribs as $key => $val ) {
			switch ($key) {
				case "aclass" :
				case "href" :
				case "title" :
				case "onclick" :
				case "target" :
				case "sclass" :
					$key = htmlspecialchars ( $key );
					$val = htmlspecialchars ( $val );
					$attribs [$key] = $val;
					break;
				
				default :
					$attributes .= htmlspecialchars ( $key ) . "=\"" . htmlspecialchars ( $val ) . "\"";
					break;
			}
		}
		
		// TH: khong co href.
		if (trim ( $attribs ["href"] ) == "") {
			$attribs ["href"] = "javascript:void(0);";
		}
		// Render button.
		return str_replace ( [
				"__aclass__",
				"__href__",
				"__title__",
				"__onclick__",
				"__target__",
				"__sclass__",
				"__attributes__" 
		], [
				$attribs ["aclass"] ?? '',
				$attribs ["href"] ?? '',
				$attribs ["title"] ?? '',
				$attribs ["onclick"] ?? '',
				$attribs ["target"] ?? '',
				$icon,
				$attributes 
		], $this->_formatIcon );
	}
	
	/**
	 * Ham ho tro render button.
	 */
	public function __toString() {
		return ( string ) $this;
	}
	
	/**
	 * Ham ho tro render button.
	 */
	public function toString() {
	    return $this->__toString();
	}
}
?>