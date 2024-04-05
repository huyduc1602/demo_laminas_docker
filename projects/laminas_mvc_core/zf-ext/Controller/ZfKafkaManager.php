<?php

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Psr\Container\ContainerInterface;
use \Enqueue\RdKafka\RdKafkaConnectionFactory as RdKafkaConFactory;

/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZfKafkaManager extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getKafkaManager';

    /**
     * @var \Enqueue\RdKafka\RdKafkaContext
     */
    protected $_context = null;

    /**
     * @var array
     */
    protected $_kafkaConfigs = null;

    /**
     * ZfKafkaManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!$this->_kafkaConfigs) {
            $thisOptions = $container->get('config');
            $this->_kafkaConfigs = $thisOptions['kafka_config'] ?? [];
        }
    }

    /**
     * @param string $contextName
     * @return $this | bool
     * @throws \Exception
     */
    public function __invoke(string $contextName)
    {
        if (!isset($this->_kafkaConfigs[$contextName])) {
            throw new \Exception("Invalid contextName `{$contextName}` in configuration.");
        }
        if (empty($this->_context[$contextName])) {
            $configs = $this->_kafkaConfigs[$contextName]['context'];
            
            if (!isset($configs['global']['log_level'])){
                // Turn off warning log of kafka
                $configs['global']['log_level'] = '3';
            }
            $this->_context[$contextName] = @(new RdKafkaConFactory(
                $configs
            ))->createContext();
                
            unset($configs);
        }
        return $this;
    }
    
    /**
     * Get context name by topic name
     */
    protected function getContextNameByTopic($topicName){
        foreach ($this->_kafkaConfigs as $contextName => $configs){
            if (in_array($topicName, $configs['topic_name'])){
                return $contextName;
            }
        }
        return null;
    }
    
    /**
     * @param string $contextName
     * @param string $topicName
     * @return \Enqueue\RdKafka\RdKafkaTopic|\Interop\Queue\Topic|null |null
     */
    public function getKafkaTopic($contextName, string $topicName)
    {
       // dd($contextName, $this->_context,$this->_context[$contextName]);
        if ( !empty($contextName) &&
            !empty($this->_context[$contextName])
        ) {
            return @$this->_context[$contextName]->createTopic($topicName);
        }

        return null;
    }
    /**
     * Send ping message to Background-service via Kafka service
     * @param string $topicName
     * @param $body
     * @param $redisUniqueKey
     * @param array $properties
     * @param array $headers
     * @param array $attr
     * @throws \Interop\Queue\Exception
     * @author Cuong_dev - 2/14/2023
     */
    public function pingMessage(string $topicName, $body, $pingOpts = [], $processKeyCheck = 'task-worker'){
        // add new is_ping key YES for ping message
        $body['form_post']['is_ping'] = 'YES';
        $redisUniqueKey = $this->sendMessage($topicName, $body);

        $fnc        = $pingOpts['func'] ?? 'usleep';
        $timeout    = $pingOpts['timeout'] ?? 300000; // 300 milliseconds
        $maxRetry   = $pingOpts['retry'] ?? 20;
        $isDel      = $pingOpts['is_del'] ?? true;
        $retryCount = 0;

        do {
            $retryCount++;
            call_user_func($fnc, $timeout);
            $cacheProcessData = $this->getStepOfProcess($redisUniqueKey);
            if ( isset($cacheProcessData[$processKeyCheck]) ){
                if ( $isDel ) $this->clearSteopOfProcess($redisUniqueKey);
                return true;
            }
        }
        while ($retryCount < $maxRetry);

        throw new \Exception("pingMessage can not send to [{$processKeyCheck}]. Please retry later!");
    }
    /**
     * @param string $topicName
     * @param array $body
     * @param array $properties
     * @param array $headers
     * @param array $attr
     * @return string|null
     * @throws \Interop\Queue\Exception
     * @throws \Exception
     */
    public function sendMessage(string $topicName, $body, $redisUniqueKey = null, array $properties = [], array $headers = [], array $attr = [])
    {
        if (empty($contextName = $this->getContextNameByTopic($topicName)) || 
            empty($topic = $this->getKafkaTopic($contextName, $topicName))
        )
            throw new \Exception("Can not send message to Kafka Service now. Please retry later!");

        if (empty($this->_context[$contextName]))
            throw new \Exception("Context is not defined. Please retry later!");
        
        $saveData=false;
        if ( isset($properties['save_to_redis']) ) {
            $saveData = $properties['save_to_redis'] ?? false;
            unset($properties['save_to_redis']);
        }

        $act = $body['action'] ?? '';
        $body['process_key'] = (
            $redisUniqueKey = $redisUniqueKey ?? $this->getRandUID()
        );
        
        $body = (is_string($body) ? $body : @json_encode($body)) ?? '';

        $message = @$this->_context[$contextName]->createMessage($body, $properties, $headers);

        foreach ($attr as $key => $val) {
            if (method_exists($message, "set" . ucfirst($key))) {
                $message->{"set" . ucfirst($key)}($val);
            }
        }
        
        @$this->_context[$contextName]->createProducer()->send(
            $topic, $message
        );

        if ( $saveData ) {
            // This step for check step of message Kafka
            $this->setStepOfProcess($redisUniqueKey, [
                'topic'    => $topicName,
                'action'   => ltrim($act, '/'),
                'producer' => [
                    'status'    => true,
                    'time'      => time()
                ]
            ]);
        }
        
        return $redisUniqueKey;
    }
    protected $redisCache = null;
    /**
     * Get Zend Cache
     * @param string $key
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected function getCacheCore($key = '')
    {
        if ( $this->redisCache == null ){
            $key = $key ? $key : $this->getRandUID();
            return $this->redisCache = \Zf\Ext\CacheCore::_getRedisCaches($key, [
                'lifetime'  => defined('KAFKA_REDIS_LIFETIME') ? KAFKA_REDIS_LIFETIME : 3600,
                'namespace' => 'KAFKA_MESSAGE'
            ]);
        }
        
        return $this->redisCache;
    }
    
    /**
     * @param int $length
     * @return string
     */
    protected function getRandUID($length = 19)
    {
        return substr(uniqid('kafka_', true), 0, $length);
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function setStepOfProcess($key, array $data)
    {
        $redisCache = $this->getCacheCore($key);
        
        $redisCache->setItem($key, array_replace(
            $redisCache->getItem($key) ?? [],
            $data
        ));
        
        unset($redisCache);
    }
    /**
     * Get cached data
     * @param string $key
     * @return mixed
     */
    public function getStepOfProcess($key){
        return $this->getCacheCore($key)->getItem($key);
    }
    /**
     * Clear data on cache
     * @param string $key
     * @return mixed
     */
    public function clearSteopOfProcess($key){
        return $this->getCacheCore($key)->removeItem($key);
    }
}
