<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace IIDO\BasicBundle\Widget;

use IIDO\BasicBundle\Helper\ColorHelper;


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
 * @author Leo Feyer <https://github.com/leofeyer>
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
            $bgColor        = ColorHelper::compileColor( $this->value );
            $colorPreview   = '<div class="color-preview" id="colorPreview_' . $this->strField . '" style="background:' . $bgColor . '"></div>';
            $strField       =  $colorPreview . preg_replace('/class="tl_text_field"/', 'class="tl_text_field color-picker"', $strField);

            $strField       = preg_replace('/\$\("ctrl_' . $this->strField . '_0"\).value = color.hex.replace\("#", ""\);/', '$("ctrl_' . $this->strField . '_0").value = color.hex.replace("#", "");var strColor = color.hex, strField = $("ctrl_' . $this->strField . '_1"); if(strField.value){strColor=\'rgba(\' + color.rgb[0] + \',\' + color.rgb[1] + \',\' + color.rgb[2] + \',\' + (strField.value/100) + \')\'}$("colorPreview_' . $this->strField . '").setStyle("background", strColor);', $strField);

            $strFunction    = ' var fn = function( el, mode )
            {
                var strField, strColor, varValue = el.value;
                
                if( mode == "color" )
                {
                    strField = $("ctrl_' . $this->strField . '_1");
                    varValue = strField.value.toInt();
                    
                    strColor = el.value;
                    
                    if( strColor.length < 3 || (strColor.length > 3 && strColor.length < 6) )
                    {
                        strColor = false;
                    }
                }
                else
                {
                    strField = $("ctrl_' . $this->strField . '_0");
                    strColor = strField.value;
                    
                    varValue = varValue.toInt();                
                }
                     
                if(varValue > 0 && strColor) 
                {
                    var arrColor = strColor.hexToRgb(true);
                    
                    if( varValue > 100 )
                    {
                        varValue = 100;
                    }
                   
                    strColor = \'rgba(\' + arrColor[0] + \',\' + arrColor[1] + \',\' + arrColor[2] + \',\' + (varValue/100) + \')\';
                }
                else
                {
                    if( strColor )
                    { 
                        strColor = \'#\' + strColor;
                    }
                    else
                    {
                        strColor = \'transparent\';
                    }
                }
                
                $("colorPreview_' . $this->strField . '").setStyle("background", strColor);
            };
            
            $("ctrl_' . $this->strField . '_1").addEvents({
    "change": function() { fn(this, "trans"); },
    "keyup": function() { fn(this, "trans"); }
});
            $("ctrl_' . $this->strField . '_0").addEvents({
    "change": function() { fn(this, "color"); },
    "keyup": function() { fn(this, "color"); }
});
                </script>';
            $strField       = preg_replace('/<\/script>/', $strFunction, $strField);
        }

        return $strField;
    }
}
