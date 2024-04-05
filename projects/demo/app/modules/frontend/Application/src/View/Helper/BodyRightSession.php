<?php
namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;


/**
 * This view helper class displays a menu bar.
 */
class BodyRightSession extends AbstractHelper 
{
    protected $_entityManager = null;
    protected $_url = null;
    
    /**
     * Constructor.
     * @param array $items Menu items.
     */
    public function __construct( $url, $entityManager = null ) 
    {
        $this->_entityManager = $entityManager;
        $this->_url = $url;
        return $this;
    }
    
    /**
     * 
     * @param string || array $temple
     * ['tmpl': string, template name, 'data': mixed ]
     * @return string
     */
    public function __invoke( $temple = false ) {
        $data = []; if ( is_array($temple) ) {
            $data   = $temple['data'];
            $temple = $temple['tmpl'];
        }
        if ( is_string($temple) )
            return $this->getView()->render($temple, $data);
        else {
            return $this->getView()->render('menu/right', [
                'menuItems'=> $this->getMenuItems($data),
                'data' => $data,
                'cstData'  => [
                ]
            ]);
        }
    }
    
    /**
     * Get menu item
     */
    public function getMenuItems($data)
    {
        /* $repoNewsTag = $this->_entityManager->getRepository(\Models\Entities\NewsTag::class);
        $repoNews = $this->_entityManager->getRepository(\Models\Entities\News::class);
        $news_most_view_params = [status => 1, limit => 5];
        if(empty($data['ncate_id']) == false){
            $news_most_view_params['ncate_id'] = $data['ncate_id'];
        }
        if(empty($data['not_id']) == false){
            $news_most_view_params['not_id'] = $data['not_id'];
        }
        $news_most_view = $repoNews->fetchOpts([
            'resultMode' => 'Array',
            'params' => $news_most_view_params,
            'order' => ['view_count' => 'desc']
        ]); */
        
        $repoNewsCate = $this->_entityManager->getRepository(\Models\Entities\NewsCategory::class);
        $cates_most_view = $repoNewsCate->fetchOpts([
            'resultMode' => 'Array',
            'params' => ['status' => 1, 'limit' => 5],
            'order' => ['order' => 'asc']
        ]);
        return [
            'cates_most_view' => $cates_most_view, 
            //'news_most_view' => $news_most_view,
        ];
    }
}