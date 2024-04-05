<?php
namespace GrootSwoole;

/**
 * Elasticsearch
 * @author TungTs 2022/07/21
 */
class ESearchManager
{
    /**
     * Set pw
     */
    protected $_isPassword = false;
    protected $_username = '';
    protected $_password = '';

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
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->_elasticaConfigs = $configs;
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
            
        $configs = $this->_elasticaConfigs[$indexName] ?? [];

        $this->_elasticaAddress = ($configs['host'] ?? 'http://172.17.0.1:9200')
        . '/'
        . $configs['index'] . '/';

        if (isset($configs['username']) && isset($configs['password'])) {
            $this->_isPassword = true;
            $this->_username = $configs['username'];
            $this->_password = $configs['password'];
        } else {
            throw new \Exception("Invalid indexName `{$indexName}` in configuration.");
        }

        return $this;
    }

    /**
     * @param array $params
     * @param string $strUrlExt
     * @param string $method
     */
    public function query($params, $strUrlExt = '', $method = 'POST')
    {
        try {
            if (!$this->_isPassword) 
                throw new \Exception(
                    'Missing authentication credentials'
                );

            if ($strUrlExt == '_reindex' || $strUrlExt == '_bulk')
                $url = preg_replace('/\/[^\/]+\/$/', '/'. $strUrlExt, $this->_elasticaAddress);
            else
                $url = $this->_elasticaAddress . $strUrlExt;

            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->request($method, $url, [
                'auth'    => [
                    $this->_username,
                    $this->_password
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => $params
            ]);

            $rsCode  = $response->getStatusCode();
            if ( empty($rsCode) ) {
                throw new \Exception(
                    'Server elastic not respone!'
                );
            }

            return $response->getBody()->getContents();

        } catch(\Throwable $e) {
            throw new \Exception(
                $e->getMessage(), $e->getCode()
            );
        }
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
        $response = $this->query(json_encode($arrDocuments), '_doc/' . $intId);
        return $response;
    }

     /**
     * Update index to search engine
     * 
     * @param array $arrDocuments
     */
    public function updateDocument(array $arrDocuments, $intId = 0)
    {
        return $this->query(json_encode([
            'doc' => $arrDocuments
        ]), '_update/' . $intId);
    }

    /**
     * Delete index
     * 
     * @return array
     */
    public function deleteIndex()
    {
        $response = $this->query('', '', 'DELETE');
        return json_decode($response, true);
    }

    /**
     * Delete document by id
     * ID here means the value of the uniqueKey field declared in the schema
     * 
     * @param integer $intId
     */
    public function deleteDocumentById($intId)
    {
        return $this->query('', '_doc/' . $intId, 'DELETE');
    }

    /**
     * Delete multi document
     * 
     * @param array $arrData
     * @return array
     */
    public function deleteMultiDocument(array $arrData = [])
    {
        $response = $this->query(json_encode($arrData), '_delete_by_query');
        return json_decode($response, true);
    }

    /**
     * Setting & mapping before index
     * 
     * @param array $arrMap
     * @return array
     */
    public function setting(array $arrMap)
    {
        return json_decode($this->query(
            json_encode($arrMap), '', 'PUT'
        ), true);
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
            json_encode($arrMap), '_mapping', 'PUT'
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
            json_encode($arrBulk), '_bulk'
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
            json_encode($arrReindex), '_reindex'
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
        $response = $this->query(json_encode($arrSearch), '_search', 'GET');
        return json_decode($response, true);
    }

    /**
     * Analyze
     * 
     * @param array $arrData
     * @return array
     */
    public function analyze(array $arrData)
    {
        return json_decode($this->query(
            json_encode($arrData), '_analyze'
        ), true);
    }
    
}
