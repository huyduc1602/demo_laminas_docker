<?php
namespace Zf\Ext\View\Helper;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Zf\Ext\View\Helper;
class Module
{
    /**
     * Retrieve default zend-db configuration for zend-mvc context.
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'view_helpers' => [
                'factories' => [
                    Helper\Url::class => Helper\HelperFactory::class,
                    Helper\Authen::class => Helper\HelperFactory::class,
                    Helper\GetRouteMatch::class => Helper\HelperFactory::class,
                    \Zf\Ext\View\Hook\ZfViewHook::class => InvokableFactory::class,
                    Helper\CommonHelper::class => InvokableFactory::class,
                    Helper\CsrfToken::class => Helper\HelperFactory::class,
                    
                    Helper\LazyAsset::class => InvokableFactory::class,
                    Helper\FormElementErrors::class => InvokableFactory::class,
                    Helper\NumberFormatToString::class => InvokableFactory::class,
                    Helper\NumberFormat::class => InvokableFactory::class,
                    
                    Helper\MinifyHeadLink::class => InvokableFactory::class,
                    Helper\MinifyHeadScript::class => InvokableFactory::class,
                    Helper\GlobalScript::class => InvokableFactory::class,
                    
                    Helper\HeadScriptAction::class => Helper\HelperFactory::class,
                    Helper\GlobalScriptAction::class => Helper\HelperFactory::class,
                    Helper\HeadStyleAction::class => Helper\HelperFactory::class,
                    
                    BootstrapToolbar::class => InvokableFactory::class,
                    BootstrapToolbar\ToolbarIcon::class => InvokableFactory::class,
                    BootstrapToolbar\ToolbarDropDown::class => InvokableFactory::class,
                    BootstrapToolbar\ToolbarChangeOrder::class => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarClearCache::class => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarBack::class => InvokableFactory::class,
                    BootstrapToolbar\ToolbarClose::class => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarDelete::class => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarInsert::class => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarEdit::class => ToolbarFactory::class,
                    BootstrapToolbar\ToolbarSave::class => InvokableFactory::class,
                    BootstrapToolbar\ToolbarSaveAndClose::class => InvokableFactory::class,
                    BootstrapToolbar\ToolbarSaveAndNew::class => InvokableFactory::class,
                    
                    BootstrapManage\ManageIcon::class => InvokableFactory::class,
                    BootstrapManage\ManageChangeStatus::class => InvokableFactory::class,
                    BootstrapManage\ManageCheckbox::class => InvokableFactory::class,
                    BootstrapManage\ManageCheckboxAll::class => InvokableFactory::class,
                    BootstrapManage\ManageDelete::class => InvokableFactory::class,
                    BootstrapManage\ManageDetail::class => InvokableFactory::class,
                    BootstrapManage\ManageIcon::class => InvokableFactory::class,
                    BootstrapManage\ManageUpdate::class => InvokableFactory::class,
                ],
                'aliases' => [
                    'zfUrl' => Helper\Url::class,
                    'zfAuthen'     => Helper\Authen::class,
                    'zfRouteMatch' => Helper\GetRouteMatch::class,
                    'viewHook' => \Zf\Ext\View\Hook\ZfViewHook::class,
                    'zfCommonHelper'  =>   Helper\CommonHelper::class,
                    Helper\CsrfToken::SERVICE_ALIAS => Helper\CsrfToken::class,
                    
                    'lazyAsset' => Helper\LazyAsset::class,
                    'zfFormElementErrors'=> Helper\FormElementErrors::class,
                    'zfNumberFormatString' => Helper\NumberFormatToString::class,
                     Helper\NumberFormat::SERVICE_ALIAS => Helper\NumberFormat::class,
                    
                    'minifyHeadLink'    => Helper\MinifyHeadLink::class,
                    'minifyHeadScript'  => Helper\MinifyHeadScript::class,
                    'globalScript'      => Helper\GlobalScript::class,
                    
                    'headStyleAction'   => Helper\HeadStyleAction::class,
                    'headScriptAction'  => Helper\HeadScriptAction::class,
                    'globalScriptAction'=> Helper\GlobalScriptAction::class,
                    
                    'bootstrapToolbar'  => BootstrapToolbar::class,
                    'toolbarIcon'       => BootstrapToolbar\ToolbarIcon::class,
                    'toolbarDropDown'   => BootstrapToolbar\ToolbarDropDown::class,
                    'toolbarChangeOrder'=> BootstrapToolbar\ToolbarChangeOrder::class,
                    'toolbarClearCache' => BootstrapToolbar\ToolbarClearCache::class,
                    'toolbarBack'       => BootstrapToolbar\ToolbarBack::class,
                    'toolbarClose'      => BootstrapToolbar\ToolbarClose::class,
                    'toolbarDelete'     => BootstrapToolbar\ToolbarDelete::class,
                    'toolbarInsert'     => BootstrapToolbar\ToolbarInsert::class,
                    'toolbarEdit'     => BootstrapToolbar\ToolbarEdit::class,
                    'toolbarSave'       => BootstrapToolbar\ToolbarSave::class,
                    'toolbarSaveAndClose'=> BootstrapToolbar\ToolbarSaveAndClose::class,
                    'toolbarSaveAndNew' => BootstrapToolbar\ToolbarSaveAndNew::class,
                    
                    'manageIcon'        => BootstrapManage\ManageIcon::class,
                    'manageChangeStatus'=> BootstrapManage\ManageChangeStatus::class,
                    'manageCheckbox'    => BootstrapManage\ManageCheckbox::class,
                    'manageCheckboxAll' => BootstrapManage\ManageCheckboxAll::class,
                    'manageDelete'      => BootstrapManage\ManageDelete::class,
                    'manageDetail'      => BootstrapManage\ManageDetail::class,
                    'manageIcon'        => BootstrapManage\ManageIcon::class,
                    'manageUpdate'      => BootstrapManage\ManageUpdate::class
                ]
            ]
        ];
    }
}
