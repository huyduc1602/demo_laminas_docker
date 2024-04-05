<?php
namespace Models\Repositories;
use Models\Entities;
use Models\Entities\NewsTag;

class NewsTagRelation extends \Doctrine\ORM\EntityRepository {
    const CACHE_KEY = 'news_tag_relation';
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param bigint || array $val
     */
    protected function _filterIds( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('NTR.ntr_id IN(:ids)')
            ->setParameter('ids', $val);

        return $qb->andWhere('NTR.ntr_id = :id')
        ->setParameter('id', $val);
    }
    
    protected function _filterNot_id($qb, $val){
        return $qb->andWhere(
            $qb->expr()->neq('NTR.ntr_id', $val)
        );
    }
    
    protected function _filterNews_id($qb, $val){
        return $qb->andWhere('NTR.ntr_news_id = :ntr_news_id')
        ->setParameter('ntr_news_id', $val);
    }
    
    /**
     * Insert multi data sql
     * @param Array $data
     * @return integer The number of affected rows.
     */
    public function insertMultiDataSql( $news_id, $tags ){
        $news_id = (int)$news_id;
        $params = [];
        $tags_sql = '';
        foreach ($tags as $key => $tag){
            $tags_sql .= ",:tagIM{$key}";
            $params['tagIM'.$key] = $tag;
        }
        $cnn = $this->getEntityManager()->getConnection();
        $tags_sql = ltrim($tags_sql, ',');
        if(empty($tags_sql)){
            return 0;
        }
        $this->deleteByNewsId($news_id);
        $sql = "INSERT INTO tbl_news_tag_relation (ntr_news_id, ntr_news_tag_id) ".
                "SELECT {$news_id},ntg_id FROM tbl_news_tag WHERE ntg_name IN ({$tags_sql})";
        $rs = $cnn->executeUpdate($sql, $params);
        $this->clearDataFromCache();
        return $rs;
    }
    
    /**
     * Delete by news id
     * @param unknown $news_id
     * @return number
     */
    public function deleteByNewsId( $news_id ){
        $news_id = (int)$news_id;
        $sql = "DELETE FROM tbl_news_tag_relation WHERE ntr_news_id = {$news_id}";
        $rs = $this->getEntityManager()->getConnection()
        ->executeUpdate($sql);
        $this->clearDataFromCache();
        return $rs;
    }
    
    /**
     * @param array $params
     * @return Array | Doctrine\ORM\Query
     * @author Sy 06.03.2019
     */
    public function fetchOpts( $opts = [] ) {
        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select('NTR')
        ->from($this->getEntityName(), 'NTR')
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
                $qb->addOrderBy('NTR.ntr_' . $col, $mode);
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
        $thumb = new Entities\NewsTag($data);
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
    public function updateData(\Models\Entities\NewsTag $entity, $updateData) {
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
    public function deleteData(\Models\Entities\NewsTag $entity){
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
        $rs = $cnn->executeUpdate('DELETE FROM tbl_news_tag WHERE ntr_id IN (' .implode(',', $ids). ')');
        $this->clearDataFromCache();
        return $rs;
    }
    
    
    /**
     * Get Zend Cache
     * @return Laminas\Cache\Storage\StorageInterface
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
            ])
            ->indexBy('NTR', 'NTR.ntr_id')
            ->getQuery()->getArrayResult();
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