<?php

namespace Models\Entities;
use Doctrine\ORM\Mapping\Table;
use \Models\Entities\Generated;
use Doctrine\ORM\Mapping\Entity;


/**
 * @Entity(repositoryClass="\Models\Repositories\Images")
 * @Table(name="tbl_images")
 */
class Images extends Generated\Images
{
    /**
     * Get user upload folder
     */
    public static function getUploadFolder(){
        return '/uploads/images' . (
            (APP_ENV_VERSON == 'vtest') ? '-vtest/' : '/'
        );
    }
}

?>