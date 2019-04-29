<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


use IIDO\BasicBundle\Model\GlobalCategoryModel;


/**
 * Provide methods to handle file meta information.
 *
 * @property array $metaFields
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class MetaWizardWidget extends \MetaWizard
{

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $count = 0;
        $languages = $this->getLanguages();
        $return = '';
        $taken = array();

        $this->import('Database');

        // Only show the root page languages (see #7112, #7667)
        $objRootLangs = $this->Database->query("SELECT REPLACE(language, '-', '_') AS language FROM tl_page WHERE type='root'");
        $existing = $objRootLangs->fetchEach('language');

        // Also add the existing keys (see #878)
        if (!empty($this->varValue))
        {
            $existing = array_unique(array_merge($existing, array_keys($this->varValue)));
        }

        $languages = array_intersect_key($languages, array_flip($existing));

        // Make sure there is at least an empty array
        if (!is_array($this->varValue) || empty($this->varValue))
        {
            if (count($languages) > 0)
            {
                $this->varValue = array(key($languages)=>array()); // see #4188
            }
            else
            {
                return '<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['metaNoLanguages'] . '</p>';
            }
        }

        // Add the existing entries
        if (!empty($this->varValue))
        {
            $return = '<ul id="ctrl_' . $this->strId . '" class="tl_metawizard">';

            // Add the input fields
            foreach ($this->varValue as $lang=>$meta)
            {
                $return .= '
    <li class="' . (($count % 2 == 0) ? 'even' : 'odd') . '" data-language="' . $lang . '">';

                $return .= '<span class="lang">' . (isset($languages[$lang]) ? $languages[$lang] : $lang) . ' ' . \Image::getHtml('delete.svg', '', 'class="tl_metawizard_img" title="' . $GLOBALS['TL_LANG']['MSC']['delete'] . '" onclick="Backend.metaDelete(this)"') . '</span>';

                // Take the fields from the DCA (see #4327)
                foreach ($this->metaFields as $field=>$attributes)
                {
                    if( $attributes == "textarea" )
                    {
                        $strField = '<textarea name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $field . '_' . $count . '" class="tl_textarea tl_text">' . \StringUtil::specialchars($meta[$field]) . '</textarea>';
                    }
                    elseif( $attributes == "select" || preg_match('/^select_/', $attributes) )
                    {
                        $arrAttributes  = explode("_", $attributes);
                        $arrOptions     = '';

                        if( $arrAttributes[1] == "blank" )
                        {
                            $arrOptions = '<option value="">-</option>';
                        }

                        $arrOptions     .= $this->getSelectOptions($field, \StringUtil::specialchars($meta[$field]));

                        $strField       = '<select name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $field . '_' . $count . '" class="tl_select">' . $arrOptions . '</select>';
                    }
                    elseif( $attributes == "iidoTag")
                    {
                        $arrData = array
                        (
                            'label'     => array('', ''),
                            'inputType' => 'iidoTag',
                            'eval'      => array
                            (
                                'multipleTags' => true
                            )
                        );

                        $strInputName   = $this->strId . '[' . $lang . '][' . $field . ']';
                        $varValue       = $meta[ $field ];
                        $strField       = '';

                        $strClass   = $GLOBALS['BE_FFL']['iidoTag'];
                        $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strField, 'tl_files', $this));

                        $objWidget->id = $field . '_' . $count;

                        $strField = $objWidget->parse();
                    }
                    elseif( $attributes === "color" )
                    {
                        $arrData = array
                        (
                            'label'     => array('', ''),
                            'inputType' => 'text',
                            'eval'      => array
                            (
                                'maxlength'         => 64,
                                'multiple'          => true,
                                'size'              => 2,
                                'colorpicker'       => true,
                                'isHexColor'        => true,
                                'decodeEntities'    => true,
                                'tl_class'          => 'w50 wizard'
                            )
                        );

//                        $strInputName   = $this->strId . '[' . $lang . '][' . $field . ']';
                        $strInputName   = $field . '_' . $count;
                        $varValue       = $meta[ $field ];
                        $arrValue       = \StringUtil::deserialize($varValue, true);
                        $strFieldName   = $field . '_' . $count;
//                        $strFieldName   = $this->strId . '[' . $lang . '][' . $field . ']';

//echo "<pre>"; print_r( $meta ); exit;
                        $strClass   = $GLOBALS['BE_FFL']['text'];

                        $objWidget  = new $strClass($strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strInputName, 'tl_files', $this));

                        $objWidget->id              = $field . '_' . $count;

                        $objWidget->colorpicker     = TRUE;
                        $objWidget->multiple        = TRUE;
                        $objWidget->isHexColor      = TRUE;
                        $objWidget->decodeEntities  = TRUE;
                        $objWidget->maxlength       = 64;
                        $objWidget->size            = 2;
//                        $objWidget->class           = 'w50 wizard';

                        $objWidget->isMetaField     = TRUE;
                        $objWidget->metaPrefix      = $this->strId;
                        $objWidget->metaLang        = $lang;
                        $objWidget->metaField       = $field;

                        $strField   = $objWidget->generate();
                        
                        $wizard     = '';

//                        $strField   = preg_replace('/<select([A-Za-z0-9\[\]\s\-="]{0,})id="ctrl_' . $this->strId . '\[' . $lang . '\]\[' . $field . '\]_([0-9]{1})"/', '<select$1id="ctrl_' . $strFieldName . '_$2"', $strField);


//                        if( $arrData['eval']['colorpicker'] )
//                        {
//                            // Support single fields as well (see #5240)
//                            $strKey     = $arrData['eval']['multiple'] ? $strFieldName . '_0' : $strFieldName;
//                            $strKey2    = $arrData['eval']['multiple'] ? $strFieldName . '_1' : $strFieldName;
//
//                            $wizard .= ' ' . \Image::getHtml('pickcolor.svg', $GLOBALS['TL_LANG']['MSC']['colorpicker'], 'title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['colorpicker']).'" id="moo_' . $strFieldName . '" style="cursor:pointer"') . '
//  <script>
//    window.addEvent("domready", function() {
//      var cl = $("ctrl_' . $strKey . '").value.hexToRgb(true) || [255, 0, 0];
//      new MooRainbow("moo_' . $strFieldName . '", {
//        id: "ctrl_' . $strKey . '",
//        startColor: cl,
//        imgPath: "assets/colorpicker/images/",
//        onComplete: function(color) {
//          $("ctrl_' . $strKey . '").value = color.hex.replace("#", "");
//
//          var strColor = color.hex,
//              strField = $("ctrl_' . $strKey2 . '");
//
//          if(strField.value)
//          {
//              strColor= \'rgba(\' + color.rgb[0] + \',\' + color.rgb[1] + \',\' + color.rgb[2] + \',\' + (strField.value/100) + \')\';
//          }
//
//          $("colorPreview_' . $strFieldName . '").setStyle("background", strColor);
//        }
//      });
//    });
//    var fn = function( el, mode )
//            {
//                var strField, strColor, varValue = el.value;
//
//                if( mode === "color" )
//                {
//                    strField = $("ctrl_' . $strKey2 . '");
//                    varValue = strField.value.toInt();
//
//                    strColor = el.value;
//
//                    if( strColor.length < 3 || (strColor.length > 3 && strColor.length < 6) )
//                    {
//                        strColor = false;
//                    }
//                }
//                else
//                {
//                    strField = $("ctrl_' . $strKey . '");
//                    strColor = strField.value;
//
//                    varValue = varValue.toInt();
//                }
//
//                if(varValue > 0 && strColor)
//                {
//                    var arrColor = strColor.hexToRgb(true);
//
//                    if( varValue > 100 )
//                    {
//                        varValue = 100;
//                    }
//
//                    strColor = \'rgba(\' + arrColor[0] + \',\' + arrColor[1] + \',\' + arrColor[2] + \',\' + (varValue/100) + \')\';
//                }
//                else
//                {
//                    if( strColor )
//                    {
//                        strColor = \'#\' + strColor;
//                    }
//                    else
//                    {
//                        strColor = \'transparent\';
//                    }
//                }
//
//                $("colorPreview_' . $strFieldName . '").setStyle("background", strColor);
//            };
//
//            $("ctrl_' . $strKey2 . '").addEvents({
//    "change": function() { fn(this, "trans"); },
//    "keyup": function() { fn(this, "trans"); }
//});
//            $("ctrl_' . $strKey . '").addEvents({
//    "change": function() { fn(this, "color"); },
//    "keyup": function() { fn(this, "color"); }
//});
//  </script>';
//                        }

                        $strSelectField     = '';
//                        $arrSelectOptions   = $this->getThemeColors();
//
//                        if( count($arrSelectOptions) )
//                        {
//                            $strSelectField = '<select name="' . $strInputName . '[]" id="ctrl_' . $strFieldName . '_2" class="tl_select color-select">';
//                            $strSelectField .= '<option value="">-</option>';
//
//                            foreach($arrSelectOptions as $key => $value)
//                            {
//                                if( is_array($value) )
//                                {
//                                    $strSelectField .= '<optgroup label="' . $key . '">';
//
//                                    foreach($value as $strKey => $strValue)
//                                    {
//                                        $selected = '';
//
//                                        if( $arrValue[2] === preg_replace('/&#35;/', '#', $strKey) )
//                                        {
//                                            $selected = ' selected';
//                                        }
//
//                                        $strSelectField .= '<option value="' . $strKey . '"' . $selected . '>' . $strValue . '</option>';
//                                    }
//
//                                    $strSelectField .= '</optgroup>';
//                                }
//                                else
//                                {
//                                    $selected = '';
//
//                                    if( $arrValue[2] === preg_replace('/&#35;/', '#', $key) )
//                                    {
//                                        $selected = ' selected';
//                                    }
//
//                                    $strSelectField .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
//                                }
//                            }
//
//                            $strSelectField .= '</select>';
//                        }

                        $strField = $strField . $wizard . $strSelectField;
                    }
                    elseif( $attributes === "link" )
                    {
                        $arrData = array
                        (
                            'label'     => array('', ''),
                            'inputType' => 'text',
                            'eval'      => array
                            (
                                'rgxp'=>'url',
                                'decodeEntities'=>true,
                                'maxlength'=>255,
                                'dcaPicker'=>true,
                                'tl_class'=>'w50 wizard'
                            )
                        );

                        $strInputName   = $field . '_' . $count;
                        $varValue       = $meta[ $field ];
                        $arrValue       = \StringUtil::deserialize($varValue, true);

                        $strClass   = $GLOBALS['BE_FFL']['text'];

                        $objWidget  = new $strClass($strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strInputName, 'tl_files', $this));

                        $objWidget->id              = $field . '_' . $count;

                        $objWidget->rgxp            = 'url';
                        $objWidget->decodeEntities  = TRUE;
                        $objWidget->maxlength       = 255;
                        $objWidget->dcaPicker       = TRUE;

//                        $objWidget->class           = 'w50 wizard';

                        $objWidget->isMetaField     = TRUE;
                        $objWidget->metaPrefix      = $this->strId;
                        $objWidget->metaLang        = $lang;
                        $objWidget->metaField       = $field;

                        $strField   = $objWidget->generate();

                        $wizard = \Backend::getDcaPickerWizard($arrData['eval']['dcaPicker'], 'tl_files', $field, $strInputName);

                        $strField = $strField . $wizard;

                    }
                    elseif( preg_match('/globalCategoriesPicker/', $attributes) )
                    {
                        $arrAttr    = explode('_', $attributes);
                        $objGC      = GlobalCategoryModel::findByPk( $arrAttr[1] );

                        if( $objGC->subCategoriesArePages )
                        {
                            $arrData = array
                            (
                                'label'             => [$objGC->title, ''],
                                'inputType'         => 'pageTree',
                                'eval'              => array('fieldType'=>'checkbox','multiple'=>true, 'tl_class'=>'w50 hauto')
                            );

                            if( $objGC->subPagesRoot )
                            {
                                $arrData['eval']['rootNodes'] = [$objGC->subPagesRoot];
                            }

                            $strInputName   = $field . '_' . $lang;
                            $varValue       = \StringUtil::trimsplit(',', $meta[ $field ]);

                            $GLOBALS['TL_DCA']['tl_files' ]['fields'][ $strInputName ] = $arrData;

                            $strClass   = $GLOBALS['BE_FFL'][ 'pageTree' ];
                            $objWidget  = new $strClass($strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strInputName, 'tl_files', $this));

                            $objWidget->id              = $field . '_' . $lang;

                            $objWidget->isMetaField     = TRUE;
                            $objWidget->metaPrefix      = $this->strId;
                            $objWidget->metaLang        = $lang;
                            $objWidget->metaField       = $field;

                            $strField   = $objWidget->generate();
                        }
                        else
                        {
                            $arrData = array
                            (
                                'label'             => array('', ''),
                                'foreignKey'        => 'tl_iido_global_category.title',
                                'inputType'         => $arrAttr[0],
                                'eval'              => array('fieldType'=>'checkbox','rootNodes'=>[$arrAttr[1]], 'tl_class'=>'w50 hauto'),
                                'options_callback'  => array('iido_basic.table.global_category', 'onCategoriesOptionsCallback'),
                            );

                            $strInputName   = $field . '_' . $lang;
                            $varValue       = \StringUtil::trimsplit(',', $meta[ $field ]);

                            $strClass   = $GLOBALS['BE_FFL'][ $arrAttr[0] ];
                            $objWidget  = new $strClass($strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strInputName, 'tl_files', $this));

                            $objWidget->id              = $field . '_' . $lang;

                            $objWidget->isMetaField     = TRUE;
                            $objWidget->metaPrefix      = $this->strId;
                            $objWidget->metaLang        = $lang;
                            $objWidget->metaField       = $field;

                            $strField   = $objWidget->generate();
                        }
                    }
                    elseif( preg_match('/picker$/i', $attributes) )
                    {
                        $strInputName   = $field . '_' . $lang;
                        $varValue       = \StringUtil::trimsplit(',', $meta[ $field ]);

                        $GLOBALS['TL_DCA']['tl_files']['fields'][ $field ]['label'][0]      = $GLOBALS['TL_LANG']['MSC']['aw_' . $field];
                        $GLOBALS['TL_DCA']['tl_files']['fields'][ $strInputName ]['label']  = array($GLOBALS['TL_LANG']['MSC']['aw_' . $field], '');

                        $strClass   = $GLOBALS['BE_FFL'][ $attributes ];
                        $objWidget  = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA']['tl_files']['fields'][ $field ], $strInputName, $varValue, $strInputName, 'tl_files', $this));

                        $objWidget->id              = $field . '_' . $lang;

                        $objWidget->isMetaField     = TRUE;
                        $objWidget->metaPrefix      = $this->strId;
                        $objWidget->metaLang        = $lang;
                        $objWidget->metaField       = $field;

                        $strField   = $objWidget->generate();
                    }
                    elseif( $attributes === "selectConfig" || $attributes === "select" )
                    {
                        $strInputName   = $field . '_' . $lang;
                        $varValue       = $meta[ $field ]; //\StringUtil::trimsplit(',', $meta[ $field ]);

                        $arrData = ($attributes === 'selectConfig' ? $GLOBALS['TL_DCA']['tl_files']['fields'][ $field ] : array());

                        $strClass   = $GLOBALS['BE_FFL'][ 'select' ];
                        $objWidget  = new $strClass($strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strInputName, 'tl_files', $this));

                        $objWidget->id              = $field . '_' . $lang;

                        $objWidget->isMetaField     = TRUE;
                        $objWidget->metaPrefix      = $this->strId;
                        $objWidget->metaLang        = $lang;
                        $objWidget->metaField       = $field;

                        $strField   = $objWidget->generate();

                        $strField = preg_replace('/name="' . $strInputName . '/', 'name="' . $this->strId . '[' . $lang . '][' . $field . ']', $strField);
                    }
                    else
                    {
                        $arrAttributes  = explode("_", $attributes);

                        if( $arrAttributes[1] == 4 )
                        {
                            $arrValue   = deserialize($meta[$field], TRUE);

                            $fieldTop       = '<input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . '][top]" id="ctrl_' . $field . '_' . $count . '_top" class="tl_text_trbl trbl_top" value="' . \StringUtil::specialchars($arrValue['top']) . '"' . (!empty($attributes) ? ' ' . $attributes : '') . '>';
                            $fieldRight     = '<input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . '][right]" id="ctrl_' . $field . '_' . $count . '_right" class="tl_text_trbl trbl_right" value="' . \StringUtil::specialchars($arrValue['right']) . '"' . (!empty($attributes) ? ' ' . $attributes : '') . '>';
                            $fieldBottom    = '<input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . '][bottom]" id="ctrl_' . $field . '_' . $count . '_bottom" class="tl_text_trbl trbl_bottom" value="' . \StringUtil::specialchars($arrValue['bottom']) . '"' . (!empty($attributes) ? ' ' . $attributes : '') . '>';
                            $fieldLeft      = '<input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . '][left]" id="ctrl_' . $field . '_' . $count . '_bottom" class="tl_text_trbl trbl_left" value="' . \StringUtil::specialchars($arrValue['left']) . '"' . (!empty($attributes) ? ' ' . $attributes : '') . '>';

                            $fieldOptions   = '<select name="' . $this->strId . '[' . $lang . '][' . $field . '][unit]" class="tl_select_unit">' . $this->getSelectOptions('units', $arrValue['unit']) . '</select>';

                            $strField       = $fieldTop . $fieldRight . $fieldBottom . $fieldLeft . $fieldOptions;
                        }
                        else
                        {
                            $strField   = '<input name="' . $this->strId . '[' . $lang . '][' . $field . ']" type="text" name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $field . '_' . $count . '" class="tl_text" value="' . \StringUtil::specialchars($meta[$field]) . '"' . (!empty($attributes) ? ' ' . $attributes : '') . '>';
                        }
                    }

                    $strField = preg_replace('/<h3><\/h3>/', '', $strField);

                    $wType = $field;

                    if( preg_match('/globalCategoriesPicker/', $attributes) )
                    {
                        $wType = 'global-category';
                    }

                    $return .= '<div class="meta-widget widget-' . $wType . '"><label for="ctrl_' . $field . '_' . $count . '">' . $GLOBALS['TL_LANG']['MSC']['aw_' . $field] . '</label> ' . $strField . '</div>';
                }

                $return .= '
    </li>';

                $taken[] = $lang;
                ++$count;
            }

            $return .= '
  </ul>';
        }

        $options = array('<option value="">-</option>');

        // Add the remaining languages
        foreach ($languages as $k=>$v)
        {
            $options[] = '<option value="' . $k . '"' . (in_array($k, $taken) ? ' disabled' : '') . '>' . $v . '</option>';
        }

        $return .= '
  <div class="tl_metawizard_new">
    <select name="' . $this->strId . '[language]" class="tl_select tl_chosen" onchange="Backend.toggleAddLanguageButton(this)">' . implode('', $options) . '</select> <input type="button" class="tl_submit" disabled value="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['aw_new']) . '" onclick="Backend.metaWizard(this,\'ctrl_' . $this->strId . '\')">
  </div>';

        return $return;
    }



    protected function getSelectOptions( $fieldName, $selectedField)
    {
        $strOptions = '';
        $arrOptions = $GLOBALS['TL_LANG']['OPT'][ 'aw_' . $fieldName ];

        if( preg_match('/position$/', $fieldName) )
        {
            $arrOptions = $GLOBALS['TL_LANG']['RSCE']['positions'];
        }
        elseif( $fieldName == "units" )
        {
            $arrOptions = $GLOBALS['TL_CSS_UNITS'];
        }

        foreach($arrOptions as $optionValue => $optionName)
        {
            $selected = '';
            
            if( $selectedField == $optionValue )
            {
                $selected = ' selected';
            }
            
            $strOptions .= '<option value="' . $optionValue . '"' . $selected . '>' . $optionName . '</option>';
        }

        return $strOptions;
    }




    /**
     * Trim the values and add new languages if necessary
     *
     * @param mixed $varInput
     *
     * @return mixed
     */
    public function validator($varInput)
    {
        foreach ($varInput as $k => $v)
        {
            if ($k != 'language')
            {
                foreach($v as $vk => $vv)
                {
                    if( is_array($vv) )
                    {
                        $varInput[ $k ][ $vk ] = serialize( $vv );
                    }
                }
            }
        }

        return parent::validator( $varInput );
    }



    protected function getThemeColors()
    {
        $arrOptions = array();

        $objThemes = \ThemeModel::findAll();

        if( $objThemes )
        {
            while( $objThemes->next() )
            {
                $arrVars = \StringUtil::deserialize($objThemes->vars, true);

                foreach($arrVars as $arrVar)
                {
                    if( preg_match('/color/', $arrVar['key']) )
                    {
                        $strValue = preg_replace('/color_/', '', $arrVar['key']);

                        $arrOptions[ $objThemes->name ][ $arrVar['value'] ] = $this->renderLangValue($strValue);
                    }
                }
            }
        }

        return $arrOptions;
    }



    protected function renderLangValue( $strText )
    {
        switch( $strText )
        {
            case "yellow":
                $strText = 'Gelb';
                break;

            case "pink":
                $strText = 'Pink / Rosa';
                break;

            case "darkblue":
                $strText = 'Dunkelblau';
                break;

            case "blue":
                $strText = 'Blau';
                break;

            case "green":
                $strText = 'Grün';
                break;

            case "red":
                $strText = 'Rot';
                break;
        }

        return $strText;
    }
}
