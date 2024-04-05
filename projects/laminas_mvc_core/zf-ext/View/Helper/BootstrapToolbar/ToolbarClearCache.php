<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarSave
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarSave.php 2014-18-01
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
 * Create a button "ClearCache" on the toolbar
 *
 */
class ToolbarClearCache extends AbstractHelper {
    /**
     * @var Laminas\Router\Http\RouteMatch
     */
    public $_matchRouter = null;
    public function __construct(Request $request, TreeRouteStack $router){
        $this->_matchRouter = $router->match($request)->getMatchedRouteName();
    }
	/**
	 * Create button "ClearCache" on the toolbar
	 * 
	 * @param string $href
	 *        	Url
	 * @param string $label
	 *        	Label
	 * @param array $attribs        	
	 */
    public function __invoke($href = null, $label = null, $attribs = []) {
	    
        $href = $href ?? $this->view->zfUrl(
            $this->_matchRouter,
            ['action' => 'clear-cache']
        );
        
	    $label = $label ?? $this->view->translate ( 'Clear cache' );
		
		$attribs ['href']  = $href;
		$attribs ['label'] = $label;
		return (new ToolbarIcon ())->__invoke ( 'fa fa-refresh', $attribs );
	}
}
?>