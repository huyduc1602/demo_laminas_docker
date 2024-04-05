<?php
namespace Models\Entities\Mapping;
use \Models\Entities\Generated;
use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 * @MappedSuperclass
 */
class Error extends Generated\Error {
    const METHOD_POST  = 'POST';
    const METHOD_GET   = 'GET';
    const METHOD_PUT   = 'PUT';
    const METHOD_DELETE= 'DELETE';
    
    public static function returnMethods( ){
        return [
            self::METHOD_GET    => self::METHOD_GET,
            self::METHOD_POST   => self::METHOD_POST,
            self::METHOD_PUT    => self::METHOD_PUT,
            self::METHOD_DELETE => self::METHOD_DELETE
        ];
    }
    
	/**
	 * (non-PHPdoc)
	 * @see \App\Model\ZFModelEntity::init()
	 */
	public function init() {
	}
}

?>