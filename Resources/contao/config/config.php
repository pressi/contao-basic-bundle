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

//$assetsPath     = 'bundles/iidobasic/';

//if( file_exists(TL_ROOT . '/web/' . $assetsPath . 'css/backend/backend.css') && TL_MODE == "BE" )
//{
//    $GLOBALS['TL_CSS'][] = $assetsPath . 'css/backend/backend.css';
//}

list( $namespace, $subNamespace, $subName, $prefix, $tablePrefix, $listenerName ) = \IIDO\BasicBundle\Config\BundleConfig::getBundleConfigArray();

$ns = $namespace . '\\' . $subNamespace;

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
//            'callback'      => $ns . '\Backend\Module\Placeholder',
//            'tables'        => array($tablePrefix . 'placeholder', 'tl_content'),
//            'stylesheet'    => $assetsPath . 'css/backend/contao-placeholder.css'
//        ),

//        $prefix . 'ConfigContao' => array
//        (
//            'callback'      => $ns . '\BackendModule\ConfigClientModule',
//            'stylesheet'    => $assetsPath . 'css/backend/config-contao.css'
//        )
//   )
//
//));
//$beUser = \BackendUser::getContainer();
//
//if( ($beUser->getParameter("id") === 1 || $beUser->getParameter("id") === 2) && $beUser->getParameter("isAdmin") )
//{
//    array_insert($GLOBALS['BE_MOD'], 3, array
//    (
//        $prefix => array
//        (
//            $prefix . 'System' => array
//            (
//                'callback'      => $ns . '\BackendModule\SystemModule'
//            )
//        )
//    ));
//}



/**
 * Content elements
 */

$GLOBALS['TL_CTE']['module']['iido_navigation']             = $ns . '\ContentElement\NavigationElement';
$GLOBALS['TL_CTE']['module']['iido_filesFilter']            = $ns . '\ContentElement\FilesFilterElement';
$GLOBALS['TL_CTE']['module']['iido_detail']                 = $ns . '\ContentElement\DetailElement';
$GLOBALS['TL_CTE']['module']['iido_articleTeaser']          = $ns . '\ContentElement\ArticleTeaserElement';
$GLOBALS['TL_CTE']['module']['iido_weather']                = $ns . '\ContentElement\WeatherElement';
//$GLOBALS['TL_CTE']['module']['iido_navigation']          = $ns . '\FrontendModule\NavigationModule';



/**
 * Front end modules
 */

$GLOBALS['FE_MOD']['inherit']['iido_inheritArticle']    = $ns . '\FrontendModule\InheritArticleModule';
$GLOBALS['FE_MOD']['navigationMenu']['navigation']      = $ns . '\FrontendModule\NavigationModule';
$GLOBALS['FE_MOD']['news']['newslist']                  = $ns . '\FrontendModule\NewsListModule';



/**
 * Back end form fields
 */

$GLOBALS['BE_FFL']['metaWizard']        = $ns . '\Widget\MetaWizardWidget';
$GLOBALS['BE_FFL']['imageSize']         = $ns . '\Widget\ImageSizeWidget';
$GLOBALS['BE_FFL']['text']              = $ns . '\Widget\TextFieldWidget';
$GLOBALS['BE_FFL']['iidoTag']           = $ns . '\Widget\TagsFieldWidget';



/**
 * Front end form fields
 */

$GLOBALS['TL_FFL']['radioTable']        = $ns . '\FormField\RadioButtonTable';



/**
 * Maintenance
 */

//$GLOBALS['TL_MAINTENANCE'][] = $ns . '\Maintenance\InitContao';



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

$GLOBALS['TL_HOOKS']['outputBackendTemplate'][]             = array($listenerName . '.listener.backend_template', 'outputCustomizeBackendTemplate');

$GLOBALS['TL_HOOKS']['replaceInsertTags'][]                 = array($listenerName . '.listener.insert_tags', 'replaceCustomizeInsertTags');

$GLOBALS['TL_HOOKS']['simpleAjaxFrontend'][]                = array($listenerName . '.listener.ajax', 'parseAjaxRequest');
//$GLOBALS['TL_HOOKS']['simpleAjax'][]                        = array($listenerName . '.listener.ajax', 'parseAjaxRequest');

$GLOBALS['TL_HOOKS']['getCombinedFile'][]                   = array($listenerName . '.listener.combiner', 'getCustomizeCombinedFile');

//$GLOBALS['TL_HOOKS']['getForm'][]                           = array($listenerName . '.listener.form', 'getCustomizeForm');
$GLOBALS['TL_HOOKS']['loadFormField'][]                     = array($listenerName . '.listener.form', 'loadCustomizeFormField');
$GLOBALS['TL_HOOKS']['parseWidget'][]                       = array($listenerName . '.listener.form', 'parseCustomizeWidget');



/**
 * Inherit group permissions
 */

//$GLOBALS['TL_PERMISSIONS'][] = 'placeholders';
//$GLOBALS['TL_PERMISSIONS'][] = 'placeholderp';



/**
 * Page types
 */

$GLOBALS['TL_PTY']['regular_redirect'] = $ns . '\Page\RegularRedirectPage';



/**
 * Register models
 */

//$GLOBALS['TL_MODELS']['tl_iido_placeholder']        = $ns . '\Model\PlaceholderModel';



/**
 * Register the auto_item keywords
 */

//$GLOBALS['TL_AUTO_ITEM'][] = "article";
//$GLOBALS['TL_AUTO_ITEM'][] = "artikel";
//$GLOBALS['TL_AUTO_ITEM'][] = "event";



/**
 * Cron jobs
 */

//$GLOBALS['TL_CRON']['hourly']['generateWeatherData'] = array($ns . '\Cron\WeatherDataCron', 'generateCustomizeWeatherData');
