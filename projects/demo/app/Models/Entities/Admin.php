<?php
namespace Models\Entities;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use \Models\Entities\Mapping;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\Admin")
 * @Table(name="tbl_admin")
 * @HasLifecycleCallbacks
 */
class Admin extends Mapping\Admin {
}

?>