<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarIcon.php 2014-18-01
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;
use Laminas\View\Helper\AbstractHelper;

/**
 * Create a "Button" on the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarIcon extends AbstractHelper {
	// Chuoi html de render button.
	protected $_formatIcon = '<li>
			<a class="__aclass__" href="__href__" title="__title__" onclick="__onclick__" target="__target__" __attributes__>
				<span class="fa __sclass__"></span>&nbsp;__label__</a>
		</li>';

    protected $content = '';
	/**
	 * Tao button tren thanh toolbar.
	 * 
	 * @param string $icon
	 *        	Icon cua button
	 * @param array $attribs
	 *        	Tham so tuy chon
	 *        	[+] aclass [+] href [+] onclick
	 *        	[+] target [+] sclass [+] label
	 * @return string The element XHTML
	 */
	public function __invoke($icon, $attribs = []) {
	    
		// TH: Khong render button.
		if ($attribs ['href'] == '#') {
			return '';
		}
		// Chuoi option cua button.
		$attributes = '';
		// Duyet vong lap.
		foreach ( $attribs as $key => $val ) {
			switch ($key) {
				case 'aclass' :
				case 'href' :
				case 'title' :
				case 'onclick' :
				case 'target' :
				case 'sclass' :
				case 'label' :
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
		// if(trim($attribs["href"]) == "") {
		// $attribs["href"] = "javascript:void(0);";
		// }
		// Render button.
		return $this->content = str_replace ( array (
				'__aclass__',
				'__href__',
				'__title__',
				'__onclick__',
				'__target__',
				'__sclass__',
				'__label__',
				'__attributes__' 
		), array (
				$attribs ['aclass'] ?? '',
				$attribs ['href'] ?? '',
				$attribs ['title'] ?? '',
				$attribs ['onclick'] ?? '',
				$attribs ['target'] ?? '',
				$icon,
				$attribs ['label'] ?? '',
				$attributes 
		), $this->_formatIcon );
	}
	
	public function toString() {
	    return $this->__toString ();
	}
}
?>