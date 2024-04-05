<?php
namespace Models\Repositories;

class FEMenu extends \Doctrine\ORM\EntityRepository {

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterMenu_id( $qb, $val ){
        if(is_array($val)){
            return $qb->andWhere('FE.menu_id IN (:menu_id)')
            ->setParameter('menu_id', $val)
            ;
        }
        return $qb->andWhere('FE.menu_id = :menu_id')
        ->setParameter('menu_id', $val)
        ;
    }


    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterMenu_parent_id( $qb, $val ){
        if(is_array($val)){
            return $qb->andWhere('FE.menu_parent_id IN (:menu_parent_id)')
            ->setParameter('menu_parent_id', $val)
            ;
        }
        return $qb->andWhere('FE.menu_parent_id = :menu_parent_id')
        ->setParameter('menu_parent_id', $val)
        ;
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterPosition( $qb, $val ){
        return $qb->andWhere('FE.menu_position = :position')
        ->setParameter('position', $val)
        ;
    }
    
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterDomain( $qb, $val ){
        return $qb->andWhere('FE.menu_domain = :mn_domain')
        ->setParameter('mn_domain', $val)
        ;
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filterParent_id( $qb, $val ){
        return $qb->andWhere('FE.menu_parent_id = :parent_id')
        ->setParameter('parent_id', $val)
        ;
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filterAdmin_id( $qb, $val ){
        return $qb->andWhere('FE.menu_admin_id = :admin_id')
        ->setParameter('admin_id', $val);
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param smallint $val
     */
    protected function _filterStatus( $qb, $val ){
        return $qb->andWhere('FE.menu_status = :status')
        ->setParameter('status', $val);
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filterLevel( $qb, $val ){
        return $qb->andWhere('FE.menu_level = :level')
        ->setParameter('level', $val);
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filterIs_login( $qb, $val ){
        $values = ['YES', 'NO', 'ALL'];
        unset($values[$val]);

        return $qb->andWhere('FE.menu_is_login IN(:is_login)')
        ->setParameter('is_login', $values);
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param bool $val
     */
    protected function _filterParent_null( $qb, $val ){
        if ( $val )
            return $qb->andWhere('FE.menu_parent_id IS NULL');
        return $qb->andWhere('FE.menu_parent_id IS NOT NULL');
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filterLimit( $qb, $val ){
        return $qb->setMaxResults((int)$val)
		->setFirstResult(0);
    }

	/**
	 * @param array $params
	 * @return Array | Doctrine\ORM\Query
	 *
	 * @author PhapIt 15.12.2017
	 */
	public function fetchOpts( $opts = [] ) {

		$qb = $this->getEntityManager()->createQueryBuilder()
			->select('FE')
			->from($this->getEntityName(), 'FE')
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
		        $qb->addOrderBy("FE.menu_" . $col, $mode);
		}else
		  $qb->addOrderBy('FE.menu_order', 'ASC');

		/* \Zend\Debug\Debug::dump($qb->getParameters());
		die($qb->getQuery()->getSQL()); */

		// -- Result
		switch ($opts['resultMode']){
		    case 'Array': return $qb->getQuery()->getArrayResult(); break;
		    case 'Entity': return $qb->getQuery()->getResult(); break;
		    case 'Query': return $qb->getQuery(); break;
		    case 'QueryBuilder':
		    default: return $qb; break;
		}
	}

	const CACHE_KEY = 'fe_menu';
	const CACHE_TREE_KEY = 'tree_key';

	/**
	 * Get Zend Cache
	 * @return Laminas\Cache\Storage\StorageInterface
	 */
	protected function getCacheCore(){
	    return \Zf\Ext\CacheCore::_getCaches(self::CACHE_KEY, [
	        'lifetime' => false,
	        'namespace'=> self::CACHE_KEY
	    ]);
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
	 * Clear cache
	 * @param array $opts
	 * @return bool
	 */
	public function clearTreeDataFromCache( ){
	    $cache = $this->getCacheCore();
	    $cache->removeItem('1_' . self::CACHE_TREE_KEY);
	    $cache->removeItem('0_' . self::CACHE_TREE_KEY);

	    return true;
	}

	/**
	 * Create tree
	 * @param array $items
	 */
	protected function buildTree( $items = [] ){
	    $rs = [];
	    foreach ($items as $item){
	        $item['childs'] = [];
	        $vn = "item" . $item['menu_id'];
	        ${$vn} = $item;
	        if(!is_null($item['menu_parent_id'])) {
	            $vp = "parent" . $item['menu_parent_id'];
	            if(isset($rs[$item['menu_parent_id']])) {
	                ${$vp} = $rs[$item['menu_parent_id']];
	            }
	            else {
	                ${$vp} = ['menu_id' => $item['menu_parent_id'], 'menu_parent_id' => null, 'childs' => []];
	                $rs[$item['menu_parent_id']] = &${$vp};
	            }

	            ${$vp}['childs'][$item['menu_id']] = &${$vn};
	            $rs[$item['menu_parent_id']] = ${$vp};
	        }else
	            $rs[$item['menu_id']] = &${$vn};
	    }
	    return $rs;
	}

	/**
	 * Get tree data from cache
	 * Require order by
	 * @param array $opts
	 * @return array
	 */
	public function getTreeDataFromCache( $opts = [], $keyPrefix = '' ){
	    $key = "{$keyPrefix}_" . self::CACHE_TREE_KEY;
	    $cache = $this->getCacheCore();
	    $items = $cache->getItem($key);
	    if (  null === $items ){

	        $opts['resultMode'] = 'Array';
	        $opts['order'] = [ 'parent_id' => 'ASC', 'order' => 'ASC'];

	        $parentOpts = $opts; $length = \Models\Entities\FEMenu::MAX_DISPLAY_COLUMN;
	        if( $parentOpts['params']['position'] == \Models\Entities\FEMenu::POSITION_FOOTER ){
	            $parentOpts['params'] += [
	                'parent_null' => true, 'level' => '0', 'status' => 1,
	                'limit' => \Models\Entities\FEMenu::MAX_DISPLAY_FOOTER_COLUMN
	            ];
	            $length = null;
	        } else {
	            $parentOpts['params'] += [
	                'parent_null' => true, 'level' => '0',
	                'status' => 1, 'limit' => \Models\Entities\FEMenu::MAX_DISPLAY_ROOT_ITEM
	            ];
	        }

	        $parents = $this->fetchOpts($parentOpts);
	        $opts['params'] = array_merge(
	            $opts['params'], ['parent_null' => false, 'status' => 1]
            );
	        $childs = $this->buildTree(
	            $this->fetchOpts($opts)
            );

	        foreach ($childs as $id => $child) {
	            foreach ($child['childs'] as $idC => $item ){
	                if ( $childs[$idC] ){
	                    $childs[$id]['childs'][$idC]['childs'] = array_values($childs[$idC]['childs']);
	                    unset($childs[$idC]);
	                }
	            }
	        }

	        $rs = [];
	        foreach ( $parents as $parent ){
	            if ( $childs[$parent['menu_id']]['childs'] ){
	               $parent['childs'] = array_slice(
	                   array_values($childs[$parent['menu_id']]['childs']),
	                   0, $length
                   );
	            } else $parent['childs'] = [];
	            $rs[] = $parent;
	        }

	        $cache->setItem($key, $rs);
	        return $rs;
	    }else return $items;
	}

	/**
	 * Get order for add new item
	 * @param Array $opts
	 * @return integer
	 */
	public function getMyOrder( $opts = [] ){
	    $rs = $this->fetchOpts([
	        'params'     => $opts,
	        'resultMode' => 'Array'
	    ]);

	    return count($rs);
	}

	/**
	 * Insert new menu
	 * @param Array $data
	 * @return Entities\FEMenu
	 */
	public function insertData( $data = [] ){
	    $entity = new \Models\Entities\FEMenu($data);

	    $this->getEntityManager()->persist($entity);
	    $this->getEntityManager()->flush($entity);

	    $this->clearDataFromCache();
	    return $entity;
	}

	/**
	 * Edit menu
	 * @param Entities\FEMenu $entity
	 * @param Array $data
	 * @return Entities\FEMenu
	 */
	public function updateData( \Models\Entities\FEMenu $entity, $data = [] ){
	    $entity->fromArray($data);
	    $this->getEntityManager()->flush($entity);

	    $this->clearDataFromCache();
	    return $entity;
	}

	/**
	 * Delete category
	 * @param array $opts
	 * @return number
	 */
	public function deleteData( $opts = [] ) {
	    if( !$opts ) return 0;

	    $adapter = $this->getEntityManager()->getConnection();
	    $idStrs = implode(',', $opts);

	    $sqlGetId = 'SELECT GROUP_CONCAT(tb1.menu_id SEPARATOR \',\') as `ids` FROM tbl_fe_menu AS tb1 WHERE tb1.menu_id IN (:menu_id) OR tb1.menu_parent_id IN (:menu_id)
OR EXISTS (SELECT tb2.menu_id FROM tbl_fe_menu AS `tb2` WHERE tb2.menu_parent_id IN (:menu_id) AND tb2.menu_id = tb1.menu_parent_id)';
	    $ids = $adapter->fetchAssoc(str_replace(':menu_id', $idStrs, $sqlGetId));

	    if( $ids && $ids['ids'] ){
	        $rs = 0;
	        try{
	            $sql = str_replace(
	                ':menu_ids', $ids['ids'],
	                'Delete from tbl_fe_menu where menu_id in(:menu_ids)'
                );
	            $rs = $adapter->executeQuery($sql)->rowCount();
	        }catch (\Throwable $e){
	            throw $e;
	        }

	        $this->clearDataFromCache();
	        return $rs;
	    }

	    return 0;
	}

	/**
	 * Update menu child count
	 * @param array $params
   * <p>
   *  mode: string, [+,-]
   *  id: integer || array
   * </p
   * @return integer
	 */
	public function updateChildCount($params = [] ){
        if ( !in_array($params['mode'], ['-', '+']) ){
            throw new \Exception('Invalid mode. Only accept + or -');
        }
        if ( !$params['id'] ){
            throw new \Exception('Missing id');
        }
        $where = '';
        if(is_array($params['id'])){
            $where = 'menu_id IN ('.implode(',', $params['id']).')';
        }else{
            $where = 'menu_id = ' . $params['id'];
        }
        
        $upDate = 'UPDATE tbl_fe_menu SET menu_child_count = menu_child_count '
            . $params['mode'] . ' 1 WHERE ' . $where;
        
        return $this->getEntityManager()->getConnection()
        ->executeUpdate($upDate);
	}
	
	/**
	 * Kiem tra link da ton tai hay chua
	 * @param array $opts
	 * <p>
	 *     url: string
	 *     type: string
	 * </p>
	 */
	public function checkLinkExists( $opts = [] ){
	    $sql = 'SELECT COUNT(menu_id) as `count` FROM `tbl_fe_menu` WHERE '.
	    ' menu_link = \':url\' AND `menu_type` <> \':type\'';
	    if( $opts['pre_id'] ){
	        $sql .= ' AND menu_id <> :pre_id';
	    }
	    
	    // Test only
	    //die(str_replace([':url', ':type', ':pre_id'],[$opts['url'], $opts['type'], $opts['pre_id']],$sql));
	    
	    return (int)$this->getEntityManager()->getConnection()
	    ->fetchColumn(str_replace(
	        [':url', ':type', ':pre_id'], 
	        [$opts['url'], $opts['type'], $opts['pre_id']], 
	        $sql
        ));
	}
	
	public function updateOrders($orders){
	    if(empty($orders)){
	        return 0;
	    }
	    $sql = '';
	    foreach ($orders as $id => $item){
	        $item = (int)$item;
	        $sql .= "({$id}, {$item}),";
	    }
	    $sql = 'INSERT INTO tbl_fe_menu(menu_id, menu_order) VALUES '. rtrim($sql, ',') .
	           ' ON DUPLICATE KEY UPDATE menu_order = VALUES(menu_order);';
	    $rs = $this->getEntityManager()->getConnection()
	    ->executeUpdate($sql);
	    $this->clearDataFromCache();
	    return $rs;
	}
	
	public function toggleStatus($entity){
	    $entity->menu_status = 1 - $entity->menu_status;
	    
	    $this->getEntityManager()->flush($entity);
	    if ( $entity->menu_link != '' && strpos($entity->menu_link, 'http') === false ){
	        $this->getEntityManager()->getConnection()
	        ->executeUpdate(
	            'UPDATE tbl_post SET p_status = :status where p_url = :url', [
	                'status' => $entity->menu_status,
	                'url'    => $entity->menu_link
	            ]
            );
	    }
	    $this->clearDataFromCache();
	    return $entity;
	}
}
?>
