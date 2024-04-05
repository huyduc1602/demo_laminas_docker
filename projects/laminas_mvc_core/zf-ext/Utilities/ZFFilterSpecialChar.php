<?php
namespace Zf\Ext\Utilities;
use Laminas\Filter\FilterInterface;

class ZFFilterSpecialChar implements FilterInterface {
	protected $arrSpecialChar = array ("`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "+", "(", ")", "|", "{", "}", "[", "]", "\\", "'", "\"", "/", ",", ".");
	
	/*
	 * (non-PHPdoc)
	 * @see Zend_Filter_Interface::filter()
	 */
	public function filter($value) {
		return str_replace ( $this->arrSpecialChar, "", $value );
	}
}

?>