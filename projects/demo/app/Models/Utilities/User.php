<?php
namespace Models\Utilities;
use \Models\Utilities as NotifiUtil;
use \Models\Entities\Client;
class User
{
    /**
     * Get data from custom configs file
     * @param bool $isEdit: Get data for edit
     * @return array
     */
    public static function getApiConfigsFile($isEdit = false)
    {
        $isGetAllData = ($isEdit ?? true) ? false : true;

        return [
            'dropbox' => [
                'folder' => DROPBOX_FOlDER,
                'configs' => self::getDropBoxOpts()
            ],
            'gt_mng' => self::getGoogleTagManagers($isGetAllData),
        ];
    }

    /**
     * Decode google tags managers configs
     * @param bool $currentVersion
     * @return []
     */
    public static function getGoogleTagManagers( $currentVersion = true ){
        // Get configs of all version
        if ( false === $currentVersion ) return array_map(function($item){
            return self::decodeStr($item);
        }, GOOGLE_TAG_MANAGER);

        // Get config by current Version
        return self::decodeStr(GOOGLE_TAG_MANAGER[APP_ENV_VERSON]);
    }

    /**
     * Path to config file
     * @var string
     */
    const CUSTOM_CONSTANT = '/custom/application.constant.php';

    /**
     * Save data to custom configs file
     * @param array $data
     * @return number (byte)
     */
    public static function setApiConfigsFile($data){
        $oldData = self::getApiConfigsFile(true);

        $items = [
            'DROPBOX_OPTS'  => self::encodeStr(json_encode($data['dropbox']['configs'])),
            'DROPBOX_FOlDER'=> $data['dropbox']['folder'],
            'GOOGLE_TAG_MANAGER' => array_map(function($item){
                return \Models\Utilities\User::encodeStr($item);
            }, array_replace(
                $oldData['gt_mng'] ?? [],
                [APP_ENV_VERSON => $data['gt_mng']]
            )),
        ];
        $filePath = CONFIG_PATH . self::CUSTOM_CONSTANT;

        $byteCount = 0;
        // Clear data
        @file_put_contents($filePath, '');
        $myfile = fopen($filePath, "w+")  ;

        $byteCount += fwrite($myfile, "<?php\n");
        foreach ( $items as $key => $item ){
            if ( is_array($item) ){
                $byteCount += fwrite($myfile, vsprintf("defined ('%s') || define('%s', [\n", [$key, $key]));
                foreach ( $item as $subKey => $val ){
                    $byteCount += fwrite($myfile, vsprintf("'%s' => '%s',\n", [$subKey, $val]));
                }
                $byteCount += fwrite($myfile, "]);\n");
            }else
                $byteCount += fwrite($myfile, vsprintf("defined ('%s') || define('%s', '%s');\n", [$key, $key, $item]));
        }

        fwrite($myfile, "?>\n");
        fclose($myfile);

        return $byteCount;
    }
    /**
     * Decode dropbox configs
     * @return []
     */
    public static function getDropBoxOpts(){
        return @json_decode(self::decodeStr(DROPBOX_OPTS), true);
    }
    /**
     * Make random string
     * @param number $len
     * @return string
     */
    public static function makeRandStr( $len = 9){
        return \Laminas\Math\Rand::getString(
            $len,
            'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            true
        );
    }
    /**
     * Salt length
     * @var integer
     */
    const SALT_KEY = 9;
    /**
     * Decode string
     * @param string $str
     * @return string
     */
    public static function decodeStr($str = ''){
        if ( empty($str) ) return '';
        return gzuncompress(base64_decode(substr($str, self::SALT_KEY)));
    }
    
    /**
     * Encode string
     * @param string $str
     * @return string
     */
    public static function encodeStr($str = ''){
        if ( empty($str) ) return '';
        return self::makeRandStr(self::SALT_KEY) . base64_encode(gzcompress($str));
    }
    /**
     * Make order fullpath
     * @param string $fromCote
     * @param string $toCode
     * @return string
     */
    public static function createPath( $fromCote, $toCode ){
        return implode('/', [
            DATA_PATH,
            \Models\Entities\User::ORDER_FOLDER,
            $fromCote . '_' . $toCode . '.txt'
        ]);
    }
    
