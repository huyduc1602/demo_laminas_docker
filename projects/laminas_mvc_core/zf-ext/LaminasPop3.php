<?php
namespace Zf\Ext;
use \Laminas\Mail\Storage\Pop3  as Pop3Core;
/**
 * Customize Laminas Pop3 
 * @author Jilv006
 */
class LaminasPop3 extends Pop3Core{
    
    public function __construct($params)
    {
        $this->messageClass = 'Zf\Ext\LaminasMailMessage';
        parent::__construct($params);
    }
    
    public function resetMessageClass(){
        $this->messageClass = 'Zf\Ext\LaminasMailMessage';
    }
    
    public function getRawData($id){
        return $this->protocol->retrieve($id);
    }
}
