<?php
namespace GrootSwoole;

use Doctrine\ORM\EntityManager;
abstract class BaseTaskEventService
{
    use BaseUtil;
    
    /**
     * @var EntityManager
     */
    protected $_entityManager;
    
    /**
     * @var
     */
    protected $_cacheCfg;

    /**
     * @var null
     */
    protected static $_caches = null;
    /**
     * @var array
     */
    protected $_kafkaConfig;

    /**
     * BaseMailService constructor.
     * @param $doctrineConfigs
     * @param $redisConfigs
     * @param $kafkaConfig
     * @throws \Doctrine\ORM\ORMException
     */
    public function __construct($doctrineConfigs, $redisConfigs, $kafkaConfig)
    {
        $this->_entityManager   = $this->createEntityManager($doctrineConfigs);
        $this->_cacheCfg        = $redisConfigs;
        $this->_kafkaConfig     = $kafkaConfig;

    }

    /**
     * @param $contextName
     * @param $topicName
     * @param $action
     * @param array $formPost
     * @throws \Interop\Queue\Exception
     */
    public function sendKafkaMessage($contextName, $topicName, $action, $formPost = []) {
        $this->getKafkaManager($contextName)->sendMessage($topicName, [
            'action'    => $action,
            'form_post' => $formPost
        ]);
    }

    /**
     * @param string $contextName
     * @return bool|KafkaManager
     * @throws \Exception
     */
    public function getKafkaManager(string $contextName) {
        return (new KafkaManager($this->_kafkaConfig))($contextName);
    }

    /**
     * @param string $cacheKey
     * @param bool $ttl
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected function getCache(string $cacheKey, $ttl = false) {

        if ( null == self::$_caches || empty(self::$_caches[$cacheKey]) ) {
            self::$_caches[$cacheKey] = self::createRedisManager(
                array_merge([
                    'namespace' => $cacheKey,
                    'ttl'       => $ttl,
                ], $this->_cacheCfg)
            );
        }
        return self::$_caches[$cacheKey];
    }

}