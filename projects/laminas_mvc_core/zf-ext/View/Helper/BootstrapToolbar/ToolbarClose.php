<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarClose
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarClose.php 2014-18-01
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapToolbar;
use Laminas\View\Helper\AbstractHelper;
use Zf\Ext\View\Helper\BootstrapToolbar\ToolbarIcon;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Router\Http\TreeRouteStack;
/**
 * Create a button "Close" on the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ToolbarClose
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarClose extends AbstractHelper {
    /**
     * @var Laminas\Router\Http\RouteMatch
     */
    public $_matchRouter = null;
    public function __construct(Request $request, TreeRouteStack $router){
        $this->_matchRouter = $router->match($request);
    }
    
	/**
	 * Create button "Close" on the toolbar
	 * 
	 * @param string $href
	 *        	Url
	 * @param string $label
	 *        	Label
	 * @param array $attribs        	
	 */
    public function __invoke($href = null, $label = null, $attribs = []) {
		// TH: khong co label.
        $label = $label ?? $this->view->translate ( "Đóng" );
        $href = $href ?? $this->view->zfUrl(
            $this->_matchRouter->getMatchedRouteName(),
            ['action' => 'index'], ['useOldQuery' => true]
        );
		
		// Them vao mang attribs.
		$attribs ['href'] = $href;
		$attribs ['label']= $label;
		// Icon cua button.
		$icon = 'fa-times';
		
		// Khoi tao toolbarIcon.
		return (new ToolbarIcon ())->__invoke($icon, $attribs);
	}
}
?>