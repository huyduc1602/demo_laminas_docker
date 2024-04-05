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
abstract class FEMenu extends Status {
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $menu_id;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_parent_id;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_admin_id;
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $menu_code = '';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $menu_title = '';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $menu_link = '';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $menu_icon = '';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $menu_style = '{}';
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $menu_type = 'TEXT';
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_order = 0;
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $menu_status = self::STATUS_UNACTIVE;
    /**
     * @Column(type="smallint", length=3, nullable=false)
     */
    protected $menu_level = 0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_time = 0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_child_count = 0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_edit_time = 0;
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $menu_edit_admin_id;
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $menu_is_login = 'ALL';
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $menu_has_post = 0;
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $menu_position= 0;
    /**
     * @Column(type="string", nullable=false)
     */
    protected $menu_domain= 0;
}