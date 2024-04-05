<?php
namespace Models\Entities\Generated;
use \Models\Entities\Abstracted\Status;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
abstract class NewsTag extends Status {
    /**
     * @Id
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $ntg_id;
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $ntg_name;
    
    /**
     * @Column(type="string", nullable=false)
     */
    protected $ntg_name_search;
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $ntg_use_count = 0;
}
?>