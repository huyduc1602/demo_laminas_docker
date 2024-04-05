<?php
namespace Application\Controller;

use \Zf\Ext\Controller\ZfController;
use Laminas\View\Model\ViewModel;
/**
 * This is the main controller class of the User Demo application. It contains
 * site-wide actions such as Home or About.
 */
class PostController extends ZfController 
{
    /**
     * Display the post of menu header or footer 
     * Home page.
     */
    public function contentsAction(){
        $id = $this->params()->fromRoute('id', '');
        $postEle = $this->zfDoctrineManager()->getRepository('\Models\Entities\Post')
        ->findOneBy(['p_url' => $id, 'p_status' => 1]);
        
        if ( !$postEle ){
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        return new ViewModel([
            'postEle' => $postEle
        ]);
    }
}

