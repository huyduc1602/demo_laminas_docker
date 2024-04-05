<?php
namespace Models\Entities;
use \Models\Entities\Mapping;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\NewsTag")
 * @Table(name="tbl_news_tag")
 */
class NewsTag extends Mapping\NewsTag {
}

?>