<?php
namespace Models\Entities\Mapping;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PreUpdate;
use \Models\Entities\Generated;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Math\Rand;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
class Admin extends Generated\Admin {
    const KW_TYPE_NAME = 'NAME';
    const KW_TYPE_EMAIL = 'EMAIL';
    const KW_TYPE_PHONE = 'PHONE';
    const IMG_FOLDER = 'images';
    const PASSWORD_RAND_CHAR = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const PASSWORD_RAND_LENGTH = 5;
    const PASSWORD_COST = 10;
    const LENGTH_OF_KEY = 2;
    
    const GROUP_SUPPORT = 'SUPPORT';
    const GROUP_SUPPER_ADMIN = 'SUPPER_ADMIN';
    const GROUP_MANAGER = 'MANAGER';
    const GROUP_STAFF = 'STAFF';
    public static function returnGroupCodes(){
        return [
            self::GROUP_STAFF => self::GROUP_STAFF,
            self::GROUP_MANAGER => self::GROUP_MANAGER,
            self::GROUP_SUPPER_ADMIN => self::GROUP_SUPPER_ADMIN,
            self::GROUP_SUPPORT => self::GROUP_SUPPORT,
        ];
    }
    /**
     * Get code to search admin account
     * @param string $code
     * @return array
     */
    public static function returnSearchGroupCodesByCode( $code = '' ){
        if ( empty($code) ) return [];
        $groups = array_keys(self::returnGroupCodes());
        $codes = array_splice(
            $groups, 0, array_search($code, $groups) + 1
        );
        return array_combine($codes, $codes);;
    }
    public static function getPassCost(){
        return '$2y$'. sprintf('%1$02d', self::PASSWORD_COST) .'$';
    }
    
    public static function getImgPath( $id = '__id__', $img = '' ){
        return self::getUploadFolder() . $id . '/' . self::IMG_FOLDER . "/{$img}";
    }
    
    /**
     * Get user upload folder
     */
    public static function getUploadFolder(){
        return '/uploads/'.FOLDER_UPLOAD_BY_SITE."/admin";
    }
    
    /**
     * Enscrypt password
     * @param string $str
     * @return string
     */
    public static function enscyptPass( $str = '' ){
        $bcrypt = new Bcrypt([ 'cost' => self::PASSWORD_COST ]);
        $pass = strrev(
            str_replace(self::getPassCost(), '', $bcrypt->create($str))
        );
    
        $pass = Rand::getString(self::PASSWORD_RAND_LENGTH, self::PASSWORD_RAND_CHAR, true)
        . $pass .
        Rand::getString(self::PASSWORD_RAND_LENGTH, self::PASSWORD_RAND_CHAR, true)
        ;
        unset($bcrypt);
        $length = strlen($pass);
        $splitLength = rand(20, 40); $lengthKey = Rand::getString(
            self::LENGTH_OF_KEY, 
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 
            true
        );
        $pass1 = substr($pass, 0, $splitLength);
        $pass2 = substr($pass, $splitLength);
        
        $index = $length - $splitLength;
        return "{$index}{$lengthKey}" . $pass2 . $pass1;
    }
    
    /**
     * Returns password.
     * @return string
     */
    public function getAdmin_password( $decode = false )
    {
        if (true === $decode){
            // Get index
            $index = (int)$this->admin_password;
            // Remove index
            $pass = substr($this->admin_password, strlen($index) + self::LENGTH_OF_KEY );
            $pass2 = substr($pass, 0, $index);
            $pass1 = substr($pass, $index);
             
            $pass = substr(
                $pass1 . $pass2,
                self::PASSWORD_RAND_LENGTH,
                strlen($pass) - 2 * self::PASSWORD_RAND_LENGTH
            );
            return self::getPassCost() . strrev($pass);
        }
        return $this->admin_password;
    }
    
    /**
     * Sets password.
     * @param string $password
     */
    public function setAdmin_password( $password = '' )
    {
        $this->admin_password = self::enscyptPass($password);
    }
    
    /**
     * Returns the date of user creation.
     * @return string
     */
    public function getAdmin_create_time($integer = true)
    {
        if (false === $integer)
            return date(APPLICATION_DATE_TIME, $this->admin_create_time);
        return $this->admin_create_time;
    }
    
    /**
     * Returns the date of user creation.
     * @return string
     */
    public function getAdmin_last_login_time($integer = true)
    {
        if (false === $integer )
            return $this->admin_last_login_time ? 
            date(APPLICATION_DATE_TIME, $this->admin_last_login_time)
            : '' ;
        return $this->admin_last_login_time;
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
     * Explore string to array
     * @param string $string
     * @return array
     */
    public static function splitWord( $string = ''){
        $string = trim(strtolower($string));
        return str_split($string);
    }
    
    /**
     * @PreUpdate
     */
    public function preUpdate(\Doctrine\ORM\Event\PreUpdateEventArgs $event){
        // -- change the admin_fullname
        $repo = $event->getObjectManager()
            ->getRepository('\Models\Entities\Admin');
        if ( $event->hasChangedField('admin_fullname') ) {
            $repo->insertKeyWord(
                self::splitJapanWord($event->getNewValue('admin_fullname')),
                $this->admin_id,
                self::KW_TYPE_NAME
            );
        }
        // -- change the admin_email
        if ( $event->hasChangedField('admin_email') ) {
            $repo
            ->insertKeyWord(
                self::splitWord($event->getNewValue('admin_email')),
                $this->admin_id,
                self::KW_TYPE_EMAIL
            );
        }
        
        // -- change the admin_phone
        if ( $event->hasChangedField('admin_phone') ) {
            $repo
            ->insertKeyWord(
                self::splitWord($event->getNewValue('admin_phone')),
                $this->admin_id,
                self::KW_TYPE_PHONE
            );
        }
        unset($repo);
    }
    
    /**
     * @PostPersist
     */
    public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $event){
        $entityManager = $event->getEntityManager()
        ->getRepository('\Models\Entities\Admin');
        
        $entityManager->insertKeyWord(
            self::splitJapanWord( $this->admin_fullname ),
            $this->admin_id,
            self::KW_TYPE_NAME
        );
        
        $entityManager->insertKeyWord(
            self::splitWord($this->admin_email),
            $this->admin_id,
            self::KW_TYPE_EMAIL
        );
        
        // -- change the admin_phone
        if ( $this->admin_phone ) {
            $entityManager->insertKeyWord(
                self::splitWord($this->admin_phone),
                $this->admin_id,
                self::KW_TYPE_PHONE
            );
        }
        unset($entityManager);
    }
    
    public function clearSession(){
        
        if ( $this->admin_status == self::STATUS_UNACTIVE 
            && '' != $this->admin_ssid ){
            
            $path = DATA_PATH . '/session/' .APPLICATION_SITE. '/sess_' . $this->admin_ssid;
            @chmod($path, 0777); @unlink( $path );
            
            return true;
        }
        return false;
    }
    
	/**
	 * (non-PHPdoc)
	 * @see \App\Model\ZFModelEntity::init()
	 */
	public function init() {
	}
}

?>