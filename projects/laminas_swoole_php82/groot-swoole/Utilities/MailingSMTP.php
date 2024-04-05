<?php


namespace GrootSwoole\Utilities;
use Doctrine\DBAL\Connection;
use Laminas\Mail\Transport\Smtp as TransportSmtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MinePart;
use Laminas\Mime\Message as MimeMessage;

class MailingSMTP
{

    /**
     * @var self
     */
    private static $_instance = null;

    /**
     *
     * @var \Laminas\Mail\Transport\Smtp
     */
    protected static $_transport = null;

    /**
     * Doctrine Manger
     * @var \Doctrine\DBAL\Connection
     */
    protected $_dtManager;

    /**
     * @var array
     */
    protected static $_dbconfig = [];
    /**
     * @var array
     * Config template
     */
    protected static $_config = [
        'host' => 'smtp.gmail.com',
        'name' => 'smtp.gmail.com',
        'port' => 465,
        'connection_class' => 'login',
		'connection_time_limit'=> 300, // recreate the connection 5 minutes after connect()
        'connection_config' => [
            'username' => '',  // auto change config when format
            'password' => '',  // auto change config when format
            'ssl' => 'ssl',
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
            'auth' => true,
			'use_complete_quit' => false, // Dont send 'QUIT' on __destruct()
        ]
    ];

    /**
     * MailingSMTP constructor.
     *
     * @param \Doctrine\DBAL\Connection $dtManger
     * @param $redisManager
     */
    public function __construct(Connection $dtManger)
    {
        $this->setDoctrineMangeger($dtManger);
    }

    /**
     * @return static|null
     */
    public static function getInstance(Connection $dtManger, $needInit = true)
    {
        if ( empty(self::$_instance) && $needInit ) {
            self::$_instance = new MailingSMTP($dtManger);
        }

        if ( empty(self::$_instance) && $needInit ) {
            throw new \Exception('Fail to create new Instance');
        }

        return self::$_instance;
    }

    /**
     * Set Doctrine manger
     * @param $dtManger
     */
    protected function setDoctrineMangeger($dtManger)
    {
        $this->_dtManager = $dtManger;
    }

    /**
     * @return |null
     */
    protected function getDbConfig()
    {
        if ( empty(self::$_dbconfig) ) {
            self::$_dbconfig = self::getDbConfigByDt();
        }

        return self::$_dbconfig;
    }

    /**
     *
     * @return \Laminas\Mail\Transport\Smtp
     * @todo : Create zend mail transport
     */
    protected static function getTransport($config = [])
    {
        if ( empty(self::$_transport) ) {
            // -- Get host mail
            $host = $config['host'];
            unset($config['host']);
            
            // Create Option Object
            $configProcess = [
                'host' => $host,
                'name' => $host,
                'port' => $config['port'] ?? 465,
                'connection_class' => $config['auth'] ?? 'login',
                // For long-time runing script
                'connection_time_limit'=> 300, // recreate the connection 5 minutes after connect()
                'connection_config' => [
                    'username' => $config['username'] ?? '',
                    'password' => $config['password'] ?? '',
                    'ssl' => $config['ssl'] ?? '',
                    'use_complete_quit' => false, // Dont send 'QUIT' on __destruct()
                ]
            ];
            
            self::$_config = formatOptions(self::$_config, $configProcess);
            $smtpOptions = new SmtpOptions(self::$_config);
            
            // Create new instance of Smtp
            self::$_transport = new TransportSmtp($smtpOptions);
            unset($smtpOptions, $config);
        }
        
        return self::$_transport;
    }

    /**
     * @param \Doctrine\DBAL\Connection $dtAdapter
     * @return array
     * @todo : Get mail server configs
     */
    protected function getDbConfigByDt()
    {
        //check Redis if the data not stored then get them from DB

        return @json_decode(
            $this->_dtManager
            ->fetchOne("SELECT fnc_GetMailConfigs()") ?? '{}',
            true
        );
    }

