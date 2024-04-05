<?php
namespace Models\Entities\Generated;
use \Models\Entities\Abstracted\Status;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
abstract class NewsTagRelation extends Status {
    /**
     * @Id
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $ntr_news_id;
    
    /**
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $ntr_news_tag_id;
}
?>