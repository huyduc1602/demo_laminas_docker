<?php
namespace Application\Controller;

use \Zf\Ext\Controller\ZfController;
use Laminas\View\Model\ViewModel;
/**
 * This is the main controller class of the User Demo application. It contains
 * site-wide actions such as Home or About.
 */
class CategoryController extends ZfController
{
    /**
     * Home page.
     */
    public function indexAction(){
        return new ViewModel([]);
    }

    /**
     * Big category page.
     */
    public function bigAction(){
        return new ViewModel([]);
    }

    /**
     * Middle category page.
     */
    public function middleAction(){
        return new ViewModel([]);
    }
}
