<?php
namespace Models\Entities;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use \Models\Entities\Mapping;

/**
 * @Entity(repositoryClass="\Models\Repositories\News")
 * @Table(name="tbl_news")
 */
class News extends Mapping\News {
    
    /**
     * Them vao phia sau va khong duoc thay doi vi tri cua cac key
     * @var array
     */
    const LIFE_PICKUP = 'LIFE_PICKUP';
    const LIFE_TOP_IMG = 'LIFE_TOP_IMG';
    const ASKBE_TOP_IMG = 'ASKBE_TOP_IMG';
  
    const BASE_IMG_FOLDER = '/uploads/news';
    const LENGTH_CODE = 11;
    const FT_CHAR_PLUS = "__";

    /**
     * Remove none alpha characters
     * @param string $str
     * @return string
     */
    public static function normalRicTxt( $str ){
        $str = self::convertEncoding($str);
        
        $patterns = [
//             // Clear all special characters
//             ['pattern' => '/([^\p{L}\s\d])/u', 'val' => ' '],
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
     * Explore string to array
     * @param string $string
     * @param number $length
     * @return array
     */
    public static function splitJapanWord( $string = '', $length = 150 ){
        /* $pattern = "/[。、？！＜＞： 「」（）｛｝≪≫〈〉《》【】 『』〔〕［］・\n\r\t\s\(\)　]/u";
         preg_replace($pattern, "", $string) */
        $strArr = preg_split("/(?<!^)(?!$)/u", $string, $length + 1, PREG_SPLIT_NO_EMPTY);
        return array_splice($strArr, 0, $length);
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
                        self::splitJapanWord(trim($firstStr)),
                        explode(' ', trim($keyword))
                        );
                    
                    $str = str_replace($firstStr.$keyword, '', $str);
                }
            }
            // Last string
            if ( '' !== $str ){
                $finals = array_merge(
                    $finals, self::splitJapanWord($str)
                );
            }
        }
        
        if (true === $isUnique ){
            $finals = array_unique($finals);
        }
        return array_splice($finals, 0, $maxLength);
    }
    
    public static function convertSearchString($str, $stopword = [], $isUnique = false){
        $keys = News::splitRichTxt($str);
        $keys = $isUnique ? array_filter((array_unique($keys))) : array_filter($keys);
        $search_txt = '';
        $ft_char_plus = self::FT_CHAR_PLUS;
        foreach ($keys AS $item){
            $item = trim($item);
            if(preg_match("/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u", $item)){
                $search_txt .= " {$item}{$ft_char_plus}";
            }else{
                $search_txt .= (mb_strlen($item) < 3 || in_array($item, $stopword)) ? " {$item}{$ft_char_plus}" : " {$item}";
                $item = mb_substr($item, 1);
                while ($item){
                    //$search_txt .= in_array($item, $stopword) ? " {$item}{$ft_char_plus}" : " {$item}";
                    $search_txt .= (mb_strlen($item) < 3 || in_array($item, $stopword)) ? " {$item}{$ft_char_plus}" : " {$item}";
                    $item = mb_substr($item, 1);
                };
            }
        }
        return trim($search_txt);
    }
    
    public static function convertSearchKeyword($str, $stopword = []){
        $keys = News::splitRichTxt($str);
        $keys = array_filter((array_unique($keys)));
        $search_txt = '';
        $ft_char_plus = self::FT_CHAR_PLUS;
        foreach ($keys AS $item){
            $item = trim($item);
            $search_txt .= " {$item}*";
        }
        return trim($search_txt);
    }
    
    public static function getOrginalImg($imgName, $serverurl = ''){
        $rs = News::BASE_IMG_FOLDER . '/' . $imgName;
        if($serverurl){
            $rs = $serverurl . $rs;
        }
        return $rs;
    }
    
    /**
     * Get max item of array
     * @param array $items
     * @return number|mixed
     */
    public static function getImgPath( $imgName = '' ){
        if ( $imgName){
            return implode(DIRECTORY_SEPARATOR, [
                PUBLIC_PATH,
                ltrim(News::BASE_IMG_FOLDER, DIRECTORY_SEPARATOR),
                $imgName
            ]);
        }
        return '';
    }
    /**
     * Remove spacial character of link
     * @param string $link
     * @return string
     */
    public static function normalLink( $link = '' ){
        return str_replace('/', '-', trim($link, ' /'));
    }
}



?>