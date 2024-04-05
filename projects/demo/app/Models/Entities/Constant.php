<?php
namespace Models\Entities;
use \Models\Entities\Generated;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
/**
 * @Entity(repositoryClass="\Models\Repositories\Constant")
 * @Table(name="tbl_constant")
 */
class Constant extends Generated\Constant {
    /**
     * Mode on
     * @var string
     */
    const STATE_ON = 'ON';
    /**
     * Mode off
     * @var string
     */
    const STATE_OFF = 'OFF';
    /**
     * Constant html: use text editor
     * @var string
     */
    const MODE_HTML = 'HTML';
    /**
     * Constant text: use text input
     * @var string
     */
    const MODE_TEXT = 'TEXT';
    const TEMPLATE_KEYS = [
        '{{fullname}}', '{{time}}', '{{money}}',
        '{{title}}', '{{lss_title}}', '{{teacher_name}}',
        '{{cost}}', '{{chat_unit}}', '{{chat_test}}',
        '{{chat_room}}', '{{link_to_payment}}',
        '{{learning_time}}', '{{price}}', '{{lesson_img}}', '{{lss_title}}',
        '{{link_to_lesson}}', '{{owner_fullname}}', '{{link_authentication}}',
        '{{__link_active__}}','{{teacher_name}}', '{{student_name}}' 
    ];
    public function getCommission($isArray = true ){
        if( false === $isArray ){
            return $this->constant_content;
        }
        return @json_decode($this->constant_content, true);
    }
    
    /**
     * Check some mode is on
     * @param array $data
     * <p> Configs data</p>
     * @param string $appName IoS, Android, Web
     * @param string $mode review, report
     * @return bool
     */
    public static function isModeOn($data , $appName, $mode ){
        return ($data && $data[$appName] && $data[$appName][$mode] == self::STATE_ON);
    }
    /**
     * Get state of report on System configs
     * @param string $os
     * @param array $opts
     * @param array $devices
     * @return string
     */
    public static function getReportState($os = '', $opts = [], $devices = []){
        $state = 'OFF';
        if ( $opts[$os] && $opts[$os]['version']
            && $devices['version'] == $opts[$os]['version'] )
            $state = $opts[$os]['report'];
        return $state;
    }

    /**
     * Set resource json
     * @param array $data
     * @return this
     */
    public function setConstant_resource( $data = [] ){
        $json = '{}';
        if( $data ){
            $json = @json_encode($data);
        }
        $this->constant_resource= $json;
        return $this;
    }

    /**
     * get resource json
     * @return array
     */
    public function getConstant_resource( ){
        if( $this->constant_resource && $this->constant_resource != '{}' ){
            return @json_decode($this->constant_resource, true);
        }
        return [];
    }
}

?>