<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\BackendModule;


use IIDO\BasicBundle\Helper\BackendHelper;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\WebsiteStylesHelper;
use IIDO\BasicBundle\Model\WebsiteStyleModel;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper;


/**
 * Backend Module: Website Styles
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class WebsiteStylesModule extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate  = 'be_mod_iido_websiteStyles';



    public function generate()
    {
        switch( \Input::get("mode") )
        {
            case "new":
                $this->strTemplate = $this->strTemplate . '_new';
                break;

            case "edit":
                $this->strTemplate = $this->strTemplate . '_edit';
                break;

            case "delete":
                $this->deleteStyle();
                break;
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
//        \Controller::loadLanguageFile("default");

        $GLOBALS['TL_CSS'][] = 'bundles/iidobasic/css/backend/website-styles.css||static';
        $GLOBALS['TL_CSS'][] = 'bundles/iidobasic/css/backend/dropdown.css||static';

        switch( \Input::get("mode") )
        {
            case "new":
                $this->renderNewStyle();
                break;

            case "edit":
                $this->renderEditStyle();

                if( \Input::post("FORM_SUBMIT") === "tl_iido_basic_website_styles_edit" )
                {
                    $this->updateStyles();
                }

                break;

            default:
                $this->Template->styles                 = WebsiteStyleModel::findAll();
                $this->Template->newStylesAvailable     = $this->checkIfNewStyleIsPossible();
                break;
        }
    }



    protected function deleteStyle()
    {
        $objRootPage = \PageModel::findByPk( \Input::get("id") );

        if( $objRootPage )
        {
            WebsiteStyleModel::deleteConfigFile( $objRootPage->alias );

            \Controller::redirect( \Controller::addToUrl('', '', array('mode', 'id')) );
        }
    }



    protected function renderNewStyle()
    {
        $this->Template->rootPageOptions = BackendHelper::renderOptions( $this->getRootPages(), 'alias', 'title', true );

        if( \Input::post("FORM_SUBMIT") === "tl_iido_basic_website_styles_new" )
        {
            WebsiteStyleModel::addNewConfigFile( \Input::post('rootPage') );

            $objRootPage = \PageModel::findOneByAlias( \Input::post('rootPage') );

            \Controller::redirect( \Controller::addToUrl('mode=edit&id=' . $objRootPage->id ) );
        }
    }



    protected function renderEditStyle()
    {
//        \Controller::loadLanguageFile('default');
        \Controller::loadLanguageFile('tl_content');

        $objRootPage    = \PageModel::findByPk( \Input::get("id") );
        $fileContent    = file_get_contents( WebsiteStyleModel::getConfigFilePath( $objRootPage->alias ) );
        $arrFileRows    = explode("\n", $fileContent);
        $arrFormLegends = array();

        $legendNum      = -1;

        $openWizard         = false;
        $wizardName         = '';
        $arrWizardFields    = array();
        $legendConfig       = array();
        $wzKey              = 0;


        foreach( $arrFileRows as $fileRow )
        {
            $fileRow = trim($fileRow);

            if( strlen($fileRow) )
            {
                if( preg_match('/^\@/', $fileRow) )
                {
                    continue;
                }

                if( preg_match('/^\/\//', $fileRow) )
                {
                    $wzKey              = 0;
                    $openWizard         = false;
                    $legendNum++;

                    $legendConfig       = array();
                    $arrLegendFields    = array();

                    $fileRowParts       = explode(";", trim(preg_replace('/^\/\//', '', $fileRow)));

                    if( count($fileRowParts) > 1 )
                    {
                        $openWizard = true;
                        $arrFields  = array();
                        $arrLegendConfigFields = array();

                        foreach(explode(",", $fileRowParts[2]) as $strFieldName)
                        {
                            $arrFieldNameParts  = explode("-", $strFieldName);
                            $fieldName          = $arrFieldNameParts[0];
                            $fieldType          = $arrFieldNameParts[1];

                            $arrFieldEval       = array('tl_class'=>'field-' . $fieldName, 'style'=>'');
                            $arrFieldOptions    = array();

                            if( $fieldType === "align" )
                            {
                                $fieldType = 'radioTable';

                                $arrFieldEval['cols'] = 3;
//                                $arrFieldEval['style'] = $arrFieldEval['style'] . 'width:145px;';
                            }
                            elseif( $fieldType === "ocColor" )
                            {
                                $fieldType = 'text';
                            }
                            elseif( $fieldType === "unit" )
                            {
                                $fieldType = 'inputUnit';

                                $arrFieldOptions = $GLOBALS['TL_CSS_UNITS'];

                                $arrFieldEval['includeBlankOption'] = TRUE;
                                $arrFieldEval['rgxp'] = 'digit_auto_inherit';
                                $arrFieldEval['maxlength'] = 20;
                            }

                            $arrFieldConfig = array
                            (
                                'name'      => $fieldName,
                                'label'     => $this->getFormFieldLabel($fileRowParts[1], $fieldName),
                                'inputType' => $fieldType?:'text',
                                'eval'      => $arrFieldEval
                            );

                            if( $fieldType === "radioTable" )
                            {
                                $arrFieldConfig['default'] = 'header_left';
                                $arrFieldConfig['options'] = $GLOBALS['TL_LANG']['tl_content']['options']['headlineFloating'];
                            }

                            if( count($arrFieldOptions) )
                            {
                                $arrFieldConfig['options'] = $arrFieldOptions;
                            }

                            $arrFields[ $fieldName ] = $arrFieldConfig;

                            $arrLegendConfigFields[] = array
                            (
                                'name'      => $fieldName,
                                'type'      => $fieldType?:'text'
                            );
                        }

                        if( count($arrFields) )
                        {
                            $arrLegendFields[] = array
                            (
                                'name'      => $fileRowParts[1],
                                'label'     => $this->getFormFieldLabel($fileRowParts[1]),
                                'inputType' => 'multiColumnWizard',
                                'eval'      => array('columnFields'=>$arrFields)
                            );
                        }

                        $legendConfig = array
                        (
                            'name'      => $fileRowParts[1],
                            'fields'    => $arrLegendConfigFields
                        );
                    }

                    $arrFormLegends[ $legendNum ] = array
                    (
                        'name'      => $fileRowParts[0],
                        'key'       => preg_replace(array('/ /', '/\(/', '/\)/'), array('-', '', ''), $fileRowParts[0]),
                        'fields'    => $arrLegendFields,
                        'config'    => $legendConfig
                    );
                }
                else
                {
                    $arrRowParts    = explode(":", $fileRow);
                    $strFieldName   = preg_replace('/^\$/', '', trim($arrRowParts[0]));

                    $arrRowParts    = explode("//", $arrRowParts[1]);
                    $strFieldValue  = preg_replace(array('/\';$/', '/^\'/', '/;$/'), array('', '', ''), trim($arrRowParts[0]));
                    $strFieldType   = trim($arrRowParts[1]);
                    $arrOptions     = array();
                    $arrDefFieldEval    = array();

                    if( $strFieldType === "color" )
                    {
                        $select = $color = $trans = '';

                        if( preg_match('/rgba/', $strFieldValue) )
                        {
                            $arrColor   = trimsplit(',', preg_replace(array('/^rgba\(/' , '\)$'), array('', ''), trim($strFieldValue)));

                            $trans      = array_pop( $arrColor );

                            $color      = ColorHelper::compileRGBtoHex( $arrColor );
                            $trans      = (floatval($trans) * 100);
                        }
                        elseif( preg_match('/rgb/', $strFieldValue) )
                        {
                            $arrColor   = trimsplit(',', preg_replace(array('/^rgba\(/' , '\)$'), array('', ''), trim($strFieldValue)));

                            $color      = ColorHelper::compileRGBtoHex( $arrColor );
                        }
                        elseif( $strFieldValue !== "transparent")
                        {
                            $color = preg_replace('/^\#/', '', $strFieldValue);
                        }

                        $strFieldValue = array($color, $trans, $select);

                        $arrDefFieldEval = array
                        (
                            'maxlength'         => 64,
                            'multiple'          => TRUE,
                            'isHexColor'        => TRUE,
                            'decodeEntities'    => TRUE,
                            'colorpicker'       => TRUE,
                            'size'              => 2,

                            'disableSelect'     => TRUE,

                            'tl_class'          => 'field-color'
                        );

                        $strFieldType = 'text';
                    }
                    elseif( $strFieldType === "unit" )
                    {
                        $strFieldType = 'inputUnit';

                        preg_match('/([0-9]+)([a-z%]{1,4})/', $strFieldValue, $valueMatches);

                        $strFieldValue = array('value'=>$valueMatches[1], 'unit'=>$valueMatches[2]);


//                        $arrUnits = array();
//                        foreach($GLOBALS['TL_CSS_UNITS'] as $unit)
//                        {
//                            $arrUnits[] = array('value'=>$unit,'label'=>$unit);
//                        }
                        $arrOptions = $GLOBALS['TL_CSS_UNITS'];

                        $arrDefFieldEval = array
                        (
                            'includeBlankOption'    => TRUE,
                            'rgxp'                  => 'digit_auto_inherit',
                            'maxlength'             => 20,
                        );

                    }
                    elseif( $strFieldType === "select" )
                    {
//                        $arrUnits = array();
//                        foreach($GLOBALS['TL_LANG']['IIDO']['WebsiteStyles']['options_' . $strFieldName] as $key => $unit)
//                        {
//                            $arrUnits[] = array('value'=>$key,'label'=>$unit);
//                        }

                        $arrOptions = $GLOBALS['TL_LANG']['IIDO']['WebsiteStyles']['options_' . $strFieldName];
                    }
                    elseif( $strFieldType === "align" )
                    {
                        $strFieldValue = 'header_' . $strFieldValue;
                    }

                    if( $openWizard )
                    {
                        $wzKey = $wzKey ?:0;

                        $arrFieldName   = explode("_", $strFieldName);
//                        $arrFieldConfig = $arrFormLegends[ $legendNum ]['fields'][ (count($arrFormLegends[ $legendNum ]['fields']) - 1) ];

//                        echo "<pre>";
//                        print_r( $fileRow );
//                        echo "<br>";
//                        print_r( $wzKey );
//                        echo "<br>";
//                        print_r( $arrFieldName[1] );
//                        echo "<br>";
//                        print_r( $arrFieldConfig );
//
//                        echo "<br>";
//                        print_r( $strFieldValue );
//
////                        echo "<br>";
////                        print_r( $arrFieldConfig );
//                        exit;

//                        $fieldKey = $arrFormLegends[ $legendNum ]['fields'][ (count($arrFormLegends[ $legendNum ]['fields']) - 1) ][''];

                        if( $strFieldType === "ocColor" )
                        {
                            $strFieldValue = preg_replace('/^#/', '', $strFieldValue);
                        }

                        $arrFormLegends[ $legendNum ]['fields'][ (count($arrFormLegends[ $legendNum ]['fields']) - 1) ]['value'][ $wzKey ][ $arrFieldName[1] ] = $strFieldValue;
//                        $arrFormLegends[ $legendNum ]['fields'][ (count($arrFormLegends[ $legendNum ]['fields']) - 1) ]['value'][ $wzKey ][] = $strFieldValue;
                    }
                    else
                    {
                        $openWizard = false;
                        $wzKey = 0;

                        $arrDefFieldConfig = array
                        (
                            'name'      => $strFieldName,
                            'label'     => $this->getFormFieldLabel( $strFieldName ),
                            'inputType' => $strFieldType,
                            'value'     => $strFieldValue
                        );

                        if( count($arrOptions) )
                        {
                            $arrDefFieldConfig['options'] = $arrOptions;
                        }

                        if( count($arrDefFieldEval) )
                        {
                            $arrDefFieldConfig['eval'] = $arrDefFieldEval;

//                            echo "<pre>";
//                            print_r( $arrDefFieldConfig );
//                            exit;
                        }

                        $arrFormLegends[ $legendNum ]['fields'][] = $arrDefFieldConfig;
                    }
                }
            }
            else
            {
                if( $openWizard )
                {
                    $wzKey = ($wzKey+1);
                }
            }
        }

//        foreach($arrFileRows as $fileRow)
//        {
//            $fileRow = trim($fileRow);
//
//            if( strlen($fileRow) )
//            {
//                if( preg_match('/^\/\//', $fileRow) )
//                {
//                    $legendNum++;
//                    $legendConfig = array();
//
//                    $fileRowParts = explode(";", trim(preg_replace('/^\/\//', '', $fileRow)));
//
//                    if( count($fileRowParts) > 1 )
//                    {
//                        $legendConfig = array
//                        (
//                            'name'      => $fileRowParts[1],
//                            'fields'    => explode(",", $fileRowParts[2])
//                        );
//                    }
//
//                    $arrFormLegends[ $legendNum ] = array
//                    (
//                        'name'      => $fileRowParts[0],
//                        'key'       => preg_replace(array('/ /', '/\(/', '/\)/'), array('-', '', ''), $fileRowParts[0]),
//                        'fields'    => array(),
//                        'config'    => $legendConfig
//                    );
//                }
//                else
//                {
//                    $rowParts       = explode(":", $fileRow);
//                    $rowName        = preg_replace('/^\$/', '', trim($rowParts[0]));
//                    $arrValue       = preg_split('/\/\//', trim(preg_replace('/\'/', '', $rowParts[1])));
//
//                    $rowValue       = preg_replace('/;$/', '', trim( $arrValue[0] ));
//                    $strType        = $arrValue[1];
//
//                    if( $openWizard )
//                    {
//                        $nameParts      = explode("_", $rowName);
//                        $nameParts[0]   = preg_split('/(\d+)/', $nameParts[0], -1, PREG_SPLIT_NO_EMPTY)[0];
//
//                        $arrWizardsFields[] = array
//                        (
//                            'name'  => $nameParts[1],
//                            'value' => $rowValue,
//                            'label' => $this->getFormFieldLabel($nameParts[0], $nameParts[1])
//                        );
//                    }
//                    else
//                    {
//                        $strType     = $strType?:'text';
//
//
//                        if( preg_match('/_/', $rowName) )
//                        {
////                            $strType    = 'multiColumnWizard';
//                            $openWizard = true;
//
//                            $nameParts  = explode("_", $rowName);
////                            $wizardName = $nameParts[0];
//
//                            $wzName = preg_split('/(\d+)/', $nameParts[0], -1, PREG_SPLIT_NO_EMPTY)[0];
//
//                            if( $wzName === $wizardName )
//                            {
//
//                            }
//                            else
//                            {
//                                $arrWizardsFields[] = array
//                                (
//                                    'name'  => $nameParts[1],
//                                    'value' => $rowValue,
//                                    'label' => $this->getFormFieldLabel($wizardName, $nameParts[1])
//                                );
//                            }
//
//                            $wizardName = $wzName;
//                        }
//                        elseif( preg_match('/^#/', $rowValue) || preg_match('/^rgb/', $rowValue)
//                            || preg_match('/^transparent/', $rowValue) || preg_match('/color/', $rowName)  )
//                        {
//                            $strType = 'color';
//                        }
//
//                        if( !$openWizard )
//                        {
//                            $arrFormLegends[ $legendNum ]['fields'][] = array
//                            (
//                                'name'      => $rowName,
//                                'label'     => $this->getFormFieldLabel( $rowName ),
//                                'value'     => $rowValue,
//                                'type'      => $strType
//                            );
//                        }
//                    }
//                }
//            }
//            else
//            {
//                if( $openWizard )
//                {
//                    $arrFormLegends[ $legendNum ]['fields'][] = array
//                    (
//                        'name'      => $wizardName,
//                        'label'     => $this->getFormFieldLabel( $wizardName ),
//                        'value'     => $arrWizardsFields,
//                        'type'      => 'multiColumnWizard'
//                    );
//                }
//
//                $openWizard         = false;
////                $wizardName         = '';
////                $arrWizardsFields   = array();
//            }
//        }
//echo "<pre>"; print_r( $arrFormLegends ); exit;
        $this->Template->arrForm = $arrFormLegends;
    }



    protected function updateStyles()
    {
        $objRootPage = \PageModel::findByPk( \Input::get("id") );

        WebsiteStylesHelper::updateConfigFileRows( $objRootPage->alias, \Input::post("field") );
    }



    protected function getRootPages($ignoreConfigured = true)
    {
        $arrRootPages   = array();
        $objRootPages   = \PageModel::findBy(array('type=?', 'fallback=?'), array('root', '1') );

        if( $objRootPages )
        {
            while( $objRootPages->next() )
            {
                if( $ignoreConfigured )
                {
                    if( !WebsiteStyleModel::checkIfStylesIsConfigured( $objRootPages->alias ) )
                    {
                        $arrRootPages[] = $objRootPages->current();
                    }
                }
                else
                {
                    $arrRootPages[] = $objRootPages->current();
                }
            }
        }

        return $arrRootPages;
    }



    protected function checkIfNewStyleIsPossible()
    {
        $isPossible     = false;
        $arrRootPages   = $this->getRootPages();

        if( count($arrRootPages) )
        {
            foreach($arrRootPages as $objRootPage)
            {
                if( !WebsiteStyleModel::checkIfStylesIsConfigured( $objRootPage->alias ) )
                {
                    $isPossible = true;
                }
            }
        }

        return $isPossible;
    }



    protected function getFormFieldLabel( $labelKey, $subLabelKey = '' )
    {
        $strLang = $GLOBALS['TL_LANG']['IIDO']['WebsiteStyles']['field'][ $labelKey . ($subLabelKey ? '_' . $subLabelKey : '') ];
        return $strLang ?: $labelKey;
    }

}
