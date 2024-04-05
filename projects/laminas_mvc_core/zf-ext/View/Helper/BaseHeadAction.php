<?php
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;
/**
 * Helper for making easy links and getting urls that depend on the routes and router
 */
class BaseHeadAction extends AbstractHelper {
    
    /**
     * @var array
     */
    protected $rqParams = [];
    public function __construct(ContainerInterface $container){
        if ( $container->has('router') && 
            $container->has('request')
        ){
            $this->rqParams = $container->get('router')->match(
                $container->get('request')
            )->getParams();
            
            $this->parseCtrlModule($this->rqParams['controller'] ?? '');
        }
    }
    /**
     * Get Controller, Module from controller class name
     * @param string $str
     */
    protected function parseCtrlModule($str = ''){
        $str = explode('\\', $str);
        $this->rqParams['module'] = array_shift($str) ?? '';
        $this->rqParams['controller'] = str_replace('Controller', '', array_pop($str) ?? '');
        
        foreach ($this->rqParams as $key => $val){
            $this->rqParams[$key] = $this->convertCamelToSnake($val);
        }
    }
    
    /**
     * Convert camel case to snake case
     * @param string $str
     * @return string
     */
    protected function convertCamelToSnake($str = ''){
        $str = lcfirst($str);
        return strtolower(preg_replace(
            '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', 
            '-', 
            $str
        ));
    }
    
    /**
     * Get request params
     */
    protected function getRqParams($key, $options = []){
        return $options[$key] ?? $this->rqParams[$key] ?? null;
    }
    
    /**
     * Lay noi dung file
     * @param string $content
     * @param array $params
     * @param array $options {action, controller, module}
     *
     * @return string
     */
    public function getFileContent($content = null, $params = [], $options = []) {
        $fileContent = '';
        if ( is_string($content) ){
            $fileContent = $content;
        }else {
            
            // Get module, controller, action
            $moduleName = $this->getRqParams('module', $options) ?? 'application';
            
            $controllerName = $this->getRqParams('controller', $options) ?? 'index';
            
            $actionName = $this->getRqParams('action', $options) ?? 'index';
            
            $callerClss = get_called_class();
            
            $site = empty($options['is_common'] ?? false) 
                    ? (defined ( 'APPLICATION_SITE' ) ? APPLICATION_SITE : '')
                    : 'shareModules';
            // Get file path
            $base = str_replace(' ', '', ucwords( str_replace('-', ' ', $moduleName)));
            $arrPath = array_filter([
                APPLICATION_PATH, $site, $base,
                $callerClss::_folderView, $callerClss::_folderAssets,
                $controllerName, $callerClss::_folderSrc, $actionName
            ]);
            
            $filePath = realpath ( implode('/', $arrPath) . $callerClss::_suffixFile );
            
            // Get file content
            if ($filePath) {
                // Return
                $fileContent = file_get_contents ( $filePath );
            }
        }
        
        // Bind data
        if ( !empty($params) ) {
            $fileContent = str_replace ( 
                array_keys ($params), 
                array_values ($params), 
                $fileContent
            );
        }
        
        return $fileContent;
    }
}
?>