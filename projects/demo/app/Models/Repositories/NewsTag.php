<?php
namespace Models\Repositories;

use Models\Entities;

class NewsTag extends \Doctrine\ORM\EntityRepository
{

    const CACHE_KEY = 'news_tag';

    /**
     *
     * @param Doctrine\ORM\QueryBuilder $qb            
     * @param
     *            bigint || array $val
     */
    protected function _filterIds($qb, $val)
    {
        if (is_array($val))
            return $qb->andWhere('NTG.ntg_id IN(:ids)')->setParameter('ids', $val);
        
        return $qb->andWhere('NTG.ntg_id = :id')->setParameter('id', $val);
    }

    protected function _filterNot_id($qb, $val)
    {
        return $qb->andWhere($qb->expr()
            ->neq('NTG.ntg_id', $val));
    }

    protected function _filterNot_name($qb, $val)
    {
        return $qb->andWhere('NTG.ntg_name <> :not_name')->setParameter('not_name', $val);
    }

    protected function _filterStr_ids($qb, $val)
    {
        return $qb->andWhere('NTG.ntg_id IN(' . $val . ')');
    }

    protected function _filterLimit($qb, $val)
    {
        return $qb->setMaxResults($val);
    }

    protected function _filterSearch($qb, $val)
    {
        return $qb->andWhere($qb->expr()
            ->like('NTG.ntg_key_name', $qb->expr()
            ->literal("%{$val}%")));
    }
    
    protected function _filterFrom_use_count($qb, $val){
        return $qb->andWhere('NTG.ntg_use_count > :use_count')->setParameter('use_count', $val - 1);;
    }

    protected function _filterSearch_ft($qb, $val)
    {
        return $qb->setParameter('tag_search_txt', $val)
            ->addOrderBy('score1', 'DESC')
            ->addSelect("MATCH(NTG.ntg_name_search) AGAINST (:tag_search_txt BOOLEAN) AS HIDDEN score1")
            ->andWhere($qb->expr()
            ->exists("SELECT nt2.ntg_id " . "FROM Models\Entities\NewsTag nt2 " . "WHERE NTG.ntg_id = nt2.ntg_id " . "AND MATCH(nt2.ntg_name_search) AGAINST (:tag_search_txt BOOLEAN) > 0"));
    }

    /**
     * Insert multi data
     * 
     * @param Array $data            
     * @return integer The number of affected rows.
     */
    public function insertMultiData($increase = [], $decrease = [])
    {
        $params = [];
        $result = 0;
        foreach ($increase as $key => $data) {
            $values_sql .= "(:tagVal{$key}, :tagValSearch{$key}, 1),";
            $params['tagVal' . $key] = $data;
            $params['tagValSearch' . $key] = \Models\Entities\News::convertSearchString($data);
        }
        
        if (empty($values_sql) == false) {
            $sql = 'INSERT INTO tbl_news_tag (ntg_name, ntg_name_search, ntg_use_count) VALUES ' . trim($values_sql, ',') . ' ON DUPLICATE KEY UPDATE ntg_use_count = ntg_use_count + 1';
            $result = $this->getEntityManager()
                ->getConnection()
                ->executeUpdate($sql, $params);
        }
        if (empty($decrease) == false) {
            $result += $this->subtractUseCountByNames($decrease);
        }
        $this->clearDataFromCache();
        return $result;
    }

    /**
     *
     * @param array $params            
     * @return Array | Doctrine\ORM\Query
     * @author Sy 06.03.2019
     */
    public function fetchOpts($opts = [])
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('NTG')
            ->from($this->getEntityName(), 'NTG');
        
        // Filter
        if (empty($opts['params']) == false && is_array($opts['params'])) {
            foreach ($opts['params'] as $key => $val) {
                $this->{"_filter" . ucfirst($key)}($qb, $val);
            }
        }
        
        // Order
        if (empty($opts['order']) == false && is_array($opts['order'])) {
            foreach ($opts['order'] as $col => $mode) {
                $qb->addOrderBy('NTG.ntg_' . $col, $mode);
            }
        }
        
        // Only for test
        /*
         * \Zend\Debug\Debug::dump($qb->getParameters());
         * die($qb->getQuery()->getSQL());
         */
        
