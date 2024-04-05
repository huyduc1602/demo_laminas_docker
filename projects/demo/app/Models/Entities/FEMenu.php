<?php
namespace Models\Entities;
use \Models\Entities\Mapping;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\FEMenu")
 * @Table(name="tbl_fe_menu")
 */
class FEMenu extends Mapping\FEMenu {
}

?>