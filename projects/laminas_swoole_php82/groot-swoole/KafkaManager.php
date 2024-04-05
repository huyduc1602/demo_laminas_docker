<?php
namespace GrootSwoole;

use \Enqueue\RdKafka\RdKafkaConnectionFactory as RdKafkaConFactory;

/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class KafkaManager
{
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
     * @param array $configs
     */
    public function __construct(array $configs, $redisCache)
    {
        $this->_kafkaConfigs = $configs;
        
        $this->_redisCache = $redisCache;
    }

    /**
     * @param string $contextName
     * @return $this | bool
     * @throws \Exception
     */
    public function __invoke(string $contextName): KafkaManager
    {
        if (!isset($this->_kafkaConfigs[$contextName])) {
            throw new \Exception("Invalid contextName `{$contextName}` in configuration.");
        }
        if (empty($this->_context[$contextName])) {
            
            $configs = $this->_kafkaConfigs[$contextName]['context'];
            // Delete consumer property
            unset(
                $configs['global']['group.id'], 
                $configs['global']['enable.auto.commit'],
                $configs['topic']['auto.offset.reset']
            );
            
            if (!isset($configs['global']['log_level'])){
                // Turn off warning log of kafka
                $configs['global']['log_level'] = '3';
            }
            
            $this->_context[$contextName] = @(new RdKafkaConFactory(
                $configs
            ))
            ->createContext();
            
            unset($configs);
        }
        return $this;
    }
    /**
     * Get context name by topic name
     */
    protected function getContextNameByTopic($topicName){
        foreach ($this->_kafkaConfigs as $contextName => $configs){
            if (
                is_array($configs['topic_name']) && in_array($topicName, $configs['topic_name'])
                || $topicName == $configs['topic_name']
            ){
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
        if ( !empty($contextName) &&
            !empty($this->_context[$contextName])
        ) return @$this->_context[$contextName]->createTopic($topicName);
            
        return null;
    }

    /**
     * @param string $topicName
     * @param array $body
     * @param array $properties
     * @param array $headers
     * @param array $attr
     * @throws \Interop\Queue\Exception
     */
    public function sendMessage(string $topicName, $body, $redisUniqueKey = null, array $properties = [], array $headers = [], array $attr = [], $partition = null)
    {
        if (empty($contextName = $this->getContextNameByTopic($topicName)) ||
            empty($topic = $this->getKafkaTopic($contextName, $topicName))
        ){
            throw new \Exception("Can not send message to Kafka Service now. Please retry later!");
        }
        
        if (empty($this->_context[$contextName]))
            throw new \Exception("Context is not defined. Please retry later!");
            
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

            if (is_numeric($partition)) $topic->setPartition($partition);

            @$this->_context[$contextName]->createProducer()->send(
                $topic, $message
            );
            
            // This step for check step of message Kafka
            $this->setStepOfProcess($redisUniqueKey, [
                'topic'    => $topicName,
                'action'   => ltrim($act, '/'),
                'producer' => [
                    'status'    => true,
                    'time'      => time()
                ]
            ]);
            
            return $redisUniqueKey;
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
     * It use for send message back to Kafka
     * @param \Laminas\Cache\Storage\StorageInterface $cacheCore
     * @param array $body
     * @param string $topicName
     * @param string $processKey
     * @throws \Interop\Queue\Exception
     */
    public function loopMessage(array $body, string $topicName, string $processKey ){
        try{
            $cacheCore = $this->getCacheCore();
            $data = $cacheCore->getItem($processKey);
            $process = $data['process'] ?? [
                'sentCount' => 0, 'total' => 0
            ];

            $process['sentCount'] += (defined('KAFKA_MAX_LOOP_ITEM') ? KAFKA_MAX_LOOP_ITEM : 2000);
            $process['sentCount'] = ($process['sentCount'] < $process['total'])
                ? $process['sentCount']
                : $process['total'];

            $data['process'] = $process;
            $cacheCore->setItem($processKey, $data);
            unset($cacheCore);
        } catch (\Throwable $e) {
            $date = gmdate('d/M/Y:H:i:s O', time());
            $msg = urldecode( mb_substr($e->getMessage(), 0, 100) );
            fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"POST /redis-error?msg={$msg} HTTP/1.1\" 500 1\n");
        }

        if( $process['sentCount'] < $process['total'] ) {
            $this->sendMessage($topicName, $body, $processKey);
            return true;
        }
        return false;
    }
    
    /**
     * @var \Laminas\Cache\Storage\StorageInterface
     */
    protected $_redisCache = null;
    
    /**
     * Get redis cache core
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected function getCacheCore() {
        return $this->_redisCache;
    }
    
    /**
     * @param string $key
     * @param array $data
     */
    public function setStepOfProcess($key = '', array $data = [])
    {
        try{
            if ( strlen($key) == 0 ) return;

            $this->getCacheCore()->setItem($key, array_replace(
                $this->getCacheCore()->getItem($key) ?? [],
                $data
            ));
        } catch (\Throwable $e) {
            $date = gmdate('d/M/Y:H:i:s O', time());
            $msg = urldecode( mb_substr($e->getMessage(), 0, 100) );
            fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"POST /redis-error?msg={$msg} HTTP/1.1\" 500 1\n");
        }
    }
    
}
