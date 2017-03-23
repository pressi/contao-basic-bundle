<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


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
        $languages = array_intersect_key($languages, array_flip($objRootLangs->fetchEach('language')));

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

                $return .= '<span class="lang">' . (isset($languages[$lang]) ? $languages[$lang] : $lang) . ' ' . \Image::getHtml('delete.svg', '', 'class="tl_metawizard_img" onclick="Backend.metaDelete(this)"') . '</span>';

                // Take the fields from the DCA (see #4327)
                foreach ($this->metaFields as $field=>$attributes)
                {
                    if( $attributes == "textarea" )
                    {
                        $strField = '<textarea name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $field . '_' . $count . '" class="tl_textarea tl_text">' . \StringUtil::specialchars($meta[$field]) . '</textarea>';
                    }
                    else
                    {
                        $strField = '<input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $field . '_' . $count . '" class="tl_text" value="' . \StringUtil::specialchars($meta[$field]) . '"' . (!empty($attributes) ? ' ' . $attributes : '') . '>';
                    }

                    $return .= '<label for="ctrl_' . $field . '_' . $count . '">' . $GLOBALS['TL_LANG']['MSC']['aw_' . $field] . '</label> ' . $strField . '<br>';
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
}
