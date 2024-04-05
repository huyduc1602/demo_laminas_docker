<?php
namespace Application\Controller;

use Doctrine\ORM\Exception\NotSupported;
use \Zf\Ext\Controller\ZfController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Models\Entities\News;
use Models\Entities\NewsCategory;
/**
 * This is the main controller class of the User Demo application. It contains
 * site-wide actions such as Home or About.
 */
class IndexController extends ZfController
{

    /**
     * @param int|null $id
     *
     * @return ViewModel
     * @throws NotSupported
     */
    public function demoAction(?int $id = 0): ViewModel
    {
        $repoNews = $this->zfDoctrineManager()->getRepository(
            \Models\Entities\News::class
        );

        $blogs = $repoNews->fetchOpts([
            'resultMode' => 'Entity',
            'params'     => ['status' => 1, 'limit' => 10],
            'order'      => ['created_at' => 'desc']
        ]);
        $view = new ViewModel([
            'blogs' => $blogs,
            'type' => $type = $this->params()->fromRoute('type')
        ]);
        $view->setTemplate("application/index/{$type}");
        return $view;
    }

    /**
     * Home page.
     */
    public function indexAction(){
        $repoNews = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\News::class);
        $repoNewsCate = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\NewsCategory::class);

        $lastest_blogs = $repoNews->fetchOpts([
            'resultMode' => 'Entity',
            'params' => ['status' => 1, 'limit' => 5], 'order' => ['created_at' => 'desc']]
        );
        $cates = $repoNewsCate->getDataFromCache([
            'params' => [ 'status' => 1],
        ]);

        return new ViewModel([
            'lastest_blogs' => $lastest_blogs,
            'cates' => $cates,
            'constant' => $this->getEntityManager()
            ->getRepository('\Models\Entities\Constant')
            ->findOneBy(['constant_code' => 'tml_toppage'])
        ]);
    }

    /**
     * Search page.
     */
    public function blogsAction(){
        $limit = $this->params()->fromQuery('limit', 5);
        $page = $this->params()->fromQuery('page', 1);

        $repoNews = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\News::class);
        $repoNewsTag = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\NewsTag::class);
        $repoNewsCate = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\NewsCategory::class);

        $cates = $repoNewsCate->getDataFromCache([
            'params' => ['status' => 1],
        ]);

        $params = ['status' => 1];
        $params += $this->getFilterParams();
        $list = $repoNews->fetchOpts(['params' => $params, 'order' => ['created_at' => 'desc']]);
        $paginator = $this->getPaginator($list->getQuery());
        $paginator = $paginator
        ->setItemCountPerPage($limit)
        ->setCurrentPageNumber($page);
        $news_tags_most_view = $repoNewsTag->getDataFromCache([
            'params' => ['limit' => 20, 'not_name' => ($params['search_ft'] ?? ''), 'from_use_count' => 1],
            'order' => ['use_count' => 'desc']
        ]);
        return new ViewModel([
            'paginator' => $paginator,
            'list' => $paginator->getCurrentItems(),
            'news_tags_most_view' => $news_tags_most_view,
            'cates' => $cates
        ]);
    }

    const NEWS_NOT_FOUND = 'NEWS_NOT_FOUND';

    protected function getFilterParams(){
        $f = trim($this->params()->fromQuery('f', ''));
        $cate = intval($this->params()->fromQuery('cate', 0));
        $rs = [];
        if(empty($f) == false){
            $rs['search_ft'] = $f;
        }
        if(empty($cate) == false){
            $rs['ncate_id'] = $cate;
        }
        return $rs;
    }

    /**
     * Article detail page.
     */
    public function blogDetailAction(){
        $code = $this->params()->fromRoute('id');
        $repoNews = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\News::class);
        $item = $repoNews->findOneBy(['news_code' => $code, 'news_status' => 1]);
        if(empty($item)){
            if( $this->getRequest()->isPost() ){
                return new JsonModel([
                    'success'=> false,
                    'code' => self::NEWS_NOT_FOUND,
                    'msg' => 'News not found'
                ]);
            }
            $this->zfFlashMsg()->addWarningMessage(
                $this->mvcTranslate('News not found')
            );
            return $this->zfRedirect()->toRoute('home');
        }
        $repoNewsCate = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\NewsCategory::class);
        $cates = $repoNewsCate->getDataFromCache([
            'params' => [ 'status' => 1],
        ]);

        $repoNewsTagRel = $this->zfDoctrineManager()
        ->getRepository(\Models\Entities\NewsTagRelation::class);
        $tag_ids = $repoNewsTagRel->fetchOpts([
            'resultMode' => 'Array',
            'params' => ['news_id' => $item->news_id]
        ]);
        $tag_ids = array_map(function($item){ return $item['ntr_news_tag_id'];}, $tag_ids);

        $relation_articles = $repoNews->getRelatedNews([
            'tag_ids' => $tag_ids,
            'obj' => $item
        ]);
        if(empty($relation_articles)){
            $cateIds = [];
            $cate = $cates[$item->news_ncate_id];
            $big_cate = $cate['ncate_big_cate'];
            if(empty($big_cate) == false){
                foreach ($cates as $itemCate){
                    if($big_cate == $itemCate['ncate_big_cate']){
                        $cateIds[] = $itemCate['ncate_id'];
                    }
                }
                if(empty($cateIds) == false){
                    $relation_articles = $repoNews->getRandomByCateIds($cateIds);
                }
            }
        }

        $repoNews->plusViewById($item->news_id, $item->news_ncate_id);
        return new ViewModel([
            'article' => $item,
            'cates' => $cates,
            'cate' => $cates[$item->news_ncate_id],
            'relation_articles' => $relation_articles
        ]);
    }

    /**
     * This function for admin check post is exists
     * @return \Laminas\View\Model\JsonModel
     */
    public function checkUrlAction(){
        $post = $this->params()->fromPost();
        $request = $this->getRequest()->setUri($post['url']);
        $match = $this->getEvent()->getRouter()->match($request);
        if($match){
            return new JsonModel([
                'success'=> false,
                'code' => 'URL_INVALID',
                'msg' => $this->mvcTranslate('Url invalid'),
            ]);
        }
        return new JsonModel([
            'success'=> true,
            'code' => 'URL_VALID',
            'msg' => $this->mvcTranslate('Url valid'),
        ]);
    }
}