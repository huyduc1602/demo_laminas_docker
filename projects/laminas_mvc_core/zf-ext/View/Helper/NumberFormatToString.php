<?php
/**
* @category   ZF
* @package    ZF_View_Helper
* @subpackage BootstrapToolbar
* @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
* @version    $Id: BootstrapToolbar.php 2014-20-01
* @license    http://framework.zend.com/license/new-bsd     New BSD License
*/

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\AbstractHelper;

/**
 * Generates a "button" element into the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class NumberFormatToString extends AbstractHelper {
	/**
	 * @return string
	 */
	public function __invoke( $number = 0 ) {
		// Return.
		if ( $number < 10000 )
		     return $number;
		
		
		if ( $number >= 100000000000 )
		    return substr($number, 0, 3) . 'B';
		if ( $number >= 10000000000 )
		    return substr($number, 0, 2) . 'B';
		if ( $number >= 1000000000 )
		    return substr($number, 0, 1) . 'B';
		
		if ( $number >= 100000000 )
		    return substr($number, 0, 3) . 'M';
		if ( $number >= 10000000 )
		    return substr($number, 0, 2) . 'M';
		if ( $number >= 1000000 )
		    return substr($number, 0, 1) . 'M';
		
		if ( $number > 100000 )
		    return substr($number, 0, 3) . 'K';
		if ( $number >= 10000 )
		  return substr($number, 0, 2) . 'K';
	}
}

?>