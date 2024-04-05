<?php
namespace Models\Repositories;
use Models\Entities;

class News extends \Doctrine\ORM\EntityRepository {
    const CACHE_KEY = 'news';
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param bigint || array $val
     */
    protected function _filterIds( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('NEWS.news_id IN(:ids)')
            ->setParameter('ids', $val);

        return $qb->andWhere('NEWS.news_id = :id')
        ->setParameter('id', $val);
    }
    
    protected function _filterCodes( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('NEWS.news_code IN(:codes)')
            ->setParameter('codes', $val);
            
            return $qb->andWhere('NEWS.news_code = :code')
            ->setParameter('code', $val);
    }
    
    protected function _filterNot_id($qb, $val){
        return $qb->andWhere(
            $qb->expr()->neq('NEWS.news_id', $val)
        );
    }
    
    protected function _filterStr_ids($qb, $val){
        return $qb->andWhere('NEWS.news_id IN('.$val.')');
    }
    
    protected function _filterLimit($qb, $val){
        return $qb->setMaxResults($val);
    }
    
    
    protected function _filterTitle( $qb, $val ){
        return $qb->andWhere('NEWS.news_title = :news_title')
        ->setParameter('news_title', $val);
    }
    
    protected function _filterStatus( $qb, $val ){
        return $qb->andWhere('NEWS.news_status = :news_status')
        ->setParameter('news_status', $val);
    }
    
    protected function _filterSearch_ft( $qb, $val ){
        $stopword = $this->getFtStopwordCache();
        $val = \Models\Entities\News::convertSearchKeyword($val, $stopword);
        return $qb->setParameter('search_txt', $val)
        ->addOrderBy('score1', 'DESC')
        ->addOrderBy('score2', 'DESC')
        ->addOrderBy('score3', 'DESC')
        ->addSelect(
            "MATCH(NEWS.news_title_search) AGAINST (:search_txt BOOLEAN) AS HIDDEN score1,".
            "MATCH(NEWS.news_tags_search) AGAINST (:search_txt BOOLEAN) AS HIDDEN score2,".
            "MATCH(NEWS.news_search) AGAINST (:search_txt BOOLEAN) AS HIDDEN score3"
        )
        ->andWhere($qb->expr()->exists(
            "SELECT n2.news_id ".
            "FROM Models\Entities\News n2 ".
            "WHERE NEWS.news_id = n2.news_id ".
            "AND MATCH(n2.news_search) AGAINST (:search_txt BOOLEAN) > 0"
        ));
    }
    
    protected function _filterNcate_id( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('NEWS.news_ncate_id IN(:news_ncate_id)')
            ->setParameter('news_ncate_id', $val);
            
        return $qb->andWhere('NEWS.news_ncate_id = :news_ncate_id')
        ->setParameter('news_ncate_id', $val);
    }
    