    /**
     *
     * @param array $opts
     *            <p>string title: subject of mail</p>
     *            <p>string toName: name of receiver</p>
     *            <p>string from: send from email</p>
     *            <p>string to: send to email</p>
     *            <p>string msg: body of email</p>
     *            <p>array attachment: list of file want to send</p>
     * @param \Doctrine\DBAL\Connection $dtAdapter
     * @return bool
     * @todo : Send mail
     */
    public function sendMail(array $opts, $config = [])
    {
        try {
            //Get Config
            $config = empty($config) ? $this->getDbConfig() : $config;
            
            //Send Mail
            self::sendMsg(self::getTransport($config), $opts);

            $this->noop();

            return true;
        } catch (\Throwable $e) {
            self::_logError(
                substr(
                    $e->getMessage() . PHP_EOL
                    . $e->getTraceAsString(), 0, 2024
                ), 
                $this->_dtManager
            );
        }
        return false;
    }

    /**
     * Send email
     * @param \Laminas\Mail\Transport\Smtp $transport
     * @param array $opts
     */
    protected static function sendMsg(\Laminas\Mail\Transport\Smtp $transport, array $opts)
    {
        $message = new Message();
        if (!empty($opts['replyTo']))
            $message->setReplyTo($opts['replyTo']);

         !empty($opts['from'])
        ? $message->setFrom($opts['from'], $opts['fromName'] ?? '')
        : $message->setFrom(NO_REPLY_EMAIL,  '');

        // Create HTML message
        $html = new MinePart($opts['content']);
        $html->setType(Mime::TYPE_HTML);
        $html->setCharset('UTF-8');
        $html->setEncoding(
            $opts['encoding'] ?? Mime::ENCODING_QUOTEDPRINTABLE
        );
        $body = new MimeMessage();
        $body->addPart($html);

        // Attach file
        if (isset($opts['attachment']) && is_array($opts['attachment'])) {
            foreach ($opts['attachment'] as $file) {
                $filePath = $file['fullPath'] ?? $file['tmp_name'];
                if (!isset($file['type'])) {
                    $file['type'] = filetype($filePath);
                }
                $attachment = new MinePart(file_get_contents($filePath));
                $attachment->type = $file['type'];
                $attachment->filename = basename(utf8convert($file['name']));
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = Mime::ENCODING_BASE64;
                $body->addPart($attachment);
            }
            unset($attachment);
        }

        $message->setEncoding('UTF-8');
        $message->setBody($body);
        if (false == empty($opts['to']))
            $message->addTo($opts['to'], $opts['toName'] ?? '');

        $message->setSubject($opts['title'] ?? '');

        // -- cc
        if (false == empty($opts['cc']))
            $message->addCc($opts['cc'], $opts['ccName'] ?? '');
        // -- bcc
        if (false == empty($opts['bcc']))
            $message->addBcc($opts['bcc'], $opts['bccName'] ?? '');

        $transport->send($message);
    }
    public function noop($isReset = false)
    {
        if (empty($isReset)) self::$_transport->getConnection()->noop();
        else self::$_transport->getConnection()->rset();
    }

    /**
     * Disconnect with database, mail server after sent
     */
    public static function selfDestroy(){
        
        self::$_transport->disconnect();
        if( self::$_instance->_dtManager->isConnected() ){
            /**
             * Configuration
             * @var \Doctrine\ORM\Configuration  $confs
             */
            $confs = self::$_instance->_dtManager->getConfiguration();
            foreach ([
                'HydrationCacheImpl',
                'MetadataCacheImpl',
                'QueryCacheImpl',
                'ResultCacheImpl',
                'HydrationCache',
                'MetadataCache',
                'QueryCache',
                'ResultCache'
                ] as $fnc
            ){
                $cache = $confs->{"get{$fnc}"}();
                if ($cache instanceof \Psr\Cache\CacheItemPoolInterface ){
                    $cache = $cache->getCache();
                }
                
                if( ($cache instanceof \Doctrine\Common\Cache\RedisCache ||
                    $cache instanceof \GrootSwoole\DoctrineRedisCache)
                    && ($redis = $cache->getRedis())->isConnected()
                ){
                    $redis->close();
                    $redis->__destruct();
                    break;
                }
            }
            self::$_instance->_dtManager->close();
        }
        
        self::$_transport->__destruct();
        self::$_transport = null;
        self::$_instance = null;
    }
    

    /**
     * Save log
     *
     * @param string $msg
     */
    protected static function _logError($msg, $zAdapter = null)
    {
        try {
            $zAdapter->insert(
                'tbl_log_error_sendmail', [
                    'log_error_email' => self::$_config['connection_config']['username'],
                    'log_error_content' => $msg,
                    'log_error_time' => time()
                ]
            );
        } catch (\Throwable $e) {}
    }
}