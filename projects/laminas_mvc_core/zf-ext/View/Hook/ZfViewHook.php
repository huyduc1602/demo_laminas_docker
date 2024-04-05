<?php
namespace Zf\Ext\View\Hook;
use Laminas\View\Helper\AbstractHelper;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;

class ZfViewHook extends AbstractHelper{
    protected static $_events;

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__,
            get_class($this)
        ]);
        self::$_events = $events;
    }

    public function getEventManager()
    {
        if (! self::$_events ) {
            $this->setEventManager(new EventManager());
        }
        return self::$_events;
    }
	
    public function __invoke() {
        return $this;
    }
    
    public function attachEvt($evtName, $evt ){
        $this->getEventManager()->attach($evtName, $evt);
    }
    
    public function triggerEvt($evtName, $params ){
        $this->getEventManager()->trigger($evtName, null, $params);
    }
}