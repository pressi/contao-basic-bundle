<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


use Contao\Backend;
use Contao\BackendUser;
use Contao\Database;
use Contao\Image;
use Contao\MetaWizard;
use Contao\StringUtil;


/**
 * Class MetaWizardWidget
 *
 * @package IIDO\BasicBundle\Widget
 */
class MetaWizardWidget extends MetaWizard
{

	/**
	 * Copy of MetaWizard generate() function
	 *
	 * @return string
	 */
	public function generate()
	{
		$count = 0;
		$return = '';

		$this->import(Database::class, 'Database');
		$this->import(BackendUser::class, 'User');

		// Only show the root page languages (see #7112, #7667)
		$objRootLangs = $this->Database->query("SELECT REPLACE(language, '-', '_') AS language FROM tl_page WHERE type='root'");
		$existing = $objRootLangs->fetchEach('language');

		foreach ($existing as $lang)
		{
			if (!isset($this->varValue[$lang]))
			{
				$this->varValue[$lang] = array();
			}
		}

		// No languages defined in the site structure
		if (empty($this->varValue) || !\is_array($this->varValue))
		{
			return '<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['metaNoLanguages'] . '</p>';
		}

		$languages = $this->getLanguages(true);

		// Add the existing entries
		if (!empty($this->varValue))
		{
			$return = '<ul id="ctrl_' . $this->strId . '" class="tl_metawizard dcapicker">';

			// Add the input fields
			foreach ($this->varValue as $lang=>$meta)
			{
				$return .= '
    <li class="' . (($count % 2 == 0) ? 'even' : 'odd') . '" data-language="' . $lang . '"><span class="lang">' . ($languages[$lang] ?? $lang) . ' ' . Image::getHtml('delete.svg', '', 'class="tl_metawizard_img" title="' . $GLOBALS['TL_LANG']['MSC']['delete'] . '" onclick="Backend.metaDelete(this)"') . '</span>';

				// Take the fields from the DCA (see #4327)
				foreach ($this->metaFields as $field=>$fieldConfig)
				{
				    $inputField = '<input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $this->strId . '_' . $field . '_' . $count . '" class="tl_text" value="' . StringUtil::specialchars($meta[$field]) . '"' . (!empty($fieldConfig['attributes']) ? ' ' . $fieldConfig['attributes'] : '') . '>';

				    if( $fieldConfig['inputType'] && $fieldConfig['inputType'] === 'fileTree' )
                    {
                        $arrData        = $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'][ $field ];
                        $strInputName   = $field . '_' . $lang;
                        $varValue       = $meta[ $field ];
//                        $arrValue       = \StringUtil::deserialize($varValue, true);

                        $strClass       = $GLOBALS['BE_FFL']['fileTree'];

                        $GLOBALS['TL_DCA']['tl_files' ]['fields'][ $strInputName ] = $arrData;

                        $objWidget      = new $strClass( $strClass::getAttributesFromDca($arrData, $strInputName, $varValue, $strInputName, 'tl_files', $this) );

                        $objWidget->id              = $field . '_' . $lang;

                        $objWidget->filesOnly       = TRUE;
                        $objWidget->fieldType       = 'radio';
                        $objWidget->extensions      = \Config::get('validImageTypes');

//                        $objWidget->class           = 'w50 wizard';

                        $objWidget->isMetaField     = TRUE;
                        $objWidget->metaPrefix      = $this->strId;
                        $objWidget->metaLang        = $lang;
                        $objWidget->metaField       = $field;

                        $strField   = $objWidget->generate();
                        $strField   = preg_replace('/<h3><\/h3>/', '', $strField);
                        $strField   = preg_replace('/<div>/', '<div style="float:left;">', $strField, 1);

                        $inputField = $strField;
                    }

					$return .= '<label for="ctrl_' . $this->strId . '_' . $field . '_' . $count . '">' . $GLOBALS['TL_LANG']['MSC']['aw_' . $field] . '</label>' . $inputField;

					// DCA picker
					if (isset($fieldConfig['dcaPicker']) && (\is_array($fieldConfig['dcaPicker']) || $fieldConfig['dcaPicker'] === true))
					{
						$return .= Backend::getDcaPickerWizard($fieldConfig['dcaPicker'], $this->strTable, $this->strField, $this->strId . '_' . $field . '_' . $count);
					}

					$return .= '<br>';
				}

				$return .= '
    </li>';

				++$count;
			}

			$return .= '
  </ul>';
		}

		$options = array('<option value="">-</option>');

		// Add the remaining languages
		foreach ($languages as $k=>$v)
		{
			$options[] = '<option value="' . $k . '"' . (isset($this->varValue[$k]) ? ' disabled' : '') . '>' . $v . '</option>';
		}

		$return .= '
  <div class="tl_metawizard_new">
    <select name="' . $this->strId . '[language]" class="tl_select" onchange="Backend.toggleAddLanguageButton(this)">' . implode('', $options) . '</select> <input type="button" class="tl_submit" disabled value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['aw_new']) . '" onclick="Backend.metaWizard(this, \'ctrl_' . $this->strId . '\')">
  </div>';

		return $return;
	}

}