    /**
     * Lay thong tin file lan dau mua hang cua user
     * @param array $opts
     * <p>
     *  owner (String): user code, user created lesson
     *  user (String): user code, user bought lesson
     * </p>
     * @return false || array
     */
    public static function getOrderFile( $opts = [] ){
        if( !$opts['owner'] || !$opts['user'] ){
            return false;
        }

        $path = self::createPath($opts['owner'], $opts['user']);
        if ( file_exists($path) ){
            return @file_get_contents($path);
        }
        
        $path = self::createPath($opts['user'], $opts['owner']);
        if ( file_exists($path) ){
            return @file_get_contents($path);
        }
        return false;
    }
    /**
     * Create order file if not exists
     * @param array $opts
     * <p>
     *  owner (String): user code, user created lesson
     *  user (String): user code, user bought lesson
     *  data (String): Mixed data
     * </p>
     * @return bool
     */
    public static function generalOrderFile($opts = []){
        if( !$opts['owner'] || !$opts['user'] ){
            return false;
        }
        
        $path = self::createPath($opts['owner'], $opts['user']);
        if ( !file_exists($path) ){
            return is_numeric(file_put_contents($path, $opts['data'] ?? '')) ;
        }

        return true;
    }
    /**
     * Kiem tra xem user hien tai da tuong mua 1 bai hoc
     * nao do cua giao vien hay chua
     * @param array $opts
     * @return bool
     */
    public static function isBoughtALesson( $opts = [] ){
        return false !== self::getOrderFile($opts);
    }

    /**
     * Calc rate point
     * @param number $point
     */
    public static function calcAvgRate( $point = 0){
        $int = (int)$point;
        if ( ($point - $int) >= 0.3 ) return "{$int}-5";
        return $int;
    }

    /**
     * Render Rate
     * @param number $point
     * @param array $opts
     * @return string
     */
    public static function renderAvgRate( $point = 0, $opts = [] ){
        $defaultClass = 'rating text-warning float-left';
        $attrs = [];
        if ( isset($opts['u_attrs']) && is_array($opts['u_attrs']) ){
            $opts['u_attrs']['class'] .= ' '. $defaultClass;
            foreach ($opts['u_attrs'] as $key => $attr)
                $attrs[$key] = "{$key}=\"{$attr}\"";
        }else $attrs['class'] = "class=\"{$defaultClass}\"";
        $attrs = implode(' ', $attrs);

        $ul = ["<ul {$attrs}>", '<li class="clearfix"></li>'];
        if ( $point > 0 ){
            $int = (int)$point;
            $ul[] = str_repeat(
                '<li class="float-left"><i class="fa fa-star" aria-hidden="true"></i></li>',
                $int
            );
            $mode = ($point - $int);
            if ( $mode > 0 )
                $ul[] = '<li class="float-left"><i class="fa fa-star-half-full" aria-hidden="true"></i></li>';
            $last = 5 - ceil($mode) - $int;
            if ( $last > 0 )
                $ul[] = str_repeat(
                    '<li class="float-left"><i class="fa fa-star-o" aria-hidden="true"></i></li>',
                    $last
                );
        }else{
            $ul[] = str_repeat(
                '<li class="float-left"><i class="fa fa-star-o" aria-hidden="true"></i></li>',
                5
            );
        }
        $ul[] = '<li class="clearfix"></li>';
        $ul[] = '</ul>';
        return implode('', $ul);
    }

