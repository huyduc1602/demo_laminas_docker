<?php

namespace Models\Repositories;

class Images extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param integer $val
     */
    protected function _filter_id($qb, $val)
    {
        if ( is_array($val) )
            return $qb->andWhere('IM.im_id IN(:im_ids)')
            ->setParameter('im_ids', $val);

        return $qb->andWhere('IM.im_id = :im_id')
        ->setParameter('im_id', $val);
    }

    /**
     * @param Doctrine\ORM\QueryBuilder $qb
     * @param string $val
     */
    protected function _filter_keyword($qb, $val)
    {
        return $qb->andWhere('IM.im_fname LIKE :im_fname')
        ->setParameter('im_fname', "%{$val}%");
    }

    /**
     * Images listing
     * @param array $params
     * @return Array | \Doctrine\ORM\Query | \Doctrine\ORM\QueryBuilder
     *
     * @author PhapIt 2022/07/08
     */
    public function fetchOpts($opts = [], $fetchJoinKey = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('IM')
            ->from($this->getEntityName(), 'IM');

        // Filter
        if ( isset($opts['params']) && 
            is_array($opts['params'])
        ) {
            foreach ($opts['params'] as $key => $val) {
                if( false === $this->{"_filter_{$key}"}($qb, $val) ){
                    $qb
                    ->select('IM.im_id')
                    ->where('1 = :zero')
                    ->setParameters(['zero' => 0]);
                    break;
                }
            }
        }

        // Order
        if ( isset($opts['order']) && 
            is_array($opts['order'])
        ) {
            foreach ($opts['order'] as $col => $mode)
                $qb->addOrderBy("IM.im_{$col}", $mode);
        } else
            $qb->addOrderBy('IM.im_id', 'DESC');

        if (false === $fetchJoinKey) {
            $qb->setParameter(\Zf\Ext\Model\ZFDtPaginator::FECTH_JOIN_COLLECTION, false);
        }

        // dd($qb->getParameters(), $qb->getQuery()->getSQL());
        
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
    }
    
    /**
     * Add new Images
     * @param array $data
     *
     * @return \Entities\Images
     * @author PhapIt 2022/07/08
     *
     */
    public function insertData($data = [])
    {
        $entity = new \Models\Entities\Images($data);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);
        
        //	Return
        return $entity;
    }

    /**
     * Edit Images
     *
     * @param \Entities\Images $entity
     * @param array $updateData
     *
     * @return \Entities\Images
     *
     * @author PhapIt 2022/07/08tthai dev
     */
    public function updateData(\Models\Entities\Images $entity, $updateData)
    {
        $entity->fromArray($updateData);
        $this->getEntityManager()->flush($entity);
        
        //	Return
        return $entity;
    }

    /**
     * Delete by IDs
     * @param array $ids
     */
    public function delByIds($ids){
        $adapter = $this->getEntityManager()->getConnection();
        $tblName = $this->getClassMetadata()->getTableName();

        $images = $adapter->fetchAllAssociative(
            "SELECT im_thumb_path AS `thumb_path` FROM {$tblName} WHERE im_id in (:ids)",
            ['ids' => $ids],
            ['ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );

        $adapter->executeStatement(
            "DELETE FROM {$tblName} WHERE im_id IN(:ids)",
            ['ids' => $ids],
            ['ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );
        
        return $images;
    }
}

?>