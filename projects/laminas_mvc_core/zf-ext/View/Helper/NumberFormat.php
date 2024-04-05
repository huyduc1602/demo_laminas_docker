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
class NumberFormat extends AbstractHelper {
const SERVICE_ALIAS = 'zfNumberFormat';
    
    public function __invoke( $num = 0, $decimal = 4 ) {
        return preg_replace(
            '/([0-9\.\,])(\.{0,1}0{1,'.$decimal.'})$/',
            '$1', 
            number_format($num, $decimal)
        );
    }
}
?>