        // -- Result
        switch ($opts['resultMode'] ?? '') {
            case 'Array':
                return $qb->getQuery()->getArrayResult();
                break;
            case 'Entity':
                return $qb->getQuery()->getResult();
                break;
            case 'Query':
                return $qb->getQuery();
                break;
            case 'QueryBuilder':
            default:
                return $qb;
                break;
        }
        return $qb;
    }

    /**
     * Insert data
     * 
     * @author Sy 06.03.2019
     * @param array $data            
     * @return \Entities
     */
    public function insertData($data = [])
    {
        // Khoi tao thong tin nhom
        $thumb = new Entities\NewsTag($data);
        // Luu csdl
        $this->getEntityManager()->persist($thumb);
        $this->getEntityManager()->flush($thumb);
        $this->clearDataFromCache();
        // Return
        return $thumb;
    }

    /**
     * Update data
     * 
     * @author Sy 12.03.2019
     * @param array $data            
     * @return \Entities
     */
    public function updateData(\Models\Entities\NewsTag $entity, $updateData)
    {
        $entity->fromArray($updateData);
        $this->getEntityManager()->flush($entity);
        $this->clearDataFromCache();
        return $entity;
    }

    /**
     * Delete data
     * 
     * @author D.Sy 30/11/2018
     * @param array $entity            
     * @return \Entities
     */
    public function deleteData(\Models\Entities\NewsTag $entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
        $this->clearDataFromCache();
        return $entity;
    }

    /**
     * Get Zend Cache
     * 
     * @return Laminas\Cache\Storage\StorageInterface
     */
    protected function getCacheCore($key = self::CACHE_KEY)
    {
        return \Zf\Ext\CacheCore::_getCaches($key, [
            'lifetime' => false,
            'namespace' => self::CACHE_KEY
        ]);
    }

    /**
     * Get data from cache
     * 
     * @param array $opts            
     * @return array
     */
    public function getDataFromCache($opts = [])
    {
        $key = self::CACHE_KEY;
        $params = empty($opts['params']) ? [] : $opts['params'];
        $order = empty($opts['order']) ? [] : $opts['order'];
        unset($params['limit'], $params['offset']);
        if (empty($params) == false) {
            foreach ($params as $param) {
                $key .= '_' . (is_array($param) ? implode('_', $param) : $param);
            }
        }
        $key = crc32($key);
        $cache = $this->getCacheCore();
        $items = $cache->getItem($key);
        $limit = empty($opts['params']['limit']) ? 0 : $opts['params']['limit'];
        $offset = empty($opts['params']['offset']) ? 0 : $opts['params']['offset'];
        unset($opts['params']['limit'], $opts['params']['offset']);
        if (null == $items) {
            $items = $this->fetchOpts([
                'resultMode' => 'QueryBuilder',
                'params' => $params,
                'order' => $order
            ])
                ->indexBy('NTG', 'NTG.ntg_id')
                ->getQuery()
                ->getArrayResult();
            $cache->setItem($key, $items);
        }
        unset($cache);
        
        if (empty($limit) == false) {
            return array_slice($items, (int) $offset, $limit);
        }
        return $items;
    }

    /**
     * Clear cache
     * 
     * @param array $opts            
     * @return bool
     */
    public function clearDataFromCache($opts = [])
    {
        $path = implode(DIRECTORY_SEPARATOR, [
            DATA_PATH,
            'cache',
            self::CACHE_KEY
        ]);
        if (realpath($path) && is_dir($path)) {
            // Delete directory on Window
            if (in_array(PHP_OS, [
                'WINNT',
                'Windows'
            ])) {
                @system('rd /s /q ' . escapeshellarg($path), $retval);
            } else {
                @system('rm -rf ' . escapeshellarg($path), $retval);
            }
            return $retval == 0;
        }
        return false;
    }

    /**
     * Subtract 1 view by tag names
     * 
     * @param array $ids            
     * @return number
     */
    public function subtractUseCountByNames($names)
    {
        if (empty($names)) {
            return 0;
        }
        $cnn = $this->getEntityManager()->getConnection();
        return $cnn->executeUpdate("UPDATE tbl_news_tag SET ntg_use_count = ntg_use_count - 1 WHERE ntg_name IN (?)", [
            $names
        ], [
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ]);
    }

    /**
     * Subtract number of view by news ids
     * 
     * @param array $ids            
     * @return number
     */
    public function subtractUseCountByNewsIds($ids)
    {
        $sql = "UPDATE tbl_news_tag " . "SET ntg_use_count =  IF((@use_count := (ntg_use_count - (SELECT COUNT(ntr_news_id) FROM tbl_news_tag_relation WHERE ntr_news_id IN (:ids) AND ntr_news_tag_id = ntg_id))) > 0, @use_count, 0)";
        return $this->getEntityManager()
            ->getConnection()
            ->executeUpdate($sql, [
            ids => $ids
        ], [
            ids => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
        ]);
    }
}