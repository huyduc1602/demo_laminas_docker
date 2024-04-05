<?php

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
/**
 * Elasticsearch
 * @author TungTs 2022/07/21
 */
class ZfESearchManager extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getESearchManager';

    /**
     * Set pw
     */
    protected $_isPassword = false;
    protected $_username = '';
    protected $_password = '';
    protected $verify = false;
    /**
     * Elastic address
     * @var string
     */
    protected $_elasticaAddress = '127.0.0.1';

    /**
     * @var array
     */
    protected $_elasticaConfigs = null;

    /**
     * ZfESearchManager constructor.
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {

        if (!$this->_elasticaConfigs) {
            $thisOptions = $container->get('config');
            $this->_elasticaConfigs = $thisOptions['elasticsearch'] ?? [];
        }
    }

    /**
     * ZfESearchManager destructor.
     */
    public function __destruct()
    {
        unset($this->_elasticaConfigs, $this->_elasticaAddress);
    }

    /**
     * @param string $_indexName
     * @return $this | bool
     * @throws \Exception
     */
    public function __invoke(string $indexName)
    {
        if (!isset($this->_elasticaConfigs[$indexName]))
            throw new \Exception("Invalid indexName `{$indexName}` in configuration.");

        $configs = $this->_elasticaConfigs[$indexName]['connection'] ?? [];

        $this->_elasticaAddress = ($configs['host'] ?? 'http://172.17.0.1:9200') . '/';

        if (isset($configs['username']) && isset($configs['password'])) {
            $this->_isPassword = true;
            $this->_username = $configs['username'];
            $this->_password = $configs['password'];
        } else {
            throw new \Exception("Invalid indexName `{$indexName}` in configuration.");
        }

        $this->verify = $configs['verify'] ?? false;

        return $this;
    }
    const DEFAULT_TIMEOUT = 10;
    /**
     * @param array $params
     * @param string $strUrlExt
     * @param string $method
     */
    public function query($params, $strUrlExt = '', $method = 'POST', $isGetBody = true)
    {
        $opts = [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            RequestOptions::HTTP_ERRORS     => false,
            RequestOptions::CONNECT_TIMEOUT => self::DEFAULT_TIMEOUT,
            RequestOptions::READ_TIMEOUT    => self::DEFAULT_TIMEOUT,
            RequestOptions::TIMEOUT         => self::DEFAULT_TIMEOUT,
        ];

        if ($params){
            $opts[RequestOptions::JSON] = $params;
        }
        if ( $this->_isPassword ) $opts[RequestOptions::AUTH] = [
            $this->_username,
            $this->_password
        ];

        if ($strUrlExt == '_reindex' || $strUrlExt == '_bulk')
            $url = preg_replace('/\/[^\/]+\/$/', '/'. $strUrlExt, $this->_elasticaAddress);
        else
            $url = $this->_elasticaAddress . $strUrlExt;
        $client = new Client(['verify' => $this->verify]);

        $response = $client->request($method, $url, $opts);

        $rsCode  = $response->getStatusCode();

        if ( empty($rsCode) ) {
            throw new \Exception(
                'Server elastic not response!'
            );
        }

        if (!$isGetBody) return $rsCode;

        $body = $response->getBody()->getContents() ?? '[]';
        unset($client, $client, $response);
        return is_numeric($body) ? $body : @json_decode($body, true);
    }

    /**
     * Add document to search engine
     * 
     * @param array $arrDocuments
     * @param int documentId
     * @return
     */
    public function indexDocument(array $arrDocuments, $intId = '')
    {
        return $this->query($arrDocuments, '_doc/' . $intId);
    }

     /**
     * Update index to search engine
     * 
     * @param array $arrDocuments
     */
    public function updateDocument(array $arrDocuments, $intId = 0)
    {
        return $this->query([
            'doc' => $arrDocuments
        ], '_update/' . $intId);
    }

    /**
     * Delete index
     * 
     * @return array
     */
    public function deleteIndex()
    {
        return $this->query([], '', 'DELETE');
    }

    /**
     * Delete document by id
     * ID here means the value of the uniqueKey field declared in the schema
     * 
     * @param integer $intId
     */
    public function deleteDocumentById($intId)
    {
        return $this->query([], '_doc/' . $intId, 'DELETE');
    }

    /**
     * Delete multi document
     * 
     * @param array $arrData
     * @return array
     */
    public function deleteMultiDocument(array $arrData = [])
    {
        return $this->query($arrData, '_delete_by_query');
    }

    /**
     * Setting & mapping before index
     * 
     * @param array $configs
     * @return array
     */
    public function setting(array $configs, $index)
    {
        return $this->query($configs, $index, 'PUT');
    }

    public function switchToKuromojiIdx($index)
    {
        return $this->setting([
            'settings' => [
                'index' => [
                    'analysis' => [
                        'kuromoji_normalize' => [
                            'char_filter' => ['icu_normalizer'],
                            'tokenizer'   =>  'kuromoji_tokenizer',
                            'filter' => [
                                'kuromoji_baseform',
                                'kuromoji_part_of_speech',
                                'cjk_width',
                                'ja_stop',
                                'kuromoji_stemmer',
                                'lowercase'
                            ]
                        ]
                    ]
                ]
            ]
        ], $index);
    }

    public function switchIcuIdx($index)
    {
        return $this->setting([
            'settings' => [
                'index' => [
                    'analysis' => [
                        'analyzer' => [
                            'nfkc_cf_normalized' => [
                                'tokenizer'  => 'icu_tokenizer',
                                'char_filter'=> ['icu_normalizer'],
                                'filter' => ['lowercase']
                            ],
                            'nfd_normalized' => [
                                'tokenizer'  => 'icu_tokenizer',
                                'char_filter'=> ['lowercase','nfd_normalizer']
                            ]
                        ],
                        'char_filter' => [
                            'nfd_normalizer' => [
                                'type' => 'icu_normalizer',
                                'name' => 'nfc',
                                'mode' => 'decompose'
                            ]
                        ]
                    ]
                ]
            ]
        ], $index);
    }

    /**
     * Mapping data when exist index name
     * 
     * @param array $arrMap
     * @return
     */
    public function map(array $arrMap)
    {
        return $this->query(
            $arrMap, '_mapping', 'PUT'
        );
    }

    /**
     * Bulking
     * 
     * @param array $arrBulk
     * @return
     */
    public function bulk(array $arrBulk)
    {
        return $this->query(
            $arrBulk, '_bulk'
        );
    }

    /**
     * Reindex 
     * @param array $arrMap
     * @return 
     */
    public function reindex(array $arrReindex)
    {
        return $this->query(
            $arrReindex, '_reindex'
        );
    }

    /**
     * Search 
     * 
     * @param array $arrSearch
     * @return array
     */
    public function search(array $arrSearch = [])
    {
        return yield $this->query(
            $arrSearch, '_search', 'GET'
        );
    }

    /**
     * Analyze
     * 
     * @param array $arrData
     * @return array
     */
    public function analyze(array $arrData)
    {
        return $this->query(
            $arrData, '_analyze'
        );
    }

    public function alias($configs)
    {
        return $this->query(
            $configs, '_aliases', 'POST'
        );
    }

    /**
     * @param string $index Index name need to alias
     * @param string $newName New name
     * @param array $filter Ex: ['filter' => [your configs], 'routing' => 'customer_ID', 'search_routing' => 'Your_data_ID']
     * @return mixed
     */
    public function addAlias(string $index, string $newName, array $filter)
    {
        return $this->alias([
            'actions' => [
                'add' => array_replace($filter, [
                    'index' => $index,
                    'alias' => $newName
                ]),
            ]
        ]);
    }

    /**
     * @param string $index Index name need to focus
     * @param string $alias Name of alias
     * @return mixed
     */
    public function delAlias(string $index, string $alias)
    {
        return $this->alias([
            'actions' => [
                'remove' => ['index' => $index, 'alias' => $alias],
            ]
        ]);
    }
    public function isIdxExist(string $idx) {
        return 200 === $this->query(null, $idx, 'HEAD', false);
    }
    public function sqlFind(
        $columns,
        $index,
        $wheres = ['1=1'],
        $orderBys = [],
        $groupBy = [],
        int $limit = 100,
        string $indexByCol = null,
        string $cursor = ''
    ){
        if (!empty($indexByCol) && false === ($indexBy = array_search($indexByCol, $columns))){
            throw new \Exception("Column `{$indexByCol}` not in list columns select.");
        }
        $res = $this->query( [
            'query' => 'SELECT ' . implode(',', $columns) .
                ' FROM ' . $index .

                ' WHERE ' .implode(' AND ', $wheres) .

                (empty($orderBys) ? '' : ' ORDER BY ' . implode(',', $orderBys)) .

                (empty($groupBy) ? '' : ' GROUP BY ' . implode(',', $groupBy)),
            'fetch_size' => $limit,
            'cursor' => $cursor

        ], '_sql?format=json', 'POST');

        $rs = [];
        foreach (($res['rows'] ?? []) as $row) {
            $i = 0; $item = [];
            foreach ($columns as $col)
                $item[$col] = $row[$i++];
            if (is_numeric($indexBy??''))
                $rs[$row[$indexBy]] = $item;
            else $rs[] = $item;
        }
        return [
            'items' => $rs,
            'cursor' => $res['cursor'] ?? '',
        ];
    }
}
