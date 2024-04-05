<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Laminas\Mvc\Plugin\Prg',
    'Laminas\Mvc\Plugin\FlashMessenger',
    'Laminas\Mvc\Plugin\FilePrg',
    'Laminas\Session',
    'Laminas\Router',
    'Laminas\I18n',
    'Laminas\Mvc\I18n',
    'Laminas\Hydrator',
    'Laminas\Form',
    'Laminas\Filter',
    'Laminas\InputFilter',
    'Laminas\Validator',
    'Laminas\Cache',
    'Laminas\Paginator',
    'DoctrineModule',
    'DoctrineORMModule',
    'Zf\Ext\Resource',
    'Zf\Ext\Controller',
    'Zf\Ext\View\Helper',
];
