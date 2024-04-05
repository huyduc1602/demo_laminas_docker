<?php

namespace Models\Entities\Generated;

use \Models\Entities\Abstracted\Status;
use \Doctrine\ORM\Mapping\MappedSuperclass;
use \Doctrine\ORM\Mapping\Id;
use \Doctrine\ORM\Mapping\Column;
use \Doctrine\ORM\Mapping\GeneratedValue;
/**
 * @MappedSuperclass
 */
abstract class Images extends Status
{
    /**
     * @Id
     * @Column(type="integer", length=11, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $im_id;
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $im_folder='';
    /**
     * @Column(type="string", length=100, nullable=false)
     */
    protected $im_thumb='';
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $im_thumb_path='';
    /**
     * @Column(type="integer", length=12, nullable=false)
     */
    protected $im_created = 0;
}