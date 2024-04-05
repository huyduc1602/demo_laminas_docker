<?php
namespace Application\View\Helper;
use Laminas\View\Helper\AbstractHelper;
use Models\Entities\News;
/**
 * This view helper class displays a menu bar.
 */
class HeaderSession extends AbstractHelper 
{
    /**
     * EntityManager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_entityManager = null;
    /**
     * AuthenticationService
     * @var Laminas\Authentication\AuthenticationService
     */
    protected $_authen = null;
    /**
     * Facebook config
     * @var array
     */
    protected $_fbConfigs = null;
    /**
     * Route match
     * @var \Laminas\Router\Http\RouteMatch
     */
    protected $routerMatch = null;
    /**
     * Constructor.
     * @param array $items Menu items.
     */
    public function __construct( $entityManager, $authen = null, $fbConfigs = null, $routerMatch = null) 
    {
        $this->_entityManager   = $entityManager;
        $this->_authen          = $authen;
        $this->_fbConfigs       = $fbConfigs;
        $this->routerMatch      = $routerMatch;
    }
    
    public function __invoke( $configs = [] ) {
        $result = [];
        foreach ($configs as $key => $bool)
            if( true === $bool ) $result[$key] = $this->{$key}();
            else if (is_string($bool)) $result[$key] = $bool;
        
        return $result;
    }
    
    protected function menuTop(){
        $menuItems = $this->_entityManager->getRepository('\Models\Entities\FEMenu')
        ->getTreeDataFromCache([
            'params' => [ 
                'position' => \Models\Entities\FEMenu::POSITION_HEADER
            ]
        ], \Models\Entities\FEMenu::POSITION_HEADER);
        return $this->getView()->render('application/layout/menu-top', [
            'menuItems'=> $menuItems,
        ]);
    }
    
    protected function banner(){
        return $this->getView()->render('application/layout/banner', []);
    }
    
    protected function mainMenu(){
        return $this->getView()->render('application/layout/main-menu', [
        ]);
    }
}