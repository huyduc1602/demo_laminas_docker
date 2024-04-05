<?php
namespace Zf\Ext;
use \Laminas\Cache\StorageFactory;

abstract class CacheCore
{

    /**
     * Location of cache
     *
     * @var string
     */
    protected static $_path = '/cache';

    /**
     * @var \Laminas\Cache\Storage\StorageInterface
     */
    protected static $_cacheCore = null;
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected static $_dAdapter = null;
    
    protected static function _checkDir( $namespace = '', $dir = '' ){
        if ( is_array($dir) ){
            $path = $dir['path'];
            if ( !is_dir($path) ){
                @mkdir($path);
            }
        }else{
            if ( !is_dir(DATA_PATH . self::$_path . '/' . $dir) ){
                @mkdir(DATA_PATH . self::$_path . '/' . $dir);
            }
            $path = DATA_PATH . self::$_path . "/{$dir}/{$namespace}";
            if ( !is_dir($path) ){
                @mkdir($path);
            }
        }
        return realpath($path);
    }


    /**
     * Lay thong tin cache
     * @param array $opts
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    public static function _getCaches($cacheKey, $opts = [] )
    {
        if (defined('REDIS_CONFIG')) return self::_getRedisCaches($cacheKey, $opts);
        
        // life time
        $lifetime = $opts['lifetime'] ? $opts['lifetime'] : 86400; // 86400 = 1 days
        if (false === $opts['lifetime'])
            $lifetime = null;

        $namespace = 'zfcache';
        if ( isset($opts['namespace']) )
            $namespace = $opts['namespace'];
        $path = self::_checkDir($namespace, $opts['path']);

        if ( null == self::$_cacheCore || empty(self::$_cacheCore[$cacheKey]) ) {
            $options = [
                'namespace' => $namespace,
                'ttl'       => $lifetime,
                'cache_dir' => $path
            ];
            if ( class_exists('Laminas\Cache\StorageFactory') ){
                self::$_cacheCore[$cacheKey] = StorageFactory::factory([
                    'adapter' => [
                        'name' => 'filesystem',
                        'options' => $options
                    ],
                    'plugins' => [
                        // Don't throw exceptions on cache errors
                        'exception_handler' => [
                            'throw_exceptions' => false
                        ],
                        'Serializer'
                    ]
                ]);
            }elseif (class_exists('\Laminas\Cache\Storage\Adapter\Filesystem')){
                self::$_cacheCore[$cacheKey] = new \Laminas\Cache\Storage\Adapter\Filesystem($options);
                self::$_cacheCore[$cacheKey]->addPlugin(
                    new \Laminas\Cache\Storage\Plugin\Serializer()
                );
            }
        }
        return self::$_cacheCore[$cacheKey];
    }

    /**
     * Lay thong tin cache
     * @param array $opts
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    public static function _getRedisCaches($cacheKey, $opts = [] )
    {
        $lifetime = $opts['lifetime'] ? $opts['lifetime'] : 86400; // 86400 = 1 days
        if (false === $opts['lifetime']) $lifetime = null;
        // Laminas cache Version 1
        if ( null == self::$_cacheCore ) {
            self::$_cacheCore = self::createRedisCache($cacheKey, $opts, $lifetime);
        }else{
            // Re-use connection
            self::$_cacheCore->getOptions()
            // Change time to live
            ->setTtl($lifetime)
            // Change name space
            ->setNamespace(
                $opts['namespace'] ?? DOMAIN_NAME
            );
        }
        
        return (new \Zf\Ext\LaminasRedisCache (self::$_cacheCore))
        ->setMyNamespace($opts['namespace'] ?? DOMAIN_NAME)
        ->setMyTTL($lifetime);
    }
    /**
     * @param string $cacheKey
     * @param array $opts
     * @param bool|integer $lifetime
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    public static function createRedisCache($cacheKey, $opts = [], $lifetime = false){
        if ( class_exists('Laminas\Cache\StorageFactory') ){
            return StorageFactory::factory([
                'adapter' => [
                    'name' => ZFRedisAdapter::class,
                    'options' => array_merge([
                        'namespace' => isset($opts['namespace']) ? $opts['namespace'] : 'redis_cache',
                        'ttl'       => $lifetime,
                    ], REDIS_CONFIG)
                ],
                'plugins' => [
                    'exception_handler' => [
                        'throw_exceptions' => true
                    ],
                    'Serializer'
                ]
            ]);
        }else{
            // Laminas cache Version 3
            unset($opts['lifetime']);

            if (isset(REDIS_CONFIG['seeds'])){
                $configs = REDIS_CONFIG ?? [];
                unset($configs['database']);
                if (isset($opts['ttl'])) $configs['ttl'] = $opts['ttl'];

                $cache = new \Laminas\Cache\Storage\Adapter\RedisCluster($configs);
            }else{
                $cache =new \Laminas\Cache\Storage\Adapter\Redis (
                    array_merge([
                        'namespace' => DOMAIN_NAME,
                        'ttl'       => $lifetime,
                    ], $opts, REDIS_CONFIG)
                );
            }

            $cache->addPlugin(
                (new \Laminas\Cache\Storage\Plugin\ExceptionHandler())
                ->setOptions(new \Laminas\Cache\Storage\Plugin\PluginOptions([
                    'throw_exceptions' => true
                ]))
            )
            ->addPlugin(
                new \Laminas\Cache\Storage\Plugin\Serializer()
            );

            return new \Zf\Ext\LaminasRedisAdapter (
                $cache,$opts['throw_err_on_call'] ?? false
            );
        }
    }
}