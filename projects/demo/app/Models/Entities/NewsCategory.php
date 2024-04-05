<?php
namespace Models\Entities;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use \Models\Entities\Mapping;

/**
 * @Entity(repositoryClass="\Models\Repositories\NewsCategory")
 * @Table(name="tbl_news_category")
 */
class NewsCategory extends Mapping\NewsCategory {
    
    const LENGTH_CODE = 8;
    /**
     * Remove none alpha characters
     * @param string $str
     * @return string
     */
    public static function normalRicTxt( $str ){
        $str = self::convertEncoding($str);
        
        $patterns = [
            // Clear multi line, tab
            ['pattern' => '/(\r|\t|\n)/', 'val' => ' '],
            // Clear multi space
            ['pattern' => '/(\s+)/', 'val' => ' ']
        ];
        
        foreach ($patterns as $item){
            $str = preg_replace($item['pattern'], $item['val'], $str);
        }
        
        return strtolower($str);
    }
    
    /**
     * Support encoding
     * @var string
     */
    public static $encodings = null;
    
    /**
     * Convert encoding to UTF-8
     * @param string $str
     * @return string
     */
    public static function convertEncoding( $str = '' ){
        // -- Get support encoding
        if ( null === self::$encodings ){
            self::$encodings = implode(', ', mb_list_encodings());
        }
        
        if (!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding( $str, 'UTF-8', self::$encodings);
        }
        
        return $str;
    }
    
    /**
     * Split text to array
     * @param string $str
     * @param integer $maxLength
     * @param bool $isUnique
     * @return array
     */
    public static function splitRichTxt( $str = '', $maxLength = 300, $isUnique = false ){
        
        $finals = [];
        if( '' !== $str && $maxLength ){
            $str = self::normalRicTxt($str);
            
            // Get all none japanese keywords
            $noneJapanKeywords = preg_split('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $str);
            $finals = [];
            foreach ( $noneJapanKeywords as $no => $keyword ){
                if( $no == 0 ){
                    if ( '' !== $keyword ) {
                        $finals = explode(' ', trim($keyword));
                        $str = substr($str, strlen($keyword));
                    }
                }elseif ( '' !== $keyword ){
                    // Get japanese keyword from string
                    $firstStr = substr($str, 0, strpos($str, $keyword));
                    
                    $finals = array_merge(
                        $finals,
                        \Models\Entities\User::splitJapanWord(trim($firstStr)),
                        explode(' ', trim($keyword))
                        );
                    
                    $str = str_replace($firstStr.$keyword, '', $str);
                }
            }
            
            // Last string
            if ( '' !== $str ){
                $finals = array_merge(
                    $finals, \Models\Entities\User::splitJapanWord($str)
                    );
            }
        }
        
        if (true === $isUnique ){
            $finals = array_unique($finals);
        }
        
        return array_splice($finals, 0, $maxLength);
    }
    
    public static function convertSearchString($str){
        $keys = News::splitRichTxt(strip_tags($str));
        $keys = array_filter((array_unique($keys)));
        $search_txt = '';
        foreach ($keys AS $item){
            $item = trim($item);
            $search_txt .= " {$item}";
            if(preg_match("/[^\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u", $item)){
                do{
                    $item = substr($item, 1);
                    $search_txt .= " {$item}";
                }while ($item);
            }
        }
        return trim($search_txt);
    }
}



?>