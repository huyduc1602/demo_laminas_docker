<?php
namespace Zf\Ext;
use \Laminas\Mail\Storage\Imap  as ImapCore;
/**
 * Customize Laminas Imap
 * @author Jilv006
 */
class LaminasImap extends ImapCore{
    public function __construct($params)
    {
        $this->messageClass = 'Zf\Ext\LaminasMailMessage';
        parent::__construct($params);
    }
    
    public function resetMessageClass(){
        $this->messageClass = 'Zf\Ext\LaminasMailMessage';
    }
    
    public function getRawData($id){
        return $this->protocol->fetch('RFC822', $id);
    }

    public function searchIndex($fromTime, $toTime = null, $type = 'single')
    {
        $fromTime = is_numeric($fromTime) ? date('j-M-Y', $fromTime) : $fromTime;
        $toTime = is_numeric($toTime) ? date('j-M-Y', $toTime) : $toTime;

        return match ($type) {
            'single' => $this->protocol->search([vsprintf('SINCE "%s"', [$fromTime])]),
            'range'  => $this->protocol->search([vsprintf('SINCE "%s" BEFORE "%s"', [$fromTime, $toTime])]),
            default => $this->protocol->search([vsprintf('SINCE "%s"', [$fromTime])])
        };
    }
}
