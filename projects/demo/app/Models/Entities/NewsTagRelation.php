<?php
namespace Models\Entities;
use \Models\Entities\Mapping;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\NewsTagRelation")
 * @Table(name="tbl_news_tag_relation")
 */
class NewsTagRelation extends Mapping\NewsTagRelation {
}

?>