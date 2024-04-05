<?php
namespace Models\Utilities;

class AppUtilities
{
    /**
     * Create url with domain, uri and params
     * @param array $opts
     * @return string
     */
    public static function getUrl($opts){
        $query = '';
        if( empty($opts['query']) == false ){
            if ( is_array($opts['query']) )
                $query = '?' . http_build_query($opts['query']);
            elseif( is_string($opts['query']) ){
                $query = '?'. ltrim($opts['query'] , '?');
            }
        }

        return implode('/', array_merge([
            rtrim($opts['domain'], '/') ,
            ltrim($opts['uri'], '/')
        ])) . $query;
    }
    
    public static function deleteFiles($files){
        $rs = 0;
        foreach ($files as $file){
            if(empty($file) == false && file_exists($file)){
                unlink($file);
                $rs++;
            }
        }
        return $rs;
    }
    
    /**
     * Window delete files
     * @param string $path
     * @param string $pattern
     * @return integer
     */
    public static function winDelFile( $path, $pattern ){
        $fullPath = implode(DIRECTORY_SEPARATOR, [$path, $pattern]);
        @shell_exec("del /s /q {$fullPath}");
        return true;
    }


    /**
     * @param $path
     * @param $pattern
     * @return int
     */
    public  static  function delFilePattern($path, $pattern) {
        $path = escapeshellarg($path);
        if ( in_array(PHP_OS, ['WINNT', 'Windows']) )
            return static::winDelFile($path, $pattern);

        return static::linuxDelFile($path, $pattern);
    }


    /**
     * Linux delete files
     * @param string $path
     * @param string $pattern
     * @return integer
     */
    public static function linuxDelFile($path, $pattern ){
        $cmd = "find {$path} -type f -name \"{$pattern}\" -delete > /dev/null 2>&1";
        @shell_exec($cmd);
        return true;
    }
    
    /**
     * Delete folder
     * @param string $path
     * @return string
     */
    public static function delFolder( $path ){
        $path = escapeshellarg($path);
        if ( in_array(PHP_OS, ['WINNT', 'Windows']) ){
            ob_start();
            @system("rd /s /q {$path}", $rs);
            ob_get_clean();
        }
        else return @system("rm -rf {$path} > /dev/null 2>&1", $rs);
        return $rs === 0;
    }
    
    /**
     * Get files by folder
     * @param path $folder
     * @param string $sortTime
     * @return array
     */
    public static function getFiles( $folder, $sortTime = true ){
        $files = glob("$folder/*");
        if($sortTime){
            usort( $files, function( $a, $b ) { return filemtime($a) - filemtime($b); } );
        }
        return $files;
    }
    
    /**
     * Get authentication
     * @param \Laminas\Mvc\MvcEvent $event
     * return \stdClass
     */
    public static function getAuthen(\Laminas\Mvc\MvcEvent $event){
        $user = new \stdClass();
        try{
            $serviceManager= $event->getApplication()->getServiceManager();
            if ( $serviceManager->has(\Laminas\Authentication\AuthenticationService::class) ){
                $authService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
                if ( $authService->hasIdentity() ){
                    $user = $authService->getIdentity();
                }
                unset($authService);
            }
        }catch (\Throwable $e){}
        return $user;
    }
    
