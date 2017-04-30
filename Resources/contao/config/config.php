<?php
/******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

$assetsPath     = 'bundles/iidobasic/';

//if( file_exists(TL_ROOT . '/web/' . $assetsPath . 'css/backend/backend.css') && TL_MODE == "BE" )
//{
//    $GLOBALS['TL_CSS'][] = $assetsPath . 'css/backend/backend.css';
//}

list( $namespace, $subNamespace, $subName, $prefix, $tablePrefix, $listenerName ) = \IIDO\BasicBundle\Config\BundleConfig::getBundleConfigArray();



/**
 * Backend modules
 */

//array_insert($GLOBALS['BE_MOD'], 3, array
//(
//
//    $prefix => array
//   (
//        $prefix . 'Placeholder' => array
//        (
//            'callback'      => $namespace . '\\' . $subNamespace . '\Backend\Module\Placeholder',
//            'tables'        => array($tablePrefix . 'placeholder', 'tl_content'),
//            'stylesheet'    => $assetsPath . 'css/backend/contao-placeholder.css'
//        ),
//
//        $prefix . 'ConfigContao' => array
//        (
//            'callback'      => $namespace . '\\' . $subNamespace . '\BackendModule\ConfigClientModule',
//            'stylesheet'    => $assetsPath . 'css/backend/config-contao.css'
//        )
//   )
//
//));



/**
 * frontend modules
 */

$GLOBALS['FE_MOD']['navigationMenu']['navigation']  = $namespace . '\\' . $subNamespace . '\FrontendModule\NavigationModule';
$GLOBALS['FE_MOD']['news']['newslist']              = $namespace . '\\' . $subNamespace . '\FrontendModule\NewsListModule';



/**
 * Back end form fields
 */

$GLOBALS['BE_FFL']['metaWizard']        = $namespace . '\\' . $subNamespace . '\Widget\MetaWizardWidget';
$GLOBALS['BE_FFL']['imageSize']         = $namespace . '\\' . $subNamespace . '\Widget\ImageSizeWidget';



/**
 * Maintenance
 */

//$GLOBALS['TL_MAINTENANCE'][] = $namespace . '\\' . $subNamespace . '\Maintenance\InitContao';



/**
 * Hooks
 */

$GLOBALS['TL_HOOKS']['initializeSystem'][]                  = array($listenerName . '.listener.system', 'initializeCustomizeSystem');

$GLOBALS['TL_HOOKS']['getPageStatusIcon'][]                 = array($listenerName . '.listener.page', 'getCustomizePageStatusIcon');
$GLOBALS['TL_HOOKS']['generatePage'][]                      = array($listenerName . '.listener.page', 'generateCustomizePage');
$GLOBALS['TL_HOOKS']['modifyFrontendPage'][]                = array($listenerName . '.listener.page', 'modifyCustomizeFrontendPage');

$GLOBALS['TL_HOOKS']['getContentElement'][]                 = array($listenerName . '.listener.content', 'getCustomizeContentElement');

$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][]            = array($listenerName . '.listener.frontend_template', 'outputCustomizeFrontendTemplate');
$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][]             = array($listenerName . '.listener.frontend_template', 'parseCustomizeFrontendTemplate');

//$GLOBALS['TL_HOOKS']['replaceInsertTags'][]                 = array($listenerName . '.listener.insert_tags', 'replaceCustomizeInsertTags');

$GLOBALS['TL_HOOKS']['simpleAjaxFrontend'][]				= array($listenerName . '.listener.ajax', 'parseAjaxRequest');
//$GLOBALS['TL_HOOKS']['simpleAjax'][]						= array($listenerName . '.listener.ajax', 'parseAjaxRequest');



/**
 * Inherit group permissions
 */

//$GLOBALS['TL_PERMISSIONS'][] = 'placeholders';
//$GLOBALS['TL_PERMISSIONS'][] = 'placeholderp';



/**
 * Page types
 */

$GLOBALS['TL_PTY']['regular_redirect'] = $namespace . '\\' . $subNamespace . '\Page\RegularRedirectPage';



/**
 * Register models
 */

//$GLOBALS['TL_MODELS']['tl_iido_placeholder']        = $namespace . '\\' . $subNamespace . '\Model\PlaceholderModel';



/**
 * Register the auto_item keywords
 */

//$GLOBALS['TL_AUTO_ITEM'][] = "article";
//$GLOBALS['TL_AUTO_ITEM'][] = "artikel";
//$GLOBALS['TL_AUTO_ITEM'][] = "event";
