<?php
namespace Models\Repositories;
use Models\Entities;

/**
 * @author PhapIt
 */
class Constant extends \Doctrine\ORM\EntityRepository {
    
	/**
	 * Tra ve hang so theo ma hang so. 
	 * @param string $constantCode Ma hang so.
	 * @param string $languageCode Mã ngôn ngữ.
	 * @return \Entities\Constant
	 */
	private function _fetchConstant($constantCode, $languageCode = null){
		// Create query
		$queryBuilder = $this->getEntityManager()->createQueryBuilder()
			->select('C')
			->from($this->_entityName, 'C')
			->where('C.constant_code = :constant_code')
			->setParameter('constant_code', $constantCode);
		
		return $queryBuilder->getQuery()->getResult();
	}
	
	/**
	 * Lay noi dung cua hang so
	 * @param string $constantCode Ma hang so.
	 * @return string
	 */
	public function fetchConstantContent($constantCode, $isJson = false){
		$constant = $this->_fetchConstant($constantCode);
		if ( true == $isJson )
		    return @json_decode($constant[0]->constant_content, true);
		return $constant[0]->constant_content;
	}
	
	/**
	 * Lay noi dung cua hang so
	 * @param string $constantCode Ma hang so.
	 * @return string
	 */
	public function fetchBadKeywords( $isArray = true ){
	    
	    $constant = $this->_fetchConstant('system_bad_keyword');
	    $content = $constant[0]->constant_content ?? '';
	    
	    if ( true == $isArray ){
	        $content = explode(',', $content);
	        return array_map(function($val){
	            return trim($val);
	        }, $content);
	    }
	    
	    return $content;
	}
	
	/**
	 * Lay noi dung cua hang so
	 * @param string $constantCode Ma hang so.
	 * @return string
	 */
	public function fetchTmplMail( $constantCode = '' ){
	    $constant = $this->getEntityManager()
	    ->getRepository('\Models\Entities\Constant')
	    ->findOneBy([ 'constant_code' => $constantCode ]);
	    if ( $constant )
	       return [
	           'content' => $constant->constant_content,
	           'sender' => $constant->constant_sender,
	           'title' => $constant->constant_title,
	           'receiver' => array_filter(explode(';', $constant->constant_receiver))
	       ];
	    return [];
	}
	
	/**
	 * Lay danh sach hang so
	 * @param array $options
	 *  
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getListData( $options = array() )
	{
		// Create query
		$qb = $this->getEntityManager()->createQueryBuilder();
		$expr = $qb->expr();
		$qb->select('C')
			->from($this->getEntityName(), 'C')
		;
		
		// Tu khoa
		if("" != $options["keyword"]){
			$orEx = $expr->orX(
				$expr->like('C.constant_code', ":keyword")
			    //,$expr->like('C.constant_name', ":keyword")
			);
			$qb->andWhere($orEx)->setParameter("keyword", "%{$options["keyword"]}%");
		}
		
		if( "" != $options["preventCode"] ){
		    $qb->andWhere('C.constant_code <> :prevent_code')
		    ->setParameter("prevent_code", $options["preventCode"]);
		}
			
		if( "" != $options["preventType"] ){
		    $qb->andWhere('C.constant_type <> :constant_type')
		    ->setParameter("constant_type", $options["preventType"]);
		}
		
		$qb->addOrderBy("C.constant_code", "ASC")
		   ->addOrderBy("C.constant_creation_time", "DESC")
		;
		
		// Test only
        /* \Zend\Debug\Debug::dump($qb->getParameters());
        die($qb->getQuery()->getSQL()); */
		
		// Return
		return $qb;
	}
	
	/**
	 * Them moi thong tin hang so
	 * @author Mr.Phap 21.03.2017
	 *
	 * @param array $insertData Du lieu them moi
	 * @return \Entities\Constant
	 */
	public function insertConstant($insertData = array()) {
		//	Khoi tao thong tin hang so
		$constantE = new Entities\Constant($insertData);
	
		//	Luu csdl
		$this->getEntityManager()->persist($constantE);
		$this->getEntityManager()->flush($constantE);
	
		//	Return
		return $constantE;
	}
	
	/**
	 * Cap nhat hang so
	 *
	 * @author Mr.Phap 21.03.2017
	 */
	public function updateConstant(Entities\Constant $constantE, $updateData) {
		/** Xoa thong tin khong duoc phep cap nhat */
		unset($updateData['constant_code'], $updateData['constant_type']);
		$updateData['constant_last_update_time'] = time();
		//	Cap nhat thong tin nhom tai khoan
		$constantE = $constantE->fromArray($updateData);
	
		//	Luu csdl
		$this->getEntityManager()->flush($constantE);
	
		//	Return
		return $constantE;
	}
	
	/**
	 * Dem so luong app da cai dat 
	 */
	public function appCount( $opts = [] ) {
	    return $this->getEntityManager()
	    ->getConnection()
	    ->executeQuery('INSERT INTO `tbl_app_count` (`ac_type`,`ac_time`) VALUES (
	        :ac_type, :ac_time
	    );', [ 'ac_type' => $opts['type'], 'ac_time' => time() ]);
	}
	
	/**
	 * Dem so luong app da cai dat
	 */
	public function getAppCount( $opts = [] ) {
	    $sql = 'SELECT COUNT(*) FROM `tbl_app_count` WHERE ';
	    $adapter = $this->getEntityManager()->getConnection();
	    $where = []; $params = [];
	    if ( $opts['from_time'] ){
	        $where[] = 'ac_time >= :from_time';
	        $params['from_time'] = $opts['from_time'];
	    }
	    
	    if ( $opts['to_time'] ){
	        $where[] = 'ac_time <= :to_time';
	        $params['to_time'] = $opts['to_time'];
	    }
	    
	    if ( $opts['device'] 
	        && in_array($opts['device'], ['ANDROID', 'IOS', 'UNKNOW']) ){
	        $where[] = 'ac_type = :ac_type';
	        $params['ac_type'] = $opts['device'];
	    }
	    
	    return $adapter->fetchColumn( $sql . implode(' AND ', $where), $params);
	}
	
	public function getByCodes($codes = []){
	    $cnn = $this->getEntityManager()->getConnection();
	    $sql = 'SELECT constant_id, constant_code, constant_content, constant_title FROM tbl_constant WHERE constant_code IN (?)';
	    return $cnn->fetchAll($sql, [$codes], [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]);
	}
	
}