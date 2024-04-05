<?php
namespace GrootSwoole;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use GrootSwoole\SqlLogger;
use GrootSwoole\Utilities\MailingSMTP;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Cache\Adapter\RedisAdapter as SymfonyRedisAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter as SymfonyPhpFilesAdapter;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

trait BaseUtil
{
    /**
     * Create doctrine entity manager
     * @param array $configs
     * @return \Doctrine\ORM\EntityManager
     */
    public function createEntityManager(array $configs)
    {
        if (class_exists('Doctrine\ORM\ORMSetup')) return $this->initDTNew($configs);
        
        $fullConfigs = $configs;
        $configs = $configs['connection']['orm_default']['params'] ?? [];
        
        $configObj = Setup::createAnnotationMetadataConfiguration(
            [], true
        );
        
        $fncConfigs = $fullConfigs['configuration'] ?? [];
        foreach (['datetime_functions','numeric_functions','string_functions'] as $fncs){
            if ( !empty($fncConfigs['orm_default'][$fncs]))
                $configObj->setCustomDatetimeFunctions(
                    $fncConfigs['orm_default'][$fncs]
                );
        }
        
        if ( !empty($fullConfigs['driver']['zf_dt_driver']['logs']) )
            $configObj->setSQLLogger(
                new SqlLogger($fullConfigs['driver']['zf_dt_driver']['logs'])
            );
        
        if ( !empty($fullConfigs['driver']['zf_dt_driver']['redis_cache']) ){
            
            $cacheDriver = \Doctrine\Common\Cache\Psr6\CacheAdapter::wrap(
                new \GrootSwoole\DoctrineRedisCache ([])
            );
            
            foreach (['setHydrationCache', 'setMetadataCache', 'setQueryCache', 'setResultCache'] as $fncName){
                $configObj->{$fncName}($cacheDriver);
            }
        }
                    
        return EntityManager::create($configs, $configObj);
    }
    
    protected function initDTNew(array $configs){
        $fullConfigs = $configs;
        $dbParams = $configs['connection']['orm_default']['params'] ?? [];
        
        $drConfigs = $fullConfigs['driver']['zf_dt_driver'] ?? [];
        
        $config = new Configuration();
        $driverImpl = new AnnotationDriver(
            new AnnotationReader(), $drConfigs['paths']
        );
        $config->setMetadataDriverImpl($driverImpl);
        $config->setProxyDir($drConfigs['proxy_dir']);
        $config->setProxyNamespace($drConfigs['proxy_namespace']);
        $config->setAutoGenerateProxyClasses($drConfigs['generate_proxies']);
        
        if ( isset($drConfigs['redis_cache']) ){
            foreach ([
                'setHydrationCache', 'setMetadataCache', 'setQueryCache', 'setResultCache',
            ] as $fncName){
                $cache = new SymfonyRedisAdapter(
                    $this->getDTRedisCache(), 'DOCTRINE_CACHE'
                );
                $config->{$fncName}($cache);
            }
        }else{
            foreach ([
                'setHydrationCache', 'setMetadataCache', 'setQueryCache', 'setResultCache',
            ] as $fncName){
                $cache = new SymfonyPhpFilesAdapter('DOCTRINE_CACHE');
                $config->{$fncName}($cache);
            }
        }
        
        $fncConfigs = $fullConfigs['configuration']['orm_default'] ?? [];
        foreach (['datetime_functions','numeric_functions','string_functions'] as $fncs){
            if ( !empty($fncConfigs[$fncs]))
                $config->setCustomDatetimeFunctions($fncConfigs[$fncs]);
        }
        
        if ( !empty($drConfigs['logs']) )
            $config->setMiddlewares([
                new \Doctrine\DBAL\Logging\Middleware(
                    new SqlLogger($drConfigs['logs'])
                )
            ]);
        
        return new EntityManager(
            DriverManager::getConnection($dbParams, $config), 
            $config
        );
    }
    /**
     * Returns the serializer constant to use. If Redis is compiled with
     * igbinary support, that is used. Otherwise the default PHP serializer is
     * used.
     *
     * @return int One of the Redis::SERIALIZER_* constants
     */
    protected function getRedisSerializerValue()
    {
        if (defined('Redis::SERIALIZER_IGBINARY') && extension_loaded('igbinary')) {
            return \Redis::SERIALIZER_IGBINARY;
        }
        
        return \Redis::SERIALIZER_PHP;
    }

