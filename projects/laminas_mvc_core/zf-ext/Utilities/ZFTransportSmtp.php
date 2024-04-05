<?php
namespace Zf\Ext\Utilities;

use \Laminas\Mail\Transport\Smtp as TransportSmtp;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part as MimePart;
use Laminas\Db\Sql\Sql;

class ZFTransportSmtp
{
    /**
     *
     * @var \Laminas\Mail\Transport\Smtp
     */
    protected static $_transport = null;

    /**
     *
     * @var array
     */
    protected static $_config = [];
    protected static $_connAt = 0;

    /**
     * Get config from database by LaminasDB
     * @param \Laminas\Db\Table $zAdapter
     * @param string $tbl
     * @return array
     */
    protected static function getMailDbConfig($zAdapter, $tbl){
        // Create Service
        $sqlService = new Sql($zAdapter);

        // Create query
        $sql = $sqlService->select()
            ->from('tbl_constant')
            ->columns([
                'constant_content'
            ])
            ->where([
                'constant_code = ?' => 'system_server_mail_config'
            ]);

        // -- Get server mail config.
        $selectString = $sql->getSqlString($zAdapter->getPlatForm());
        $config = $zAdapter->query($selectString, $zAdapter::QUERY_MODE_EXECUTE)->current();

        // -- has a result
        if ($config) {
            $config = json_decode($config['constant_content'], true);
        } else
            $config = [];

        // -- Get send mail account
        $sql = $sqlService->select()
            ->from($tbl)
            ->columns([
                'send_mail_id',
                'send_mail_account',
                'send_mail_password'
            ])
            ->order('send_mail_total ASC')
            ->limit(1)
            ->offset(0);
        $selectString = $sqlService->buildSqlString($sql, $zAdapter);
        $sendMail = $zAdapter->query($selectString, $zAdapter::QUERY_MODE_EXECUTE)->current();

        // Plus send mail times.
        $update = $sqlService->update($tbl);

        $update->set([
            'send_mail_total' => new \Laminas\Db\Sql\Expression('`send_mail_total` + 1')
        ])->where([
            'send_mail_id' => $sendMail['send_mail_id']
        ]);
        $sqlService->prepareStatementForSqlObject($update)->execute();

        return array_merge($config, [
            'username' => $sendMail['send_mail_account'],
            'password' => $sendMail['send_mail_password'],
        ]);
    }

    /**
     *
     * @todo : Create zend mail transport
     * @param array $config            
     * @param \Laminas\Db\Table $zAdapter
     * @return \Laminas\Mail\Transport\Smtp
     */
    protected static function getTransport($config = [], $zAdapter = null, $tbl = 'tbl_send_mail', $force = false)
    {
        if ( empty(static::$_transport ?? false) || $force ) {
            if ( empty($config) ) {
                $config = static::getMailDbConfig($zAdapter, $tbl);
            }
            
            $config['use_complete_quit'] = true;
            static::$_config = $config;
            
            // -- Get host mail
            $host = $config['host'];
            unset($config['host']);

            // Create Option Object
            $smtpOptions = new \Laminas\Mail\Transport\SmtpOptions();

            $smtpOptions
                ->setHost($host)
                ->setName($host)
                ->setConnectionClass($config['auth'])
                ->setPort((int) $config['port']);

            if ( defined('IS_BG_SERVICE') ) {
                $smtpOptions->setConnectionTimeLimit(300);
                $config['use_complete_quit'] = false;
            }

            $smtpOptions->setConnectionConfig($config);
            
            // Create new instance of Smtp
            static::$_transport = new TransportSmtp($smtpOptions);
            unset($smtpOptions, $config);

            static::$_connAt = time();
        }elseif (static::$_connAt > 0 && (time() - static::$_connAt) > 5 ){
			static::resetConn($zAdapter, $tbl);
        }

        return static::$_transport;
    }
	
	protected static function resetConn($zAdapter, $tbl){
		try {
            static::$_transport->getConnection()->rset();
			static::$_connAt = time();
        }catch (\Throwable $e){
			static::_logError(
				$e->getMessage()
				. "\n"
				. $e->getTraceAsString(),
				$zAdapter
			);
			
			static::destructConn($zAdapter);
			
			static::getTransport(
				self::getDbConfigByDt($zAdapter), 
				$zAdapter, $tbl, true
			);
		}
	}
	
	protected static function destructConn($zAdapter){
		try {
			static::$_transport = null; static::$_connAt = 0;
		}catch (\Throwable $e){
			static::_logError(
				$e->getMessage()
				. "\n"
				. $e->getTraceAsString(),
				$zAdapter
			);
		}
	}
	
    /**
     * @todo : Get mail server configs
     * @param \Doctrine\DBAL\Connection $dtAdapter
     * @return array
     */
    protected static function getDbConfigByDt($dtAdapter = null)
    {
        return @json_decode(
            $dtAdapter->fetchOne("SELECT fnc_GetMailConfigs()") ?? '{}', true
        );
    }
    
