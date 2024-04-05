<?php
namespace Models\Entities\Generated;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use \Models\Entities\Abstracted\Status;

/**
 * @MappedSuperclass
 */
abstract class NewsCategory extends Status {
    /**
     * @Id
     * @Column(type="integer", length=10, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $ncate_id;
    
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $ncate_code= '';
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $ncate_name= '';
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $ncate_order = 0;
    
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $ncate_bg_color = '';
    
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $ncate_txt_color = '';
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $ncate_view_count = 0;
    
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $ncate_status = 0;
}
?>