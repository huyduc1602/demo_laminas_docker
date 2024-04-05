<?php
namespace Models\Entities\Generated;
use \Models\Entities\Abstracted\Status;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
abstract class TemplateKeyword extends Status {
    /**
     * @Id
     * @Column(type="integer", length=10, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $tkw_id;
    
    /**
     * @Column(type="string", length=50, nullable=false)
     */
    protected $tkw_type = 'COLUMN';
    
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $tkw_key_name = '';
    
    /**
     * @Column(type="string", nullable=false)
     */
    protected $tkw_key_note = '';
    
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $tkw_obj_var = '';
    /**
     * @Column(type="string", nullable=false)
     */
    protected $tkw_obj_params = '';
    
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $tkw_obj_name = '';
    
}
?>