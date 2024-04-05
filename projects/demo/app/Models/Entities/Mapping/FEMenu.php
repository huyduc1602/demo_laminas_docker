<?php
namespace Models\Entities\Mapping;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use \Models\Entities\Generated;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
class FEMenu extends Generated\FEMenu {
    const TYPE_LINK  = 'LINK';
    const TYPE_TEXT  = 'TEXT';
    const TYPE_IMAGE= 'IMAGE';
    const TYPE_IMAGE_LINK= 'IMAGE_LINK';
    const TYPE_REFER_LINK= 'REFER_LINK';
    const TYPE_INPUT = 'INPUT';
    const MAX_DISPLAY_ROOT_ITEM = 10;
    const MAX_DISPLAY_COLUMN = 8;
    const MAX_DISPLAY_FOOTER_COLUMN = 6;
    const POSITION_HEADER = 'HEADER';
    const POSITION_FOOTER = 'FOOTER'; 
    const DOMAIN_ASKBE = 'ASKBE';
    const DOMAIN_ASKBE_LIFE = 'ASKBE_LIFE'; 
    
    
    public static function getTypes ($position = self::POSITION_HEADER){
        $types = [
            self::TYPE_TEXT  => 'Text',
            self::TYPE_LINK  => 'Link',
            self::TYPE_INPUT => 'Input',
            self::TYPE_REFER_LINK=> 'Only Link',
        ];
        if($position == self::POSITION_FOOTER){
            $types = [
                self::TYPE_TEXT  => 'Text',
                self::TYPE_LINK  => 'Link',
                self::TYPE_IMAGE=> 'Image',
                self::TYPE_IMAGE_LINK=> 'Image Link',
                self::TYPE_REFER_LINK=> 'Only Link',
            ];
        }
        return $types;
    }
    /**
     * Folder upload icon for menu item
     * @var string
     */
    const BASE_IMG_FOLDER = '/uploads/menu_icons';
    
    /**
     * @ManyToOne(targetEntity="\Models\Entities\FEMenu")
     * @JoinColumn(name="menu_parent_id", referencedColumnName="menu_id")
     */
    protected $parent;
    
    public function setParent( $parent ){
        $this->parent = $parent;
        $this->menu_parent_id = $parent->menu_id;
        return $this;
    }
    
     /**
     * @OneToMany(targetEntity="\Models\Entities\FEMenu", mappedBy="parent")
     * @OrderBy({"menu_order" = "ASC", "menu_title" = "ASC"})
     */
    protected $childs;
    
    public function addChilds(\Models\Entities\FEMenu $child){
        $this->childs->add($child);
        return $this;
    }
    /**
     * Get menu icon
     * @param string $icon
     * @return string
     */
    public static function getIcon( $icon = '' ){
        if( $icon )
            return self::BASE_IMG_FOLDER . "/{$icon}";
        return '';
    }
    public function setMenu_link( $link = '' ){
        if ( strlen($link) > 0 ){
            $this->menu_link = strpos($link, 'http') === 0 ? $link: strtolower($link);
        }
        else $this->menu_link = null;
        return $this;
    }
    
    /**
     * Get full image path
     * @return string
     */
    public function getMenuIcon( ){
        return self::getIcon($this->menu_icon);
    }
    
    public function setMenu_style( $opts = [] ){
        $json = '{}';
        if ( $opts ) $json = json_encode($opts);
        $this->menu_style = $json;
        return $this;
    }
    
    public static function decodeMenuStyle( $json = '', $isJson = true ) {
        if ( true === $isJson ) return $json;
        
        $rs = [];
        if( $json && $json != '{}' )
            $rs = array_filter(json_decode($json, true));
        return $rs;
    }
    
    /**
     * Get decode of style
     * @return array
     */
    public function getMenuStyle( $isJson = true ){
        return self::decodeMenuStyle($this->menu_style, $isJson);
    }
    
    const ARR_DATA_KEY = [
        'code', 'title', 'icon',
        'link', 'style', 'order',
        'status', 'level', 'is_login', 'type'
    ];
    
    public function toArrayData( $scope = self::ARR_DATA_KEY ){
        $json = [];
        foreach ($scope as $key){
            $json[$key] = $this->__get("menu_".$key);
        }
        
        return $json;
    }
    
	/**
	 * (non-PHPdoc)
	 * @see \App\Model\ZFModelEntity::init()
	 */
	public function init() {
	}
}

?>