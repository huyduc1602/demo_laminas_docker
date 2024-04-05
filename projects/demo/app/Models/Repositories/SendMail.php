<?php
namespace Models\Repositories;
use Models\Entities;
class SendMail extends \Doctrine\ORM\EntityRepository {
    /**
     * @todo: Update info of SendMail
     * @param Entities\SendMail $entity
     * @param array $otps
     * @return Entities\SendMail
     */
    public function updateData( $entity, $otps = array() ) {
        // -- Set data
        $entity->fromArray($otps);
        // -- Execute update
        $this->getEntityManager()->flush($entity);
         
        return $entity;
    }
    
    /**
     * @todo: Insert info of SendMail
     * @param array $otps
     * @return SendMail
     */
    public function insertData( $otps = array() ) {
        // -- Set data
        $entity = new Entities\SendMail($otps);
        $this->getEntityManager()->persist($entity);
         
        // -- Execute insert
        $this->getEntityManager()->flush($entity);
         
        return $entity;
    }
    
    /**
     * Get news
     * @param array $options
     *
     * @return \Doctrine\ORM\QueryBuilder || Array || ...
     */
    public function getListData( $options = array() )
    {
        $entityName = $this->getEntityName();
        // Create query
        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select('SM')
        ->from($entityName, 'SM INDEX BY SM.send_mail_id')
        ;
       
        // -- order
        if ( $options['order'] )
            $qb->addOrderBy($options['order']['field'], $options['order']['mode']);
        else
            $qb->addOrderBy('SM.send_mail_total', 'DESC');
    
        // Limit
        if ( isset($options['limit']) ){
            $qb->setMaxResults( $options['limit'] );
        }
        // -- Start
        if ( isset($options['offset']) ){
            $qb->setFirstResult( $options['offset'] );
        }
        
        // -- test only
        /* \Zend_Debug::dump($qb->getParameters());
         die($qb->getQuery()->getSQL()); */
    
        // -- Result
        switch ($options['resultMode']){
            case 'Array': return $qb->getQuery()->getArrayResult(); break;
            case 'Entity': return $qb->getQuery()->getResult(); break;
            case 'Query': return $qb->getQuery(); break;
            case 'QueryBuilder':
            default: return $qb; break;
        }
    }
    
    /**
     * Get send mail
     * @param array $options
     *
     * @return \Entities\SendMail
     */
    public function getSendMail( $options = array() )
    {
        $mailAcc = (array)$this->getListData(array( 
            'resultMode' => 'Entity',
            'limit' => 1, 'offset' => 0, 
            'order' => array('field' => 'SM.send_mail_total', 'mode' => 'ASC')
        ));
        $sendMail = array_shift($mailAcc);
        
        // update order 
        $this->getEntityManager()->createQueryBuilder()
	    ->update(Entities\SendMail::class, 'SM')
	    ->set('SM.send_mail_total', 'SM.send_mail_total + 1')
	    ->andWhere('SM.send_mail_id = :send_mail_id')
	    ->setParameter('send_mail_id', $sendMail->send_mail_id)
	    ->getQuery()
	    ->execute()
	    ;
        return $sendMail;
    }
}
?>