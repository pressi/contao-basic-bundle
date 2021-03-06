<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


use Contao\ArticleModel;
use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\PageHelper;


/**
 * Provide methods to handle text fields.
 *
 * @property integer $maxlength
 * @property boolean $mandatory
 * @property string  $placeholder
 * @property boolean $multiple
 * @property boolean $hideInput
 * @property integer $size
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class TextFieldWidget extends \TextField
{

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $strField = parent::generate();

        if( $this->colorpicker )
        {
            $elementId          = Input::get('id');
            $tableModelClass    = Model::getClassFromTable( $this->strTable );
            $objModel           = $tableModelClass::findByPk( $elementId );
            /* @var $objModel \Contao\ArticleModel */

            $arrExists = [];
            if( $this->strTable === 'tl_article' )
            {
                $objCurrentPage     = $objModel->getRelated('pid')->loadDetails();
                $objFirstPages      = PageModel::findByPid( $objCurrentPage->rootId );
                $fieldName          = $this->strField;

                if( $objFirstPages )
                {
                    while( $objFirstPages->next() )
                    {
                        $objArticles = ArticleModel::findByPid( $objFirstPages->id );

                        if( $objArticles )
                        {
                            while( $objArticles->next() )
                            {
                                if( $objArticles->id !== $elementId )
                                {
                                    if( $objArticles->$fieldName )
                                    {
                                    }
                                }
                            }
                        }
                    }
                }
            }


//            echo "<pre>"; print_r( $objModel ); exit;
//            echo "<pre>"; print_r( $this->strField ); exit;
        }
        if( $this->addon )
        {
            $strField = preg_replace('/class="tl_text/', 'class="tl_text has-addon', $strField);
            $strField = $strField . '<div class="addon">' . $this->addon . '</div>';
        }

