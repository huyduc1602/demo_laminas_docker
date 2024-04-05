<?php
namespace Models\Entities\Generated;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
abstract class Keyword extends \Zf\Ext\Model\ZFModelEntity {
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $kw_id;
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $kw_str='';
}