<?php
namespace Models\Entities;
use \Models\Entities\Generated;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\Post")
 * @Table(name="tbl_post")
 */
class Post extends Generated\Post{
    /**
     * Base url of post
     * @var string
     */
    const POST_BASE_URL = '/main/contents';
    
    /**
     * Set cate json
     * @param array $data
     * @return this
     */
    public function setP_resource( $data = [] ){
        $json = '{}';
        if( $data ){
            $json = @json_encode($data);
        }
        $this->p_resource= $json;
        return $this;
    }
    
    /**
     * get cate json
     * @return array
     */
    public function getP_resource( ){
        if( $this->p_resource && $this->p_resource != '{}' ){
            return @json_decode($this->p_resource, true);
        }
        return [];
    }
}

?>