    /**
     * Push notification
     * @param \Doctrine\ORM\EntityManager $dbAdapter
     * @param array $clients
     * @param array $msg
     * @return array
     */
    public static function pushNotify( $dbAdapter, $clients = [], $msg = [] ){

        if ( $clients ){

            // Get server info
            $serverInfors = $dbAdapter->getRepository('\Models\Entities\Server')
            ->getDataFromCache([
                'params' => [
                    'status' => \Models\Entities\Server::STATUS_ACTIVE
                ]
            ]);

            $clientByTypes = []; $browserType = 'BROWSER';
            foreach ( $clients as $client ){
                if ( in_array($client['client_type'], ['IOS', 'ANDROID']) ){
                    $token = $client['client_auth_token'];
                    $clientByTypes[$client['client_type']][$token] = $token;
                }else $clientByTypes[$browserType][] = $client;
            }

            // Send for android device
            if ( $clientByTypes[Client::TYPE_ANDROID] ){
                $playload = $msg; unset($playload['link']);
                $rs[Client::TYPE_ANDROID] = NotifiUtil\NotifyApp::handleAndroidResp([
                    NotifiUtil\NotifyApp::androidMsg(
                        // Server key
                        $serverInfors[Client::TYPE_ANDROID]['private_key'],

                        // Client token
                        array_values($clientByTypes[Client::TYPE_ANDROID]),

                        // Message
                        null,

                        // Data
                        $playload
                    )],
                    $dbAdapter->getRepository('Models\Entities\Client')
                );
            }

            // Send for ios device
            if ( $clientByTypes[Client::TYPE_IOS] ){
                $playload = $msg; unset($playload['link']);
                $rs[Client::TYPE_IOS] = NotifiUtil\NotifyApp::handleAndroidResp([
                    NotifiUtil\NotifyApp::androidMsg(
                        // Server key
                        $serverInfors[Client::TYPE_ANDROID]['private_key'],

                        // Client token
                        array_values($clientByTypes[Client::TYPE_IOS]),

                        // Message
                        $playload,

                        // Data
                        null,

                        ['is_ios' => true]
                    )],
                    $dbAdapter->getRepository('Models\Entities\Client')
                );
            }

            // Web device
            if ( $clientByTypes[$browserType] ){
                if ( empty($msg['time']) ){
                    $msg['time'] = date('Y-m-d\TH:i:sP', time());
                }
                if ( empty($msg['type']) ){
                    $msg['type'] = \Models\Utilities\ChatRoom::TYPE_BROWSER_PUSH;
                }
                $msg['tag'] = base64_encode(random_bytes(19));
                // -- Max length is 1024
                //$msg['body'] = mb_substr($msg['body'], 0, 1024);

                $rs[$browserType] = NotifiUtil\ChatRoom::handleWebRespone(
                    $dbAdapter->getRepository('Models\Entities\Client'),
                    NotifiUtil\ChatRoom::webPushNotify(
                        // Server infor
                        $serverInfors[$browserType],

                        // Clients
                        $clientByTypes[$browserType],

                        // Message
                        $msg,

                        // Multi user
                        true
                    ),
                    $clientByTypes[$browserType]
                );
            }
            return $rs;
        }
        return [];
    }

    const A8_BASE_URL = 'https://px.a8.net/a8fly/earnings';
    /**
     * Create local request
     */
    public static function localRequest( $params = [] ){
        $rs = file_get_contents(
            //'https://px.a8.net/a8fly/earnings?'
            self::A8_BASE_URL
            .'?'
            . http_build_query($params)
        );
        return [
            'code' => 200,
            'body' => $rs
        ];
    }
    
    /**
     * Create request to A8
     * @param array $params
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function makeA8Request( $params = [] ){
        
        $params['pid'] = \Models\Utilities\User::getA8PID();
        try{
            $client = new \GuzzleHttp\Client(); //https://px.a8.net/a8fly/earnings
            $respone = $client->request('GET', self::A8_BASE_URL, [
                'query' => $params
            ]); unset($client);
            
            return [
                'code' => $respone->getStatusCode(),
                'body' => (string)$respone->getBody()
            ];
        }
        catch (\Throwable $e){
            return self::localRequest($params);
        }
    }
    
    const DEFAULT_MONEY_TYPE = 'jpy';
    
    public static function initPJ(){
        // --- Init payjp
        $payjpConfig = self::getPayJPConfigs();
        
        \Payjp\Payjp::setApiKey($payjpConfig['private_key']);
        unset($payjpConfig);
    }

    /**
     * create customer
     * @param array $opts
     * @return \Payjp\Customer
     */
    public static function createPJCustomer( $opts = [] ){
        self::initPJ();
        return \Payjp\Customer::create($opts);
    }
    /**
     * Get customer by id
     * @param string $cId
     * @return \Payjp\Customer
     */
    public static function getPJCustomers( $opts = [] ){
        self::initPJ();
        return \Payjp\Customer::all($opts);
    }
    
    /**
     * Get customer by id
     * @param string $cId
     * @return \Payjp\Customer
     */
    public static function getPJCustomerById( $cId = '' ){
        self::initPJ();
        return \Payjp\Customer::retrieve($cId);
    }

    /**
     * Create card for customer
     * @param string $cId
     * @param string $token
     * @return \Payjp\Card
     */
    public static function editPJCard( $cId = '', $token = '' ){
        $customer = self::getPJCustomerById($cId);
        if ( $customer ){
            // --- Add new card
            $cardNew = $customer->cards->create([
                'card' => $token
            ]); $newId = (string)$cardNew['id'];

            if ( $customer->cards->count > 1 ){
                foreach ($customer->cards['data'] as $card ){
                    if ( $newId != (string)$card['id'] ){
                        $card->delete();
                    }
                }
            }
            return $cardNew;
        } unset($customer);
    }