    /**
     *
     * @todo : Send mail
     * @param array $opts
     *            <p>string title: subject of mail</p>
     *            <p>string toName: name of receiver</p>
     *            <p>string from: send from email</p>
     *            <p>string to: send to email</p>
     *            <p>string msg: body of email</p>
     *            <p>array attachment: list of file want to send</p>
     * @param \Laminas\Db\Table $zAdapter
     * @return bool
     */
    public static function sendMail(array $opts, $zAdapter = null)
    {
        try {
            $transport = self::getTransport([], $zAdapter);
            self::sendMsg($transport, $opts);
            unset($transport);
            return true;
        } catch (\Throwable $e) {
            self::_logError($e->getMessage(), $zAdapter);
        }
        return false;
    }
    
    /**
     *
     * @todo : Send mail
     * @param array $opts
     *            <p>string title: subject of mail</p>
     *            <p>string toName: name of receiver</p>
     *            <p>string from: send from email</p>
     *            <p>string to: send to email</p>
     *            <p>string msg: body of email</p>
     *            <p>array attachment: list of file want to send</p>
     * @param \Doctrine\DBAL\Connection $dtAdapter
     * @return bool
     */
    public static function sendMailNew(array $opts, $dtAdapter = null)
    {
        try {
            self::sendMsg(self::getTransport(
                self::getDbConfigByDt($dtAdapter),
				$dtAdapter
            ), $opts);
            
            return true;
        } catch (\Throwable $e) {
            self::_logError(
                $e->getMessage()
                . "\n"
                . $e->getTraceAsString(),
                
                $dtAdapter
            );
        }
        return false;
    }
    
    /**
     * Send email
     * @param \Laminas\Mail\Transport\Smtp $transport
     * @param array $opts
     */
    public static function sendMsg($transport, array $opts){
        $message = new \Laminas\Mail\Message();
        if ( false == empty($opts['replyTo']) )
            $message->setReplyTo($opts['replyTo']);
        if ( false == empty($opts['from']) )
            $message->setFrom($opts['from'], $opts['fromName'] ?? '');
                
        // Create HTML message
        $html = new MimePart($opts['msg']);
        $html->setType(\Laminas\Mime\Mime::TYPE_HTML);
        $html->setCharset('UTF-8');
        $html->setEncoding(
            $opts['encoding'] ?? \Laminas\Mime\Mime::ENCODING_QUOTEDPRINTABLE
        );
        $body = new MimeMessage();
        $body->addPart($html);
        
        // Attach file
        if (isset($opts['attachment']) && is_array($opts['attachment']) ) {
            foreach ($opts['attachment'] as $file){
                $filePath = $file['fullPath'] ?? $file['tmp_name'];
                if( !isset($file['type']) ){
                    $file['type'] = filetype($filePath);
                }
                $attachment = new MimePart( file_get_contents($filePath) );
                $attachment->type = $file['type'];
                $attachment->filename = basename(self::noMark($file['name']));
                $attachment->disposition = \Laminas\Mime\Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = \Laminas\Mime\Mime::ENCODING_BASE64;
                $body->addPart($attachment);
            }
            unset($attachment);
        }
        
        $message->setEncoding('UTF-8');
        $message->setBody($body);
        if ( false == empty($opts['to']) )
            $message->addTo($opts['to'], $opts['toName'] ?? '');
            
        $message->setSubject($opts['title'] ?? '');
        
        // -- cc
        if ( false == empty($opts['cc']))
            $message->addCc($opts['cc'], $opts['ccName'] ?? '');
        // -- bcc
        if ( false == empty($opts['bcc']) )
            $message->addBcc($opts['bcc'], $opts['bccName'] ?? '');
            
        $transport->send($message);

        if ( define('IS_BG_SERVICE') && IS_BG_SERVICE ) {
            $transport->getConnection()->noop();
        }
    }
    /**
     * Escap Vietnam char
     *
     * @param string $string            
     * @return string
     */
    public static function noMark($string)
    {
        if (! $string)
            return '';
        $utf8 = array(
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'D' => 'Đ',
            'd' => 'đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ'
        );
        foreach ($utf8 as $ascii => $uni)
            $string = preg_replace("/($uni)/", $ascii, $string);
        return $string;
    }

    /**
     * Save log
     *
     * @param string $msg            
     */
    protected static function _logError($msg, $zAdapter = null)
    {
        try {
            if( $zAdapter instanceof \Doctrine\DBAL\Connection){
                $zAdapter->insert(
                    'tbl_log_error_sendmail', [
                    'log_error_email'   => self::$_config['username'] ?? '',
                    'log_error_content' => $msg,
                    'log_error_time'    => time()
                ]);
            }else{
                // Create Service
                $sqlService = new Sql($zAdapter);
                $insert = $sqlService->insert('tbl_log_error_sendmail');
                $insert->columns([
                    'log_error_email',
                    'log_error_content',
                    'log_error_time'
                ])->values([
                    'log_error_email' => self::$_config['username'] ?? '',
                    'log_error_content' => $msg,
                    'log_error_time' => time()
                ]);
                
                $sqlService->prepareStatementForSqlObject($insert)->execute();
                unset($sqlService, $insert);
            }
        } catch (\Throwable $e) {}
    }
}
?>