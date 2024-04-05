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
abstract class Constant extends Status {
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $constant_id;
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $constant_code='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $constant_sender='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $constant_receiver='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $constant_title='';
    /**
     * @Column(type="string", nullable=false)
     */
    protected $constant_content='';
    /**
     * @Column(type="string", nullable=false)
     */
    protected $constant_note='';
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $constant_creation_time=0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $constant_last_update_time=0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $constant_last_update_admin_id;
    /**
     * @Column(type="string", length=15, nullable=false)
     */
    protected $constant_type;
    /**
     * @Column(type="string", length=15, nullable=false)
     */
    protected $constant_mode;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $constant_resource='';
}