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
abstract class Post extends Status {
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $p_id;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $p_cate_id;
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $p_url='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $p_title='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $p_keyword='';
    /**
     * @Column(type="string", length=500, nullable=false)
     */
    protected $p_description='';
    /**
     * @Column(type="string", length=500, nullable=false)
     */
    protected $p_note='';
    /**
     * @Column(type="string", nullable=false)
     */
    protected $p_content='';
    /**
     * @Column(type="string", nullable=false)
     */
    protected $p_resource='{}';
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $p_admin_id;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $p_time=0;
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $p_status=self::STATUS_UNACTIVE;
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $p_type='NEWS';
}