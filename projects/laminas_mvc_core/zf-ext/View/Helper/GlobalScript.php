<?php

 namespace Zf\Ext\View\Helper;

 use Laminas\View\Helper\AbstractHelper;
 
 class GlobalScript extends AbstractHelper {
    
	/**
	 * Return global Script object
	 *
	 * @param  string $name 
	 * @return Zend_View_Helper_Placeholder
	 */
	public function __invoke($name = "globalScript") {
	    $items = $this->getView()->placeholder($name);
	    
	    $this->getView()->plugin('placeholder')->deleteContainer($name);
	    
	    $scripts = '';
	    foreach ($items as $item ){
	        $scripts .= implode(PHP_EOL, [
	            '<script type="text/javascript">',
	            '//<!--', $item, '//-->',
	            '</script>'
	        ]);
	    }
	    
	    return $scripts;
	}
}