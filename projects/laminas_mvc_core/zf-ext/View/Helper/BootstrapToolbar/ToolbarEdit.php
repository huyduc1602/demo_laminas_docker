<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage ToolbarDelete
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ToolbarDelete.php 2014-18-01
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
 * Create a button "Delete" on the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage ToolbarDelete
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ToolbarEdit extends AbstractHelper {
    /**
     * @var Laminas\Router\Http\RouteMatch
     */
    public $_matchRouter = null;
    public function __construct(Request $request, TreeRouteStack $router){
        $this->_matchRouter = $router->match($request);
    }
	/**
	 * Create button "Delete" on the toolbar
	 * 
	 * @param string $href Url
	 * @param string $label Label
	 * @param array $attribs        	
	 */
	public function __invoke($href = '', $label = null, $attribs = []) {
		// TH: khong co href.
	    if ( empty($href) || is_numeric($href) ) {
			$href = $this->view->zfUrl(
			    $this->_matchRouter->getMatchedRouteName(),
			    ['action' => 'edit', 'id' => $href]
		    );
		}
		
		// TH: khong co label.
	    $label = $label ?? $this->view->translate('Chỉnh sửa');
		
		// Them vao mang attribs.
		$attribs ['href'] = $href;
		$attribs ['label'] = $label;
		$attribs['aclass'] = ($attribs['aclass'] ?? '') .' toolbar-edit';
		
		// Khoi tao toolbarIcon.
		return (new ToolbarIcon ())->__invoke( 'fa-pencil', $attribs );
	}
}
?>