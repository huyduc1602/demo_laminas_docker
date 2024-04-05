<?php
namespace Models\Repositories;
use Models\Entities;

class NewsCategory extends \Doctrine\ORM\EntityRepository {
    const CACHE_KEY = 'news_cate';
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param bigint || array $val
     */
    protected function _filterIds( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('NCATE.ncate_id IN(:ids)')
            ->setParameter('ids', $val);

        return $qb->andWhere('NCATE.ncate_id = :id')
        ->setParameter('id', $val);
    }
    
    protected function _filterNot_id($qb, $val){
        return $qb->andWhere(
            $qb->expr()->neq('NCATE.ncate_id', $val)
        );
    }
    
    protected function _filterStr_ids($qb, $val){
        return $qb->andWhere('NCATE.ncate_id IN('.$val.')');
    }
    
    protected function _filterLimit($qb, $val){
        return $qb->setMaxResults($val);
    }
    
    protected function _filterName( $qb, $val ){
        return $qb->andWhere('NCATE.ncate_name = :ncate_name')
        ->setParameter('ncate_name', $val);
    }
    
    protected function _filterStatus( $qb, $val ){
        return $qb->andWhere('NCATE.ncate_status = :ncate_status')
        ->setParameter('ncate_status', $val);
    }
    
    protected function _filterSearch( $qb, $val ){
        return $qb->andWhere($qb->expr()->like(
            'NCATE.ncate_name', $qb->expr()->literal("%{$val}%"))
        );
    }
    
    protected function _filterNcate_big_cate( $qb, $val ){
        return $qb->andWhere( 'NCATE.ncate_big_cate = :ncate_big_cate' )
        ->setParameter('ncate_big_cate', $val);
    }
    
    /**
     * @param array $params
     * @return Array | Doctrine\ORM\Query
     * @author Sy 06.03.2019
     */
    public function fetchOpts( $opts = [] ) {
        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select('NCATE')
        ->from($this->getEntityName(), 'NCATE')
        ;
        
        // Filter
        if ( empty($opts['params']) == false && is_array($opts['params']) ){
            foreach ($opts['params'] as $key => $val){
                $this->{"_filter" . ucfirst($key)}($qb, $val);
            }
        }
        
        // Order
        if ( empty($opts['order']) == false && is_array($opts['order']) ){
            foreach ($opts['order'] as $col => $mode){
                $qb->addOrderBy('NCATE.ncate_' . $col, $mode);
            }
        }
            
        // Only for test
        /* \Zend\Debug\Debug::dump($qb->getParameters());
         die($qb->getQuery()->getSQL()); */
        
        // -- Result
        switch ($opts['resultMode'] ?? ''){
            case 'Array': return $qb->getQuery()->getArrayResult(); break;
            case 'Entity': return $qb->getQuery()->getResult(); break;
            case 'Query': return $qb->getQuery(); break;
            case 'QueryBuilder':
            default: return $qb; break;
        }
        return $qb;
    }
    
    /**
     * Insert data
     * @author Sy 06.03.2019
     * @param array $data
     * @return \Entities
     */
    public function insertData( $data  = [] ) {
        //	Khoi tao thong tin nhom
        $thumb = new Entities\NewsCategory($data);
        //	Luu csdl
        $this->getEntityManager()->persist($thumb);
        $this->getEntityManager()->flush($thumb);
        $this->clearDataFromCache();
        //	Return
        return $thumb;
    }
    
    /**
     * Update data
     * @author Sy 12.03.2019
     * @param array $data
     * @return \Entities
     */
    public function updateData(\Models\Entities\NewsCategory $entity, $updateData) {
        $entity->fromArray($updateData);
        $this->getEntityManager()->flush($entity);
        $this->clearDataFromCache();
        return $entity;
    }
    
    /**
     * Delete data
     * @author D.Sy 30/11/2018
     * @param array $entity
     * @return \Entities
     */
    public function deleteData(\Models\Entities\NewsCategory $entity){
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
        $this->clearDataFromCache();
        return $entity;
    }
    
    /**
     * Delete data by id
     * @author D.Sy 30/11/2018
     * @param array $entity
     * @return \Entities
     */
    public function deleteDataByIds($ids){
        $ids = array_filter($ids, function($item){
            return (int)$item > 0;
        });
        $cnn = $this->getEntityManager()->getConnection();
        $rs = $cnn->executeUpdate('DELETE FROM tbl_news_category WHERE ncate_id IN (' .implode(',', $ids). ')');
        $this->clearDataFromCache();
        return $rs;
    }
    
