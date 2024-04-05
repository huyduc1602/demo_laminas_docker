<?php
namespace Models\Entities\Generated;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use \Models\Entities\Abstracted\Status;

/**
 * @MappedSuperclass
 */
abstract class News extends Status {
    /**
     * @Id
     * @Column(type="integer", length=10, nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $news_id;
    
    /**
     * @Column(type="string", length=20, nullable=false)
     */
    protected $news_code = '';
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_ncate_id;
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_title = '';
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_title_path_url = '';
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_tags = '';
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_img = '';
    
    /**
     * @Column(type="smallint", length=1, nullable=false)
     */
    protected $news_status = 0;
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_seo_meta_discription = '';
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_seo_meta_keyword = '';
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $news_field_key = '';
    
    /**
     * @Column(type="string", nullable=false)
     */
    protected $news_content = '';
    
    /**
     * @Column(type="string", nullable=false)
     */
    protected $news_title_search = '';
    
    /**
     * @Column(type="string", nullable=false)
     */
    protected $news_tags_search = '';
    
    /**
     * @Column(type="string", nullable=false)
     */
    protected $news_search = '';
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_view_count = 0;
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_week_view_count = 0;
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_created_by;
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_updated_by;
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_created_at = 0;
    
    /**
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $news_updated_at = 0;
    
}
?>