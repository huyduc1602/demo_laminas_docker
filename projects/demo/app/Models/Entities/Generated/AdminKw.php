<?php
namespace Models\Entities\Generated;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
abstract class AdminKw extends \Zf\Ext\Model\ZFModelEntity {
    /**
	 * @Id
	 * @Column(type="integer", length=11, nullable=false)
	 */
    protected $ak_admin_id;
    /**
	 * @Id
	 * @Column(type="integer", length=11, nullable=false)
	 */
    protected $ak_kw_id;
    /**
     * @Column(type="string", length=10, nullable=false)
     */
    protected $ak_type='NAME';
}