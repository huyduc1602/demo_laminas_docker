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
abstract class Admin extends Status {
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $admin_id;
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $admin_code='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $admin_bg_timeline='';
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $admin_username='';
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $admin_password='';
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $admin_status='';
    /**
     * @Column(type="string", length=10, nullable=false)
     */
    protected $admin_groupcode;
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $admin_fullname='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $admin_kataname='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $admin_avatar='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $admin_address='';
    /**
     * @Column(type="string", length=150, nullable=false)
     */
    protected $admin_email='';
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $admin_phone='';
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $admin_create_time=0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $admin_last_login_time=0;
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $admin_ssid='';
}