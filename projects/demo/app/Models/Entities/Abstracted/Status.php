<?php
namespace Models\Entities\Abstracted;
use \Zf\Ext\Model\ZFModelEntity;

abstract class Status extends ZFModelEntity {
	/**
	 * State unactive
	 * @var string
	 */
	const STATUS_UNACTIVE = 0;
	
	/**
	 * State active
	 * @var string
	 */
	const STATUS_ACTIVE = 1;
	
	/**
	 * List of state
	 * @return array
	 */
	public static function returnStatus() {
		return array(
			self::STATUS_ACTIVE		=> 'Active',
			self::STATUS_UNACTIVE	=> 'Unactive'	
		);
	}
}

?>