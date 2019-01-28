<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$assetsPath     = 'bundles/iidobasic/';

//if( file_exists(TL_ROOT . '/web/' . $assetsPath . 'css/backend/backend.css') && TL_MODE == "BE" )
//{
//    $GLOBALS['TL_CSS'][] = $assetsPath . 'css/backend/backend.css';
//}

list( $namespace, $subNamespace, $subName, $prefix, $tablePrefix, $listenerName ) = \IIDO\BasicBundle\Config\BundleConfig::getBundleConfigArray();

$ns = $namespace . '\\' . $subNamespace;



/**
 * Backend modules
 */

//$GLOBALS['BE_MOD']['design'][ $prefix . 'StyleSelector' ] = array
//(
//    'tables' => array( $tablePrefix . 'style_selector')
//);

array_insert($GLOBALS['BE_MOD']['system'], 2, array
(
    $prefix . 'WebsiteConfig' => array
    (
        'tables'    => array($tablePrefix . 'website_config')
    ),

    $prefix . 'WebsiteStyles' => array
    (
        'tables'    => array($tablePrefix . 'website_styles')
    )
));


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

$GLOBALS['TL_CTE']['news']['newslist']                      = '\ModuleNewsList';

if( \IIDO\BasicBundle\Config\BundleConfig::isActiveBundle('codefog/contao-news_categories') )
{
    $GLOBALS['TL_CTE']['news']['newscategories']            = \Codefog\NewsCategoriesBundle\FrontendModule\NewsCategoriesModule::class;
}

$GLOBALS['TL_CTE']['module']['iido_navigation']             = $ns . '\ContentElement\NavigationElement';
$GLOBALS['TL_CTE']['module']['iido_filesFilter']            = $ns . '\ContentElement\FilesFilterElement';
$GLOBALS['TL_CTE']['module']['iido_detail']                 = $ns . '\ContentElement\DetailElement';
$GLOBALS['TL_CTE']['module']['iido_articleTeaser']          = $ns . '\ContentElement\ArticleTeaserElement';
$GLOBALS['TL_CTE']['module']['iido_weather']                = $ns . '\ContentElement\WeatherElement';
$GLOBALS['TL_CTE']['module']['iido_imprint']                = $ns . '\ContentElement\ImprintElement';

$GLOBALS['TL_CTE']['module']['iido_login']                  = $ns . '\ContentElement\LoginElement';
//$GLOBALS['TL_CTE']['module']['iido_navigation']          = $ns . '\FrontendModule\NavigationModule';


$GLOBALS['TL_CTE']['iido_wrapper']['iido_wrapperStart']     = $ns . '\ContentElement\WrapperStartElement';
$GLOBALS['TL_CTE']['iido_wrapper']['iido_wrapperSeparator'] = $ns . '\ContentElement\WrapperSeparatorElement';
$GLOBALS['TL_CTE']['iido_wrapper']['iido_wrapperStop']      = $ns . '\ContentElement\WrapperStopElement';
$GLOBALS['TL_CTE']['module']['iido_flip']                   = $ns . '\ContentElement\FlipElement';
$GLOBALS['TL_CTE']['tracking']['iido_tracking']             = $ns . '\ContentElement\TrackingElement';


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
$GLOBALS['BE_FFL']['explanation']       = $ns . '\Widget\ExplanationWidget';
$GLOBALS['BE_FFL']['listWizard']        = $ns . '\Widget\ListWizardWidget';



/**
 * Front end form fields
 */

$GLOBALS['TL_FFL']['radioTable']        = $ns . '\FormField\RadioButtonTable';
$GLOBALS['TL_FFL']['databaseSelect']    = $ns . '\FormField\DatabaseSelect';
$GLOBALS['TL_FFL']['pickdate']          = $ns . '\FormField\PickDate';



/**
 * Maintenance
 */

$GLOBALS['TL_MAINTENANCE'][]    = $ns . '\Maintenance\WebsiteConfigMaintenance';



/**
 * Hooks
 */

$GLOBALS['TL_HOOKS']['initializeSystem'][]                  = array($listenerName . '.system', 'initializeCustomizeSystem');

$GLOBALS['TL_HOOKS']['getPageStatusIcon'][]                 = array($listenerName . '.page', 'getCustomizePageStatusIcon');
$GLOBALS['TL_HOOKS']['generatePage'][]                      = array($listenerName . '.page', 'generateCustomizePage');
$GLOBALS['TL_HOOKS']['modifyFrontendPage'][]                = array($listenerName . '.page', 'modifyCustomizeFrontendPage');

$GLOBALS['TL_HOOKS']['getContentElement'][]                 = array($listenerName . '.content', 'getCustomizeContentElement');

$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][]            = array($listenerName . '.frontend_template', 'outputCustomizeFrontendTemplate');
$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][]             = array($listenerName . '.frontend_template', 'parseCustomizeFrontendTemplate');

$GLOBALS['TL_HOOKS']['outputBackendTemplate'][]             = array($listenerName . '.backend_template', 'outputCustomizeBackendTemplate');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]              = array($listenerName . '.backend_template', 'parseCustomizeBackendTemplate');

//$GLOBALS['TL_HOOKS']['replaceInsertTags'][]                 = array($listenerName . '.insert_tags', 'replaceCustomizeInsertTags'); // IN SERVICE!!

$GLOBALS['TL_HOOKS']['simpleAjaxFrontend'][]                = array($listenerName . '.ajax', 'parseAjaxRequest');
//$GLOBALS['TL_HOOKS']['simpleAjax'][]                        = array($listenerName . '.ajax', 'parseAjaxRequest');

$GLOBALS['TL_HOOKS']['getCombinedFile'][]                   = array($listenerName . '.combiner', 'getCustomizeCombinedFile');

//$GLOBALS['TL_HOOKS']['getForm'][]                           = array($listenerName . '.form', 'getCustomizeForm');
$GLOBALS['TL_HOOKS']['compileFormFields'][]                 = array($listenerName . '.form', 'compileCustomizeFormFields');
$GLOBALS['TL_HOOKS']['loadFormField'][]                     = array($listenerName . '.form', 'loadCustomizeFormField');
$GLOBALS['TL_HOOKS']['parseWidget'][]                       = array($listenerName . '.form', 'parseCustomizeWidget');

$GLOBALS['TL_HOOKS']['parseArticles'][]                     = array($listenerName . '.news', 'parseCustomizeArticles');

$GLOBALS['TL_HOOKS']['importUser'][]                        = array($listenerName . '.user', 'importCustomizeUser');



/**
 * Group permissions
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

if( \IIDO\BasicBundle\Helper\CronHelper::isActive("weather") )
{
    $GLOBALS['TL_CRON']['hourly']['generateWeatherData'] = array($ns . '\Cron\WeatherDataCron', 'generateCustomizeWeatherData');
}



/**
 * Wrapper elements
 */

// START
$GLOBALS['TL_WRAPPERS']['start'][]      = 'iido_wrapperStart';


// STOP
$GLOBALS['TL_WRAPPERS']['stop'][]       = 'iido_wrapperStop';


// SEPARATOR
$GLOBALS['TL_WRAPPERS']['separator'][]  = 'iido_wrapperSeparator';