    /**
     * Save error log of system
     * @param \Laminas\Mvc\MvcEvent $event
     */
    public static function logErrors(\Laminas\Mvc\MvcEvent $event ){
        if ( $event->getParam('exception') ) {
            try {
                $serviceManager = $event->getApplication()->getServiceManager();
                
                $user = self::getAuthen($event);
                $request = $event->getApplication()->getRequest();
                $params = [
                    'post' => $request->getPost()->toArray(),
                    'get' => $request->getQuery()->toArray(),
                ];
                $pathApp = realpath( APPLICATION_PATH . '/../../');
                $pathLib = realpath( LIBRARY_PATH . '/../');
                $pathPub = realpath( PUBLIC_PATH);
                
                $error = $event->getParam('exception');
                $dataError = [
                    'error_user_id' => ($user ? $user->user_id ?? $user->admin_id ?? null : null),
                    'error_uri'     => (string)$request->getUri()->getPath(),
                    'error_params'  => @json_encode($params),
                    'error_method'  => ($request->isPost() ? 'POST' : 'GET'),
                    'error_msg'     => 'Message: '. str_replace([$pathApp, $pathPub, $pathLib], '', $error->getMessage())
                    . ".\nOn line: "  . $error->getLine()
                    . ".\nOf file: " . str_replace([$pathApp, $pathPub, $pathLib], '', $error->getFile()),
                    'error_trace'   => str_replace([$pathApp, $pathPub, $pathLib], '', $error->getTraceAsString()),
                    'error_code'    => $error->getCode(),
                    'error_time'    => time()
                ];
                
                $configs = $serviceManager->get('config')['db'] ?? [];
                $zfAdapter = new \Laminas\Db\Adapter\Adapter($configs);
                // Create Service
                $sqlService = new \Laminas\Db\Sql\Sql($zfAdapter);
                $insert = $sqlService->insert('tbl_error');
                $rs = $insert
                ->columns(array_keys($dataError))
                ->values($dataError);
                $sqlService->prepareStatementForSqlObject($insert)->execute();
                
                unset($request, $error, $zfAdapter, $sqlService);
            }catch (\Throwable $e){}
        }
    }

    /**
     * Convert bigint
     * @param string $value
     * @param integer $maxLength
     * @return number
     */
    public static function bigintval($value, int $maxLenth = 19) {
        $value = ltrim(trim($value), '0');
        if ( is_numeric($value) && is_int($value) ) {
            return $value;
        }
        preg_match('/^([0-9]{1,'.$maxLenth.'}).*/', $value, $maths);
        if ( isset($maths[1]) ) {
            return $maths[1];
        }
        return 0;
    }
    /**
     * Get database code
     * @param string $str
     * @param int $length
     * @return string
     */
    public static function idStringVal( string $str, int $length = 19){
        return substr(
            preg_replace('/[^a-z0-9\-\_]/i', '', $str),
            0, $length
        );
    }
    /**
     * is validate password
     * @param string $password
     * @return bool
     */
    public static function isValidPasswod(string $password = '' )
    {
        $pLength = strlen($password);
        if ( $pLength < 8 || $pLength > 32 ){
            return false;
        }
        
        $oldPass = $password;
        $password = preg_replace(
            '/([^a-z0-9'.preg_quote('!@#$%^&*-_+;:,.?', '/').'])/im',
            '', $password
        );
        
        if ( $oldPass != $password
            || preg_match('/[0-9]/', $password) !== 1
            || preg_match('/[a-z]/i', $password) !== 1
        ){
            return false;
        }
        return true;
    }
    /**
     * 
     * @param string $tmplCode
     * @param \Doctrine\ORM\EntityManager $dtAdapter
     * @param \Laminas\Db\Adapter $dbAdapter
     * @param array $keys
     * @param array $vals
     * @param array $to
     * @return boolean
     */
    public static function sendMailByTmplCode(
        $tmplCode, $dtAdapter, $keys = [], $vals = [], 
        $to = [], $attachment = []
    ){
        $tmpl = $dtAdapter
        ->getRepository('Models\Entities\Constant')
        ->fetchTmplMail($tmplCode);
        
        if (empty($tmpl)) return false;
        
        $tmpl['content'] = str_replace(
            $keys, $vals, $tmpl['content'] ?? ''
        );
        if (empty($to)) $to = [
            'email' => $tmpl['receiver'] ?? '',
            'name'  => DOMAIN_NAME
        ];
        return \Zf\Ext\Utilities\ZFTransportSmtp::sendMailNew([
            'to'        => $to['email'],
            'toName'    => $to['name'],
            
            'from'      => SIGN_UP_EMAIL,
            'fromName'  => $tmpl['sender'],
            
            'replyTo'   => NO_REPLY_EMAIL,
            'title'     => $tmpl['title'] ?? '',
            'msg'       => $tmpl['content'],
            'attachment'=> $attachment,
            'encoding'  => \Laminas\Mime\Mime::ENCODING_QUOTEDPRINTABLE
        ], $dtAdapter->getConnection());
            
    }
    
