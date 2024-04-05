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
abstract class Error extends Status {
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $error_id;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $error_user_id;
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $error_uri='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $error_params='';
    /**
     * @Column(type="string", length=50, nullable=false)
     */
    protected $error_method='';
    /**
     * @Column(type="string", nullable=false)
     */
    protected $error_msg='';
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $error_code='';
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $error_status = self::STATUS_UNACTIVE;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $error_time=0;
}