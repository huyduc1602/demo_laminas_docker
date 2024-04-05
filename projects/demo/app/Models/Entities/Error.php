<?php
namespace Models\Entities;
use \Models\Entities\Mapping;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\Error")
 * @Table(name="tbl_error")
 */
class Error extends Mapping\Error {
}

?>