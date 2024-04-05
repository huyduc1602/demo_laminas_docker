<?php
namespace Models\Entities;
use Models\Entities\Generated;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="Models\Repositories\SendMail")
 * @Table(name="tbl_send_mail")
 */
class SendMail extends Generated\SendMail {
}

?>