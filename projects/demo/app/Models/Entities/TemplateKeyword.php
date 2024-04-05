<?php
namespace Models\Entities;
use \Models\Entities\Mapping;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="\Models\Repositories\TemplateKeyword")
 * @Table(name="tbl_template_keyword")
 */
class TemplateKeyword extends Mapping\TemplateKeyword {
}

?>