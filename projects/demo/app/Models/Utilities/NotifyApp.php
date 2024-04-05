<?php
namespace Models\Utilities;
use \ZendService\Google\Gcm;
use \ZendService\Apple\Apns;

class NotifyApp
{
    const TIME_TO_LIVE       = 432000;// = 5 * 86400
    const ADROID_PACKAGE_NAME= 'com.jilnesta.askbe';
    const IOS_PACKAGE_NAME   = 'com.jilnesta.askbe';
    
    /**
     * @param string $gcmApiKey
     * @param array $ids
     * @param string $msg
     * @param string $data
     * @param array $opts
     * @return boolean[]|string[]|NULL[][]|array[]
     */
    public static function androidMsg( $gcmApiKey = '', $ids = [], $msg = false, $data = false, $opts = [] ){
        $client = new Gcm\Client();
        $client->setApiKey($gcmApiKey);
        
        $message = new Gcm\Message();
        
        if ($msg) $message->setNotification($msg);
        if ($data) $message->setData($data);
        
        if ( APPLICATION_VERSION == 'vtest' ){
            $message->setPriority('high');
        }
        
        $message->setRegistrationIds($ids);
        // --- IOS device ---
        if ( isset($opts['is_ios']) && true === $opts['is_ios'] ){
            $message->setRestrictedPackageName(self::IOS_PACKAGE_NAME);
            $message->addNotification('content_available', true);
            $message->addNotification('sound', 'default');
            /* ---- For release
            $msgKey = ['body' => '', 'title' => '', 'bag' => 0, 'icon' => ''];
            $apsMsg = array_intersect_key($data, $msgKey);
            
            if ( $msg && is_array($msg) ) $apsMsg = array_merge($apsMsg, $msg);
            if ( $apsMsg ) $message->setNotification($apsMsg);
            
            if ( $data ) $message->setData(array_diff_key($data, $msgKey));
            */
        }
        else {
            // ---- For release -------
            /*if ($msg) $message->setNotification($msg);
            if ($data) $message->setData($data);*/
            $message->setRestrictedPackageName(self::ADROID_PACKAGE_NAME);
        }
        
        $message->setTimeToLive(self::TIME_TO_LIVE);
        $message->setDelayWhileIdle(false);
        $message->setDryRun(false);
        
        $rs = [];
        try {
            $response = $client->send($message);
            $rs = [
                'status' => true,
                'count' => [
                    'successCount' => $response->getSuccessCount(),
                    'failCount' => $response->getFailureCount(),
                    'canonicalCount' => $response->getCanonicalCount(),
                ],
                'result' => $response->getResults()
            ];
        } catch (\ZendService\Google\Exception\RuntimeException $e) {
            $rs = [
                'status'=> false,
                'msg'   => $e->getMessage() . PHP_EOL
            ];
        }
        
        unset($client, $response, $message);
        return $rs;
    }
    
    /**
     * Token not registered
     * @var string
     */
    const Android_Error_NotReg = 'NotRegistered';
    /**
     * Remove all client has fail status
     * @param array $items
     * @param DoctrineRepository $clientRepo
     * @return integer
     */
    public static function handleAndroidResp( $items = [], $clientRepo ){
        $tokens = []; $total = 0;
        foreach ($items as $item){
            if ( $item['status'] ){
                $total += $item['count']['successCount'];
                // Find error token
                if ( $item['count']['failCount'] > 0 ){
                    foreach ($item['result'] as $token => $error )
                        if ( $error['error'] == self::Android_Error_NotReg ) $tokens[] = $token;
                }
            }
        }
        if( count($tokens) > 0 ) $clientRepo->removeOldToken($tokens);
        return $total;
    }
    
    // ------------------------- IOS ----------------------------------
    /**
     * Certificate key
     * @var string
     */
    const APNS_PassPhrase = 'abcd1234';
    /**
     * Send notify for IOS device 
     * @param string $token
     * @param array $msg
     * @param string $msgId
     * @param string $cert
     */
    public static function iosMsg( $tokens = [], $msg = [], $msgId = '', $cert = 'askbe_production_APNS.pem' ){
        $client = new Apns\Client\Message();
        $envir = Apns\Client\Message::PRODUCTION_URI;
        if ( APPLICATION_VERSION == 'vtest' ){
            $cert = 'askbe_test_APNS.pem';
            $envir = Apns\Client\Message::SANDBOX_URI;
        }
        $client->open(
            $envir, //Apns\Client\Message::SANDBOX_URI, 
            DATA_PATH . '/certificate/apns/'. $cert, 
            self::APNS_PassPhrase
        );
        
        $message = new Apns\Message();
        // my_unique_id
        if ( '' != $msgId )
            $message->setId($msgId);
        
        $bags = $msg['bag']; $msg = $msg['msg'];
        // Set message
        if ( $msg['body'] || $msg['title'] ){
            $alert = new Apns\Message\Alert();
            $alert->setBody($msg['body']);
            $alert->setTitle($msg['title']);
            $message->setAlert($alert);
            unset($msg['title'], $msg['body']);
        }
        
        // Set data
        if ( count($msg) > 0 ) $message->setCustom($msg);
        $message->setExpire(self::TIME_TO_LIVE);
        $message->setSound('default');
        //$message->setBadge(1);
        $message->setContentAvailable(1);
        
        $result = [];
        try {
            
            foreach ($tokens as $token){
                $message->setBadge(
                    max([$bags[$token], 1])
                );
                
                // DEVICE_TOKEN
                $message->setToken($token);
                $response = $client->send($message);
                
                $result[$token] = [
                    'status'    => Apns\Response\Message::RESULT_OK === $response->getCode(),
                    'code'      => $response->getCode(),
                    'message_id'=> $response->getId()
                ];
            }
            
            $client->close();
            $rs = [
                'status'=> true,
                'result' => $result
            ];
        } catch (\ZendService\Apple\Exception\RuntimeException $e) {
            $rs = [
                'status' => false,
                'msg'    => $e->getMessage() . PHP_EOL,
                'result' => $result
            ];
        }
        
        return $rs;
    }
    /**
     * Token not registered
     * @var int
     */
    const IOS_Error_NotReg = 410;
    /**
     * Remove all client has fail status
     * @param array $items
     * @param DoctrineRepository $clientRepo
     * @return integer
     */
    public static function handleIosResp( $items = [], $clientRepo ){
        $tokens = []; $total = 0;
        // Find error token
        foreach ($items as $token => $item){
            if ( !$item['status'] ){
                if ( $item['code'] == self::IOS_Error_NotReg
                    || Apns\Response\Message::RESULT_INVALID_TOKEN 
                    || Apns\Response\Message::RESULT_INVALID_TOKEN_SIZE ) $tokens[] = $token;
            }else $total++;
        }
        if( count($tokens) > 0 ) $clientRepo->removeOldToken($tokens);
        return $total;
    }
}