    /**
     * Change status by category id
     * @param integer $ncate_id
     * @param boolean $news_status
     * @return number|\Doctrine\DBAL\Driver\ResultStatement|\Doctrine\DBAL\Driver\Statement
     */
    public function changeStatusByNewsStatus($ncate_id, $news_status){
        if($news_status){
            $query = 'UPDATE tbl_news_category SET ncate_status = 1 WHERE ncate_id = ' . (int)$ncate_id;
        }else{
            return 0;
        }
        $rs = $this->getEntityManager()->getConnection()->executeUpdate($query);
        $this->clearDataFromCache();
        return $rs;
    }
    
    /**
     * Get Zend Cache
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected function getCacheCore( $key = self::CACHE_KEY ){
        return \Zf\Ext\CacheCore::_getCaches($key, [
            'lifetime'  => false,
            'namespace' => self::CACHE_KEY
        ]);
    }
    
    /**
     * Get data from cache
     * @param array $opts
     * @return array
     */
    public function getDataFromCache( $opts = [] ){
        $key = self::CACHE_KEY;
        $params = empty($opts['params']) ? [] : $opts['params'];
        $order = empty($opts['order']) ? [] : $opts['order'];
        unset($params['limit'], $params['offset']);
        if ( empty($params) == false ){
            foreach ($params as $param){
                $key .= '_' . (is_array($param) ? implode('_', $param) : $param);
            }
        }
        $key = crc32($key);
        $cache = $this->getCacheCore();
        $items = $cache->getItem($key);
        $limit = empty($opts['params']['limit']) ? 0 : $opts['params']['limit'];
        $offset = empty($opts['params']['offset']) ? 0 : $opts['params']['offset']; 
        unset($opts['params']['limit'], $opts['params']['offset']);
        if ( null == $items ){
            $items = $this->fetchOpts([
                'resultMode' => 'QueryBuilder',
                'params' => $params,
                'order' => $order
            ])
            ->indexBy('NCATE', 'NCATE.ncate_id')
            ->getQuery()->getArrayResult();
            $cache->setItem($key, $items);
        }unset($cache);

        if ( empty($limit) == false ){
            return array_slice($items, (int)$offset, $limit);
        }
        return $items;
    }
    
    /**
     * Get data select from cache
     * @param array $opts
     * @return array
     */
    public function getDataSelectFromCache( $opts = [] ){
        $key = self::CACHE_KEY . '_select';
        $params = empty($opts['params']) ? [] : $opts['params'];
        $order = empty($opts['order']) ? [] : $opts['order'];
        unset($params['limit'], $params['offset']);
        if ( empty($params) == false ){
            foreach ($params as $param){
                $key .= '_' . (is_array($param) ? implode('_', $param) : $param);
            }
        }
        $key = crc32($key);
        $cache = $this->getCacheCore();
        $items = $cache->getItem($key);
        $limit = empty($opts['params']['limit']) ? 0 : $opts['params']['limit'];
        $offset = empty($opts['params']['offset']) ? 0 : $opts['params']['offset']; 
        unset($opts['params']['limit'], $opts['params']['offset']);
        if ( null == $items ){
            $items = $this->fetchOpts([
                'resultMode' => 'QueryBuilder',
                'params' => $params,
                'order' => $order
            ])
            ->indexBy('NCATE', 'NCATE.ncate_id')
            ->getQuery()->getArrayResult();
            $items = array_map(function($item){ return $item['ncate_name'];}, $items);
            $cache->setItem($key, $items);
        }unset($cache);

        if ( empty($limit) == false ){
            return array_slice($items, (int)$offset, $limit);
        }
        return $items;
    }
    
    /**
     * Clear cache
     * @param array $opts
     * @return bool
     */
    public function clearDataFromCache( $opts = [] ){
        $path = implode(DIRECTORY_SEPARATOR, [
            DATA_PATH, 'cache', self::CACHE_KEY
        ]);
        if ( realpath($path) && is_dir($path) ){
            // Delete directory on Window
            if ( in_array(PHP_OS, ['WINNT', 'Windows']) ){
                @system('rd /s /q ' . escapeshellarg($path), $retval);
            }
            else{
                @system('rm -rf ' . escapeshellarg($path), $retval);
            }
            return $retval == 0;
        } return false;
    }
    

}