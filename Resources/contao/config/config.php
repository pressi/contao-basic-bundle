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

$namespace          = 'IIDO';
$subNamespace       = 'BasicBundle';
$subName            = strtolower( preg_replace('/Bundle$/', '', $subNamespace) );

$prefix             = strtolower($namespace);
$tablePrefix        = 'tl_' . $prefix . '_';

$listenerName       = $prefix . '_' . $subName;
//\IIDO\WebsiteBundle\Loader::loadConfig("dcaVars");



/**
 * Backend modules
 */

array_insert($GLOBALS['BE_MOD'], 3, array
(

    $prefix => array
   (
//        $prefix . 'Placeholder' => array
//        (
//            'callback'      => $namespace . '\\' . $subNamespace . '\Backend\Module\Placeholder',
//            'tables'        => array($tablePrefix . 'placeholder', 'tl_content'),
//            'stylesheet'    => $assetsPath . 'css/backend/contao-placeholder.css'
//        ),

        $prefix . 'InitContao' => array
        (
            'callback'      => $namespace . '\\' . $subNamespace . '\Backend\Module\InitContao',
            'stylesheet'    => $assetsPath . 'css/backend/init-contao.css'
        )
   )

));



/**
 * Back end form fields
 */

$GLOBALS['BE_FFL']['metaWizard'] = 'IIDO\BasicBundle\Widget\MetaWizardWidget';



/**
 * Hooks
 */

$GLOBALS['TL_HOOKS']['initializeSystem'][]                  = array($listenerName . '.listener.system', 'initializeCustomizeSystem');

//$GLOBALS['TL_HOOKS']['generatePage'][]                      = array($listenerName . '.listener.page', 'generateCustomizePage');
//$GLOBALS['TL_HOOKS']['modifyFrontendPage'][]                = array($listenerName . '.listener.page', 'modifyCustomizeFrontendPage');

//$GLOBALS['TL_HOOKS']['getContentElement'][]                 = array($listenerName . '.listener.content', 'getCustomizeContentElement');

//$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][]            = array($listenerName . '.listener.frontend_template', 'outputCustomizeFrontendTemplate');
//$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][]             = array($listenerName . '.listener.frontend_template', 'parseCustomizeFrontendTemplate');

//$GLOBALS['TL_HOOKS']['replaceInsertTags'][]                 = array($listenerName . '.listener.insert_tags', 'replaceCustomizeInsertTags');



/**
 * Inherit group permissions
 */

//$GLOBALS['TL_PERMISSIONS'][] = 'placeholders';
//$GLOBALS['TL_PERMISSIONS'][] = 'placeholderp';



/**
 * Register models
 */

//$GLOBALS['TL_MODELS']['tl_iido_placeholder']        = $namespace . '\\' . $subNamespace . '\Model\PlaceholderModel';