//        if( $this->colorpicker && !preg_match('/fillColor/', $this->strField) )
//        {
//            if( !preg_match('/<img/', $strField) )
//            {
//                $strColorKey = $this->multiple ? $this->strField . '_0' : $this->strField;
//
//                $strField .= ' ' . \Image::getHtml('pickcolor.svg', $GLOBALS['TL_LANG']['MSC']['colorpicker'], 'title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['colorpicker']).'" id="moo_' . $this->strField . '" style="cursor:pointer"') . '
//  <script>
//    window.addEvent("domready", function() {
//      var cl = $("ctrl_' . $strColorKey . '").value.hexToRgb(true) || [255, 0, 0];
//      new MooRainbow("moo_' . $this->strField . '", {
//        id: "ctrl_' . $strColorKey . '",
//        startColor: cl,
//        imgPath: "assets/colorpicker/images/",
//        onComplete: function(color) {
//          $("ctrl_' . $strColorKey . '").value = color.hex.replace("#", "");
//        }});
//    });
//  </script>';
//            }
//
//            $arrValue       = \StringUtil::deserialize($this->value, true);
//            $bgColor        = ColorHelper::compileColor( $this->value );
//            $colorPreview   = '<div class="color-preview" id="colorPreview_' . $this->strField . '" style="background:' . $bgColor . '"></div>';
//
//            $strField       =  $colorPreview . preg_replace('/class="tl_text_field/', 'class="tl_text_field color-picker', $strField);
//            $strField       = preg_replace('/\$\("ctrl_' . $this->strField . '_0"\).value = color.hex.replace\("#", ""\);/', '$("ctrl_' . $this->strField . '_0").value = color.hex.replace("#", "");var strColor = color.hex, strField = $("ctrl_' . $this->strField . '_1"); if(strField.value){strColor=\'rgba(\' + color.rgb[0] + \',\' + color.rgb[1] + \',\' + color.rgb[2] + \',\' + (strField.value/100) + \')\'}$("colorPreview_' . $this->strField . '").setStyle("background", strColor);', $strField);
//
//            $strFunction    = ' var fn = function( el, mode )
//            {
//                var strField, strColor, varValue = el.value;
//
//                if( mode === "color" )
//                {
//                    strField = $("ctrl_' . $this->strField . '_1");
//                    varValue = strField.value.toInt();
//
//                    strColor = el.value;
//
//                    if( strColor.length < 3 || (strColor.length > 3 && strColor.length < 6) )
//                    {
//                        strColor = false;
//                    }
//
//                    if( !strColor )
//                    {
//                        strField = $("ctrl_' . $this->strField . '_2");
//                        strColor = strField.value.replace(/#/, "");
//                    }
//                }
//                else if( mode === "trans" )
//                {
//                    strField = $("ctrl_' . $this->strField . '_0");
//                    strColor = strField.value;
//
//                    varValue = varValue.toInt();
//
//                    if( strColor.length < 3 || (strColor.length > 3 && strColor.length < 6) )
//                    {
//                        strColor = false;
//                    }
//
//                    if( !strColor )
//                    {
//                        strField = $("ctrl_' . $this->strField . '_2");
//                        strColor = strField.value.replace(/#/, "");
//                    }
//                }
//                else
//                {
//                    strField = $("ctrl_' . $this->strField . '_1");
//                    varValue = strField.value.toInt();
//
//                    strCSField = $("ctrl_' . $this->strField . '_2");
//                    strColor = strCSField.value.replace(/#/, "");
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
//                $("colorPreview_' . $this->strField . '").setStyle("background", strColor);
//            };
//
//            $("ctrl_' . $this->strField . '_1").addEvents({
//    "change": function() { fn(this, "trans"); },
//    "keyup": function() { fn(this, "trans"); }
//});
//            $("ctrl_' . $this->strField . '_0").addEvents({
//    "change": function() { fn(this, "color"); },
//    "keyup": function() { fn(this, "color"); }
//});
//
//            $("ctrl_' . $this->strField . '_2").addEvents({
//    "change": function() { fn(this, "select"); }
//});
//                </script>';
//            $strField       = preg_replace('/<\/script>/', $strFunction, $strField);
//
//            $strSelectField     = '';
//            $arrSelectOptions   = ColorHelper::getThemeColors();
//
//            if( !count($arrSelectOptions) )
//            {
//                $arrSelectOptions = array('-');
//            }
//
//            if( count($arrSelectOptions) )
//            {
//                $strSelectField = '<select name="' . $this->strField . '[]" id="ctrl_' . $this->strField . '_2" class="tl_select color-select">';
//
//                if( $arrSelectOptions[0] !== "-" )
//                {
//                    $strSelectField .= '<option value="">-</option>';
//                }
//
//                foreach($arrSelectOptions as $key => $value)
//                {
//                    if( is_array($value) )
//                    {
//                        $strSelectField .= '<optgroup label="' . $key . '">';
//
//                        foreach($value as $strKey => $strValue)
//                        {
//                            $selected = '';
//
//                            if( preg_replace(array('/&#35;/', '/#/'), '', $arrValue[2]) === preg_replace(array('/&#35;/', '/#/'), '', $strKey) )
//                            {
//                                $selected = ' selected';
//                            }
//
//                            $strSelectField .= '<option value="' . $strKey . '"' . $selected . '>' . $strValue . '</option>';
//                        }
//
//                        $strSelectField .= '</optgroup>';
//                    }
//                    else
//                    {
//                        $selected = '';
//
//                        if( preg_replace(array('/&#35;/', '/#/'), '', $arrValue[2]) === preg_replace(array('/&#35;/', '/#/'), '', $key) )
//                        {
//                            $selected = ' selected';
//                        }
//
//                        $strSelectField .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
//                    }
//                }
//
//                $strSelectField .= '</select>';
//            }
//
//
////            if( !preg_match('/<img/', $strField) )
////            {
////                // Support single fields as well (see #5240)
////                $strKey = $this->multiple ? $this->strField . '_0' : $this->strField;
////
////                $wizard = ' ' . \Image::getHtml('pickcolor.svg', $GLOBALS['TL_LANG']['MSC']['colorpicker'], 'title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['colorpicker']).'" id="moo_' . $this->strField . '" style="cursor:pointer"');
////
////                $strField = $strField . $wizard;
////            }
//
//            $strField = preg_replace('/<img([A-Za-zöäüÖÄÜ0-9\s\-=".:,;\[\]_\/]{0,})>/', $strSelectField . '<img$1>', $strField);
//            $strField = preg_replace('/maxlength="64"/', 'maxlength="6"', $strField, 1);
//            $strField = preg_replace('/maxlength="64"/', 'maxlength="3"', $strField, 1);
//
//            if( $this->isMetaField )
//            {
//                $strNewName = $this->metaPrefix . '[' . $this->metaLang . '][' . $this->metaField . ']';
//                $strField   = preg_replace('/name="' . $this->strField . '\[\]"/', 'name="' . $strNewName . '"', $strField);
//            }
//        }

        return $strField;
    }
}