    protected function _filterNot_img( $qb, $val ){
        return $qb->andWhere('NEWS.news_img <> :news_not_img')
        ->setParameter('news_not_img', $val);;
    }
    
    
    /**
     * @param array $params
     * @return Array | Doctrine\ORM\Query
     * @author Sy 06.03.2019
     */
    public function fetchOpts( $opts = [] ) {
        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select('NEWS')
        ->from($this->getEntityName(), 'NEWS')
        ;
        // Filter
        if ( empty($opts['params']) == false && is_array($opts['params']) ){
            foreach ($opts['params'] as $key => $val){
                $this->{"_filter" . ucfirst($key)}($qb, $val);
            }
        }
        // Only for test
//         \Zend\Debug\Debug::dump($qb->getParameters());
//         die($qb->getQuery()->getSQL());
        // Order
        if ( empty($opts['order']) == false && is_array($opts['order']) ){
            foreach ($opts['order'] as $col => $mode){
                $qb->addOrderBy('NEWS.news_' . $col, $mode);
            }
        }else{
            $qb->addOrderBy('NEWS.news_id', 'DESC');
        }
        
        
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
    
    public function countByCateIds($cateIds){
        $cateIds = array_filter($cateIds, function($item){
            return (int)$item > 0;
        });
            
        if(empty($cateIds)){
            return 0;
        }
        
        $cnn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT COUNT(news_id) FROM tbl_news WHERE news_ncate_id IN (". implode(',', $cateIds).")";
        $rs = $cnn->fetchColumn($sql);
        return  empty($rs) ? 0 : $rs;
    }
    
    /**
     * Insert data
     * @author Sy 06.03.2019
     * @param array $data
     * @return \Entities
     */
    public function insertData( $data  = [] ) {
        //	Khoi tao thong tin nhom
        $thumb = new Entities\News($data);
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
    public function updateData(\Models\Entities\News $entity, $updateData) {
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
    public function deleteData(\Models\Entities\News $entity){
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
        $repoNewsTag = $this->getEntityManager()->getRepository(Entities\NewsTag::class);
        
        //subtract use tag
        $repoNewsTag->subtractUseCountByNewsIds($ids);
        
        $news_list = $cnn->fetchAllAssociative("SELECT news_img FROM tbl_news WHERE news_id IN (?)", [$ids], [
            \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
        ]);
        $imgs = array_map(function($item){ return $item['news_img'];}, $news_list);
        //delete news
        $rs = $cnn->executeStatement("DELETE FROM tbl_news WHERE news_id IN (?)", [$ids], [
            \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
        ]);
        $this->clearDataFromCache();
        
        //delete img
        $this->deleteImgByNames($imgs);
        
        return $rs;
    }
    
    /**
     * Delete images by news ids
     * @param array|string $ids
     * @return boolean
     */
    public function deleteImgByNames($names){
        if(empty($names)){
            return 0;
        }
        $count = 0;
        foreach ($names as $item){
            if(empty(trim($item)) == false){
                @unlink(Entities\News::getImgPath($item));
                $count++;
            }
        }
        return $count;
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
            ->indexBy('NEWS', 'NEWS.news_id')
            ->getQuery()->getArrayResult();
            $cache->setItem($key, $items);
        }unset($cache);

        if ( empty($limit) == false ){
            return array_slice($items, (int)$offset, $limit);
        }
        return $items;
    }
    
    /**
     * Get stopword list of fulltext search
     * @return array
     */
    public function getFtStopwordCache(){
        $key = self::CACHE_KEY . '_ft_stopword';
        $cache = $this->getCacheCore($key);
        $items = $cache->getItem($key);
        if( $items == null ){
            $items = $this->getEntityManager()
            ->getConnection()
            ->fetchAllAssociative('SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD;');
            $items = array_map(function($item){ return $item['value']; }, $items);
            $cache->setItem($key, $items);
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
    
    /**
     * Get related news (same tag to multiply 3 with 1 tag, same middle category to plus 1)
     * @param array $params
     * @return \Doctrine\DBAL\Driver\mixed[]|array
     */
    public function getRelatedNews($params){
        $obj = $params['obj'];
        $news_id = $obj->news_id;
        $news_ncate_id = $obj->news_ncate_id;
        $tag_ids = implode(',', $params['tag_ids']);
        
        $countTag = "SELECT COUNT(ntr_news_id) FROM tbl_news_tag_relation WHERE ntr_news_id = news_id AND ntr_news_tag_id IN ({$tag_ids})";
        $tags_count = empty($tag_ids) ? '0' : "IFNULL(({$countTag}), 0)";
        $query = "SELECT @tags_count := $tags_count AS tags_count,".
                "(@tags_count * 3 + IF(news_ncate_id = {$news_ncate_id}, 1, 0)) AS news_point,".
                "n.* ".
                "FROM tbl_news n WHERE news_id <> {$news_id} AND news_status = 1 HAVING news_point > 0 ORDER BY news_point DESC, news_view_count DESC LIMIT 4";
        return $this->getEntityManager()->getConnection()
        ->executeQuery($query)->fetchAll();
    }
    
    
    /**
     * Get random list (4 record) by category id list
     * @param integer $cate_ids
     * @return \Doctrine\DBAL\Driver\mixed[]|array
     */
    public function getRandomByCateIds($cate_ids){
        $cate_ids = is_array($cate_ids) ? implode(',', $cate_ids) : $cate_ids;
        $column_order = ['news_ncate_id'];
        $query = "SELECT * FROM tbl_news WHERE news_ncate_id IN({$cate_ids}) ORDER BY RAND() LIMIT 4";
        return $this->getEntityManager()->getConnection()
        ->executeQuery($query)->fetchAll();
    }
    
    /**
     * Increase view for news and news category
     * @param integer $news_id
     * @param integer $ncate_id
     * @return \Doctrine\DBAL\Driver\ResultStatement|\Doctrine\DBAL\Driver\Statement
     */
    public function plusViewById($news_id, $ncate_id){
        $query = 'UPDATE tbl_news SET news_view_count = news_view_count + 1, news_week_view_count = news_week_view_count + 1 WHERE news_id = ' . $news_id;
        $query .= ';UPDATE tbl_news_category SET ncate_view_count = ncate_view_count + 1 WHERE ncate_id = ' . $ncate_id;
        return $this->getEntityManager()
        ->getConnection()
        ->executeUpdate($query);
    }
    
    /**
     * Change status by category news 
     * (all news of category will hidden if category status hidden)
     * @param integer $ncate_id
     * @param boolean $ncate_status
     * @return number|\Doctrine\DBAL\Driver\ResultStatement|\Doctrine\DBAL\Driver\Statement
     */
    public function changeStatusByCateStatus($ncate_id, $ncate_status){
        if($ncate_status){
            return 0;
        }else{
            $query = 'UPDATE tbl_news SET news_status = 0 WHERE news_ncate_id = ' . (int)$ncate_id;
        }
        $rs = $this->getEntityManager()->getConnection()->executeUpdate($query);
        $this->clearDataFromCache();
        return $rs;
    }
    
    /**
     * Update all week view into 0
     * @return number
     */
    public function clearWeekView(){
        $query = 'UPDATE tbl_news SET news_week_view_count = 0';
        return $this->getEntityManager()
        ->getConnection()
        ->executeUpdate($query);
    }
}