    /**
     * Get all action of controller
     * @param string $class
     * @param bool $useCache
     * @return array
     */
    public static function extractAllActionOfCtr( array $routers, $routersType, $useCache = true) : array {
        $rs = [];

        $writer = new \Laminas\Config\Writer\PhpArray();
        $writer->setUseBracketArraySyntax(true);

        foreach ( $routers as $className => $data){
            $moduleName = self::getNamespacesOfClass($className);

            //module
            if (!isset($rs[$moduleName])){
                $moduleCmts = new \ReflectionClass($moduleName . '\Module');
                $moduleCmts = self::scanComment($moduleCmts->getDocComment(), 'module');
                $moduleCmts['name'] = $moduleName;
                $rs[$moduleName] = $moduleCmts;
            }

            //controller
            $class = new \ReflectionClass($className);
            $controllerCmts = self::scanComment($class->getDocComment(), 'class');

            $path = [];
            foreach ($data as $router) {
                $path[$router] = implode(DIRECTORY_SEPARATOR, [
                    DATA_PATH, 'reflec_cache', APPLICATION_SITE, crc32($router) . '.php'
                ]);
                if ($useCache && file_exists($path[$router])
                    && !empty($cacheData = include $path[$router])
                ) {
                    $rs[$moduleName]['items'][$router] = $cacheData;
                    unset($cacheData);
                    continue;
                }

                if (!isset($controllerCmts['@deprecatedpermission'])) {
                    $controllerCmts['name'] = $router;
                    $rs[$moduleName]['items'][$router] = $controllerCmts;

                    //action
                    $className = ltrim($className, '\\');
                    $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

                    foreach ($methods as $method) {
                        if (
                            // Public function not public static
                            $method->getModifiers() == 1 &&
                            $method->class != 'Laminas\Mvc\Controller\AbstractActionController' &&
                            str_ends_with($method->name, 'Action')
                        ) {
                            $actionCmts = self::scanComment($method->getDocComment(), 'action');
                            if (!isset($actionCmts['@deprecatedpermission'])) {
                                if ($routersType[$router]['type'] == 'Zf\Ext\Router\RouterLiteral') {
                                    if (stripos($method->name, str_replace('-', '',
                                            $routersType[$router]['action'])) !== false) {
                                        $actionCmts['name'] = $method->name;
                                        $rs[$moduleName]['items'][$router]['title'] = $router;
                                        $rs[$moduleName]['items'][$router]['items'][$method->name] = $actionCmts;
                                        break;
                                    }
                                } else {
                                    $actionCmts['name'] = $method->name;
                                    $rs[$moduleName]['items'][$router]['title'] = $router;
                                    $rs[$moduleName]['items'][$router]['items'][$method->name] = $actionCmts;
                                }
                            }
                        }
                    }

                    $writer->toFile($path[$router], $rs[$moduleName]['items'][$router]);
                }
            }
        }
        return ['modules' => $rs];
    }
    
    /**
     * Merge item to list module
     * @param array $list
     * @param array $item
     * @return array
     */
    public static function mergeModule($list, $item){
        $moduleName = key($item);
        if(empty($list)){
            $list = $item;
        }else{
            $controllerName = key($item[$moduleName]['items']);
            $list[$moduleName]['items'][$controllerName] = $item[$moduleName]['items'][$controllerName];
        }
        return $list;
    }
    
    /**
     * Scan comment
     * @param string $commend
     * @param string $type
     * @return array|string[]
     */
    public static function scanComment($comment, $type = 'module'){
        $rs = [];
        switch ($type){
            case 'module':
                $regex = "/@moduleTitle:(.*)/m";
                break;
            case 'class':
                $regex = "/@controllerTitle:(.*)|\@deprecatedPermission:(.*)/m";
                break;
            case 'action':
                $regex = "/\@actionTitle\:(.*)|\@actionDescription\:(.*)|\@deprecatedPermission:(.*)/m";
                break;
            default:
                return $rs;
        }
        
        if( preg_match_all($regex, $comment, $matches) ){
            foreach ($matches[0] ?? [] as $key => $comment){
                $tmp = explode(':', $comment, 2);
                if(count($tmp) > 1){
                    $key = str_replace([
                        '@module', '@controller', '@action'
                    ], '', trim($tmp[0]));
                    $rs[strtolower($key)] = trim($tmp[1]);
                }
            }
        }
        return $rs;
    }
    /**
     * Get namespace of class
     * @param string $className
     * @return string
     */
    public static function getNamespacesOfClass(string $className) : string {
        $arr = explode('\\', $className);
        return array_shift($arr);
    }
}