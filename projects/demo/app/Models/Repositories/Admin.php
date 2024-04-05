<?php
namespace Models\Repositories;

class Admin extends \Doctrine\ORM\EntityRepository {
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filterAdmin_id( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('A.admin_id IN(:admin_ids)')
            ->setParameter('admin_ids', $val);
        
        return $qb->andWhere('A.admin_id = :admin_id')
        ->setParameter('admin_id', $val);
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filterGroupcode( $qb, $val ){
        if ( is_array($val) )
            return $qb->andWhere('A.admin_groupcode IN(:groupcodes)')
            ->setParameter('groupcodes', $val);
        return $qb->andWhere('A.admin_groupcode = :groupcode')
        ->setParameter('groupcode', $val);
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterStatus( $qb, $val ){
        return $qb->andWhere('A.admin_status = :admin_status')
        ->setParameter('admin_status', $val);
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterEmail( $qb, $val ){
        return $qb->andWhere('A.admin_email LIKE :admin_email')
        ->setParameter('admin_email', "%{$val}");
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterFullname( $qb, $val ){
        return $qb->andWhere('A.admin_fullname LIKE :admin_fullname')
        ->setParameter('admin_fullname', "%{$val}");
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterPhone( $qb, $val ){
        return $qb->andWhere('A.admin_phone LIKE :admin_phone')
        ->setParameter('admin_phone', "%{$val}");
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterKeyword( $qb, $val ){
        
        // --- Find company name in tbl_keyword
        $subQuery = $this->getEntityManager()
        ->createQueryBuilder()
        ->select('AK')
        ->from('\Models\Entities\AdminKw', 'AK')
        ->innerJoin(
            '\Models\Entities\Keyword', 'Kw',
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'Kw.kw_id = AK.ak_kw_id'
        )
        ->where('Kw.kw_str LIKE :kw_str')
        ->andWhere('A.admin_id = AK.ak_admin_id')
        ;$exp = $qb->expr();
    
        // Subquery
        return $qb
        ->andWhere($exp->exists($subQuery))
        ->setParameter('kw_str', "{$val}%")
        ;
    }
    
	/**
	 * Lay danh sach Admin
	 * @param array $params Dieu kien tim kiem
	 * @return Array | Doctrine\ORM\Query
	 *
	 * @author PhapIt 11.04.2017
	 */
	public function fetchOpts( $opts = array() ) {
	    
		/** Tao cau truy van lay danh sach nhan vien theo dieu kien tim kiem */
		$qb = $this->getEntityManager()->createQueryBuilder()
			->select('A')
			->from($this->getEntityName(), 'A')
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
		        $qb->addOrderBy("A.admin_" . $col, $mode);
		}else
		  $qb->addOrderBy('A.admin_create_time', 'DESC');
		
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
	 * Them moi Admin
	 * @author PhapIt 11.04.2017
	 * 
	 * @param array $data Du lieu them moi
	 * 
	 * @return \Entities\Admin
	 */
	public function insertData( $data  = array() ) {
		//	Khoi tao thong tin nhom
		$admin = new \Models\Entities\Admin($data);
		
		//	Luu csdl
		$this->getEntityManager()->persist($admin);
		$this->getEntityManager()->flush($admin);
		
		//	Return
		return $admin;
	}

	/**
	 * Cap nhat thong tin Admin
	 * 
	 * @param \Entities\Admin $admin
	 * @param array $updateData
	 * 
	 * @return \Entities\Admin
	 * 
	 * @author PhapIt 11.04.2017
	 */
	public function updateData(\Models\Entities\Admin $admin, $updateData) {
		//	Cap nhat thong tin nhom tai khoan
		$admin->fromArray($updateData);
		
		//	Luu csdl
		$this->getEntityManager()->flush($admin);
		
		//	Return
		return $admin;
	}
	
	/**
	 * Luu ky tu vao bang tim kiem
	 * @param array $keywords
	 * @param integer $idAdmin
	 */
	public function insertKeyWord( $keywords = array(), $idAdmin, $type = 'NAME' ){
	
	    if ( !$idAdmin )
	        throw new \Exception('Id user can not be null!', 500);
	
	    $no = count($keywords);
	    if ( $no <= 0 ) return false;
	
	    // Get adapter
	    $zAdapter = $this->getEntityManager()->getConnection();
	
	    //$queryKw = 'SELECT * FROM `tbl_keyword` WHERE (`kw_str` = ';
	    $clearOld = 'DELETE FROM `tbl_admin_kw` WHERE `ak_admin_id` = ' . $idAdmin;
	
	    //$this->getEntityManager()->beginTransaction();
	    try {
	
	        // --- Xoa du lieu cu
	        $zAdapter->delete('tbl_admin_kw', ['ak_admin_id' => $idAdmin]);
	         
	        $repo = $this->getEntityManager()->getRepository('\Models\Entities\Keyword');
	         
	        while ( $no > 0 ){
	            $kw    = strtolower(implode('', $keywords));
	            $kwEn  = $repo->findOneBy(array( 'kw_str' => $kw ));
	
	            // -- Luu thong tin tu khoa
	            if( !$kwEn ){
	                $zAdapter  ->insert('tbl_keyword', array('kw_str' => $kw));
	                $id        = $zAdapter->lastInsertId('tbl_keyword');
	            }else $id      = $kwEn->kw_id;
	
	            $rs = $zAdapter->insert('tbl_admin_kw', array(
	                'ak_admin_id' => $idAdmin, 
	                'ak_kw_id' => $id,
	                'ak_type' => $type
	            ));
	            array_shift($keywords);$no--;
	        }
	        //$this->getEntityManager()->commit();
	
	    }catch (\Exception $e){
	        //$this->getEntityManager()->rollBack();
	    }
	
	    unset($zAdapter, $repo);
	}
	
	/**
	 * Lay thong tin cong ty theo ten
	 * @param array $opts
	 * @return array
	 */
	public function quickSearch( $opts = array() ){
	
	    // Neu keyword = null thi tra ve mang rong
	    if ( !$opts['keyword'] ) return array();
	
	    // -- limit 20 items
	    $limit = $opts['limit'] ? $opts['limit'] : 30;
	    // -- offset
	    $offset = isset($opts['offset']) ? $opts['offset'] : 0;
	
	    // Select
	    $selects = 'partial T.{admin_id,admin_fullname}';
	    if ( $opts['cstCols'] && is_array($opts['cstCols']) ){
	        $selects = 'partial T.{' . implode(',', $opts['cstCols']) . '}';
	    };
	
	    // --- Find company name in tbl_keyword
	    $subQuery = $this->getEntityManager()
	    ->createQueryBuilder()
	    ->select('A', 'Kw')
	    ->from('\Models\Entities\AdminKw', 'AK')
	    ->innerJoin(
	        '\Models\Entities\Keyword', 'Kw',
	        \Doctrine\ORM\Query\Expr\Join::WITH,
	        'Kw.kw_id = AK.ak_kw_id'
	    )
	    ->where('Kw.kw_str LIKE :kw_str')
	    ->andWhere('A.admin_id = AK.ak_admin_id')
	    ;
	
	    // Create query
	    $mainQuery = $this->getEntityManager()
	    ->createQueryBuilder()
	    ->select($selects)
	    ->from('\Models\Entities\Admin', 'A')
	    ;$exp = $mainQuery->expr();
	
	    // Subquery
	    $mainQuery
	    ->where($exp->exists($subQuery))
	    ->setParameter('kw_str', "{$opts['keyword']}%")
	    ;
	
	    if ( isset($opts['status']) )
	        $mainQuery
	        ->andWhere('T.admin_status = :admin_status')
	        ->setParameter('admin_status', $opts['status']);
	
	    // Limit
	    $mainQuery
	    ->setMaxResults($limit)
	    ->setFirstResult($offset);
	
	    // Bind result
	    $rs = $mainQuery->getQuery()->getArrayResult();
	    $result = array();
	
	    if ( !$opts['cstCols'] )
	        foreach ($rs as $row) $result[ $row['admin_id'] ] = $row['admin_fullname'];
	    else{
	        foreach ($rs as $row) $result[ $row['admin_id'] ] = $row;
	    }
	    return $result;
	}
	
	
	public function clearSamary(){
	    // List of table for delete
	    $dels = [
	        'tbl_amount_log'=> '1=1',
	        'tbl_pay_report'=> '1=1',
	        'tbl_payment'   => '1=1',
	        'tbl_pay_user'  => '1=1',
	    ];$sql = [];
	     
	    foreach ($dels as $tbl => $identify )
	        $sql[] = "DELETE FROM `{$tbl}` WHERE {$identify}";
	     
	    return $this->getEntityManager()->getConnection()
	    ->executeQuery(implode(';', $sql))->rowCount();
	}
}

?>