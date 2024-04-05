<?php
namespace Models\Repositories;

class Post extends \Doctrine\ORM\EntityRepository {
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterStatus( $qb, $val ){
        return $qb->andWhere('P.p_status = :status')
        ->setParameter('status', $val)
        ;
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterCate_id( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('P.p_cate_id IN(:cate_ids)')
            ->setParameter('cate_ids', $val);
        
        return $qb->andWhere('P.p_cate_id = :cate_id')
        ->setParameter('cate_id', $val);
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterUrl( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('P.p_url IN(:urls)')
            ->setParameter('urls', $val);
        
        return $qb->andWhere('P.p_url = :url')
        ->setParameter('url', $val);
    }
    
	/**
	 * @param array $params
	 * @return Array | Doctrine\ORM\Query
	 *
	 * @author PhapIt 13.08.2018
	 */
	public function fetchOpts( $opts = array() ) {
	    
		$qb = $this->getEntityManager()->createQueryBuilder()
			->select('P')
			->from($this->getEntityName(), 'P')
		;
		
		// Filter
		if ( $opts['params'] && is_array($opts['params']) ){
		    foreach ($opts['params'] as $key => $val){
		        $this->{"_filter" . ucfirst($key)}($qb, $val);
		    }
		}
		
		// Order
		if ( $opts['order'] && is_array($opts['order']) ){
		    foreach ($opts['order'] as $col => $mode)
		        $qb->addOrderBy("P.p_" . $col, $mode);
		}else
		  $qb->addOrderBy('P.p_time', 'DESC');
		  
		// -- Result
		switch ($opts['resultMode']){
		    case 'Array': return $qb->getQuery()->getArrayResult(); break;
		    case 'Entity': return $qb->getQuery()->getResult(); break;
		    case 'Query': return $qb->getQuery(); break;
		    case 'QueryBuilder':
		    default: return $qb; break;
		}
	}
	
	/**
	 * Delete post by Url
	 */
	public function delPostByUrl( $opts = [] ){
	    if ( !$opts['url'] ) return false;
	    
	    return $this->getEntityManager()->createQueryBuilder()
	    ->delete('\Models\Entities\Post', 'P')
	    
	    ->where('P.p_url = :url')
	    ->setParameter('url', $opts['url'])
	    
	    ->getQuery()->execute()
	    ;
	}
	
	/**
	 * add post
	 * @author PhapIt 13.08.2018
	 *
	 * @param array $data
	 *
	 * @return \Models\Entities\Post
	 */
	public function insertData( $data  = [] ) {
	    //	Khoi tao thong tin nhom
	    $thumb = new \Models\Entities\Post($data);
	    
	    //	Luu csdl
	    $this->getEntityManager()->persist($thumb);
	    $this->getEntityManager()->flush($thumb);
	    //	Return
	    return $thumb;
	}
	
	/**
	 * edit post
	 *
	 * @param \Models\Entities\Post $post
	 * @param array $updateData
	 *
	 * @return \Models\Entities\Post
	 *
	 * @author PhapIt 13.08.2018
	 */
	public function updateData(\Models\Entities\Post $post, $updateData) {
	    $post->fromArray($updateData);
	    $this->getEntityManager()->flush($post);
	    //	Return
	    return $post;
	}
}
?>