    protected function iniRedis()
    {
        $redis = new Redis();
        $redis->connect(REDIS_CONFIG['server']['host'], REDIS_CONFIG['server']['port']);

        if( !empty(REDIS_CONFIG['password']) ) {
            $redis->auth(REDIS_CONFIG['password']);
        }

        $redis->select(REDIS_CONFIG['database'] ?? 0);

        $redis->setOption(Redis::OPT_PREFIX, 'DOCTRINE_CACHE:');

        return $redis;
    }

    protected function iniRedisCluster()
    {
        $redis = new RedisCluster(
            null,
            REDIS_CONFIG['seeds'],
            REDIS_CONFIG['timeout'] ?? 5,
            0,
            true,
            REDIS_CONFIG['password']
        );

        $redis->setOption(Redis::OPT_PREFIX, (REDIS_CONFIG['lib_options'][Redis::OPT_PREFIX] ?? '') . 'DOCTRINE_CACHE:');

        $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_ERROR);

        // $this->redis->multi();

        return $redis;
    }

    /**
     * Reuse redis connection 
     * @return \Redis
     */
    protected function getDTRedisCache(){
        if( $this->dtRedisCache ) return $this->dtRedisCache;

        if (isset(REDIS_CONFIG['seeds']))
            $this->dtRedisCache = $this->iniRedisCluster();
        else $this->dtRedisCache = $this->iniRedis();

        $this->dtRedisCache->setOption(
            \Redis::OPT_SERIALIZER,
            $this->getRedisSerializerValue()
        );

        return $this->dtRedisCache;
    }

    /**
     * @param array $opts
     * @return \GrootSwoole\LaminasRedisAdapter
     */
    public static function createRedisManager($opts = [])
    {
        if ( class_exists('Laminas\Cache\StorageFactory') ){
            return StorageFactory::factory([
                'adapter' => [
                    'name' => \GrootSwoole\BaseRedisAdapter::class,
                    'options' => array_merge([
                        'namespace' => 'KAFKA_MESSAGE',
                        'ttl'       => null,
                    ], $opts, REDIS_CONFIG)
                ],
                'plugins' => [
                    'exception_handler' => [
                        'throw_exceptions' => true
                    ],
                    'Serializer'
                ]
            ]);
        }else{
            if ( isset(REDIS_CONFIG['seeds']) ){
                $configs = REDIS_CONFIG ?? [];

                unset($configs['database'], $configs['read_timeout']);

                if (isset($opts['ttl'])) $configs['ttl'] = $opts['ttl'];

                $cache = new \Laminas\Cache\Storage\Adapter\RedisCluster();
                $cache->setOptions($configs);
            }else{
                $cache =new \Laminas\Cache\Storage\Adapter\Redis (
                    array_merge([
                        'namespace' => DOMAIN_NAME,
                        'ttl'       => null,
                    ], $opts, REDIS_CONFIG)
                );
            }

            // Laminas cache Version 3
            return new \GrootSwoole\LaminasRedisAdapter (
                $cache
                ->addPlugin(
                    (new \Laminas\Cache\Storage\Plugin\ExceptionHandler())
                    ->setOptions(new \Laminas\Cache\Storage\Plugin\PluginOptions([
                        'throw_exceptions' => true
                    ]))
                )
                ->addPlugin(
                    new \Laminas\Cache\Storage\Plugin\Serializer()
                )
            );
        }
    }
    
    /**
     * @param bool $getInstance
     * @return \GrootSwoole\Utilities\MailingSMTP
     * @throws \Doctrine\ORM\ORMException
     * @author Cuongnp - 05/10/2021
     */
    public function getMailingSMTP($dbInstanace = true, $getInstance = true) {
        return MailingSMTP::getInstance(
            $this->getEntityManager($dbInstanace)->getConnection(),
            $getInstance
        );
    }

}