    /**
     * Get credit card by customer id
     * @param string $cId
     * @return \Payjp\Card
     */
    public static function getPJCardByCustomerId( $cId = '' ){
        if ( !$cId ) return null;
        try{
            $customer = self::getPJCustomerById($cId);
            if ( $customer && $customer->cards['count'] ){
                return array_shift($customer->cards['data']);
            } unset($customer);
        }catch (\Throwable $e){}
    }
    /**
     * User has credit card by customer id
     * @param string $cId
     * @return bool
     */
    public static function hasCardByCustomerId( $cId = '' ){
        if ( !$cId ) return null;
        try{
            return !empty(self::getPJCardByCustomerId($cId));
        }catch (\Throwable $e){}
        return false;
    }
    /**
     * Get credit card by customer id
     * @param string $cId
     * @return \Payjp\Card
     */
    public static function getPJCardInfoByCustomerId( $cId = '' ){
      $card = self::getPJCardByCustomerId($cId);
      if( $card ){
        return [
          'number'    => $card['last4'],
          'exp_date'  => [
              'month' => $card['exp_month'],
              'year'  => $card['exp_year']
          ],
          'owner_name'=> $card['name'],
          'security_code' => '',
        ];
      }
      return [];
    }
    /**
     * Create a payment by specifying the card ID
     * @param array $opts
     * @param string $amount
     * @param string $currency
     * @param array $data
     * @return \Payjp\Charge || null
     */
    public static function makePJChargeByCardId( $opts , $amount = 0, $currency = 'jpy', $data = []  ){
        $amount = (int)$amount;
        if ( empty($opts) || $amount <= 0  ) return null;

        self::initPJ();
        return \Payjp\Charge::create($opts + [
            'amount'   => (int)$amount,
            'currency' => $currency,
            'metadata' => $data
        ]);
    }

    /**
     * get a payment by specifying the card ID
     * @param string $cardId
     * @return \Payjp\Charge || null
     */
    public static function getPJChargeById( $cardId = ''){
        if ( empty($cardId) ) return null;

        self::initPJ();
        return \Payjp\Charge::retrieve($cardId);
    }

    /**
     * Get token infor
     * @param string $token
     * @return \Payjp\Token || null
     */
    public static function getPJToken( $token = '' ){
        if ( !$token ) return null;
        
        self::initPJ();
        return \Payjp\Token::retrieve($token);
    }
    
    
    /**
     * Update charge infor
     * @param string $chargeId
     * @param array $opts
     * @return bool
     */
    public static function updateCharge( $chargeId, $opts = [] ){
        if ( empty($opts) ) return false;
        
        self::initPJ();
        $charge = \Payjp\Charge::retrieve($chargeId);
        if( $charge ){
            foreach ( $opts as $key => $vals ){
                $charge->{$key} = $vals;
            }
            $charge->save(); unset($charge);
            return true;
        }else throw new \Exception('Charge not found.');
    }
    /**
     * Refund charge infor
     * @param string $chargeId
     * @param array $opts
     * @return bool
     */
    public static function refundByChargeId( $chargeId, $opts = [] ){
        if ( empty($opts) ) return false;
        
        self::initPJ();
        $charge = \Payjp\Charge::retrieve($chargeId);
        if( $charge ){
            $charge->refund([
                'amount' => $opts['amount'],
                'refund_reason' => $opts['msg']
            ]);
            return true;
        }else throw new \Exception('Charge not found.');
    }
    /**
     * delete charge id
     * @param array $opts
     * @return bool
     */
    public static function delChargeIdLocal( $opts = [] ){
      // Get old charge
      $path = implode(DIRECTORY_SEPARATOR, [
          DATA_PATH, 'charge', $opts['userCode'], $opts['code']. '.txt'
      ]);
      if( file_exists($path) ) return @unlink($path);
      return false;
    }
    
    /**
     * Save charge id
     * @param array $opts
     * @return string
     */
    public static function getChargeIdLocal( $opts = [] ){
      // Get old charge
      $path = implode(DIRECTORY_SEPARATOR, [
          DATA_PATH, 'charge', $opts['userCode'], $opts['code']. '.txt'
      ]);
      if( @file_exists($path) )
        return @file_get_contents($path);
      return '';
    }

    /**
     * Save charge id
     * @param array $opts
     * @return bool || null
     */
    public static function saveChargeIdLocal( $opts = [] ){
      $basePath = implode(DIRECTORY_SEPARATOR, [
            DATA_PATH, 'charge', $opts['userCode']
      ]);
      if ( false == realpath($basePath) ){
          @mkdir($basePath, 0755, true); @chmod($basePath, 0755);
      }
      return @file_put_contents(implode(DIRECTORY_SEPARATOR, [
          $basePath, $opts['code'] . '.txt'
      ]), $opts['id'], LOCK_EX);
    }
}
