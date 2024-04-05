<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarInsert
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarIcon.php 2014-18-01
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
 * Create a button "Insert" on the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ToolbarInsert
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarInsert extends AbstractHelper {
    /**
     * @var Laminas\Router\Http\RouteMatch
     */
    public $_matchRouter = null;
    public function __construct(Request $request, TreeRouteStack $router){
        $this->_matchRouter = $router->match($request)->getMatchedRouteName();
    }
    
	/**
	 * Create a button 'Insert' on the toolbar
	 * 
	 * @param string $href
	 *        	Url cua button
	 * @param string $label
	 *        	Label cua button
	 * @param array $attribs        	
	 */
    public function __invoke($href = null, $label = null, $attribs = []) {
		// TH: khong co href.
        $href = $href ?? $this->view->zfUrl(
            $this->_matchRouter,
            ['action' => 'add']
        );
		// TH: khong co label.
	    $label = $label ?? $this->view->translate ( "Thêm" );
	    
		// Them vao mang attribs.
		$attribs ['href'] = $href;
		$attribs ['label'] = $label;
		// Icon cua button.
		$icon = 'fa-plus-circle';
		
		// Khoi tao toolbarIcon.
		return (new ToolbarIcon())->__invoke ( $icon, $attribs );
	}
}
?>