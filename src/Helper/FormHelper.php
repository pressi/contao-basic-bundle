<?php


namespace IIDO\BasicBundle\Helper;


use Contao\Controller;
use PRESTEP\PowerPoolBundle\Helper\SettingsHelper;


class FormHelper
{

    public static function renderTableFormFields($strTable, array $arrFormFields, $arrValue = array(), $strFormSubmit = '', $prefix = '', $activeFieldName = '' )
    {
        $strFields  = '';
        $arrFields  = array();
        $row        = 1;
        $class      = new self();

        Controller::loadLanguageFile( $strTable );
        Controller::loadDataContainer( $strTable );

        foreach ($arrFormFields as $row => $field)
        {
            $usePrefix = false;

            if( strlen($prefix) && isset($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $prefix . $field ]) )
            {
                $field      = $prefix . $field;
                $usePrefix  = true;
            }

            $arrData = &$GLOBALS['TL_DCA'][ $strTable ]['fields'][ $field ];


            if( 0 === strpos($field, 'accept_') )
            {
                $fieldPrefix    = SettingsHelper::getFieldPrefix( $field );
                $objSettings    = SettingsHelper::getSettingsObject();
                $fieldName      = preg_replace('/^accept_/', 'add_', $field);

                if( $objSettings->$fieldName )
                {
                    $labelKey   = $fieldPrefix . 'label';
                    $textKey    = $fieldPrefix . 'text';

                    $arrData['label'][0] = ContentHelper::renderText( $objSettings->$labelKey?:$objSettings->$textKey );
                }
            }


            // Map checkboxWizards to regular checkbox widgets
            if ($arrData['inputType'] === 'checkboxWizard')
            {
                $arrData['inputType'] = 'checkbox';
            }

            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class does not exist
            if (!$arrData['eval']['feEditable'] || !class_exists($strClass))
            {
                continue;
            }

            $strGroup = $arrData['eval']['feGroup'];

            $arrData['eval']['required']    = false;
            $arrData['eval']['tableless']   = true;

            if( strlen($activeFieldName) )
            {
                if( $arrData['eval']['mandatory'] )
                {
                    $arrData['eval']['feClass']     = trim($arrData['eval']['feClass'] . " is-required");
                }

                $arrData['eval']['mandatory']       = FALSE;
            }

            $placeholder = $arrData['label'][0];

            $arrData['eval']['placeholder'] = $placeholder . (( $arrData['eval']['mandatory'] ) ? ' *' : '');

            $varValue = "";

            if( (!$usePrefix && !strlen($prefix)) || $usePrefix )
            {
                $varValue = $arrValue[ $field ];
            }

            if( strlen($prefix) && !$usePrefix )
            {
                $field = $prefix . $field;
            }

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $varValue, '', '', $class));

            $objWidget->storeValues     = true;
            $objWidget->rowClass        = 'row_' . $row . (($row == 0) ? ' row_first' : '') . ((($row % 2) == 0) ? ' even' : ' odd');

            // Increase the row count if it is a password field
            if ($objWidget instanceof \FormPassword)
            {
                ++$row;
                $objWidget->rowClassConfirm = 'row_' . $row . ((($row % 2) == 0) ? ' even' : ' odd');
            }

            // Validate the form data
            if ( $strFormSubmit && \Input::post('FORM_SUBMIT') == $strFormSubmit)
            {
                $objWidget->validate();
                $varValue = $objWidget->value;

                $rgxp = $arrData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '')
                {
                    try
                    {
                        $objDate = new \Date($varValue);
                        $varValue = $objDate->tstamp;
                    }
                    catch (\OutOfBoundsException $e)
                    {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Do not submit the field if there are errors
                if ($objWidget->hasErrors())
                {
                    $doNotSubmit = true;
                }
                elseif ($objWidget->submitInput())
                {
                    // Store the form data
                    $_SESSION['FORM_DATA'][$field] = $varValue;

                    // Set the correct empty value (see #6284, #6373)
                    if ($varValue === '')
                    {
                        $varValue = $objWidget->getEmptyValue();
                    }

                    // Set the new value
//                    $objUser->$field = $varValue;

                    // Set the new field in the member model
//                    $blnModified = true;
//                    $objMember->$field = $varValue;
                }
            }

            if ($objWidget instanceof \uploadable)
            {
                $hasUpload = true;
            }

            $temp = $objWidget->parse();

            if( $arrData['eval']['feInfo'] )
            {
                $strAttr = '';
                $arrData['eval']['feInfo'] = preg_replace('/"/', "'", $arrData['eval']['feInfo']);

                if( $arrData['eval']['feInfoClick'] )
                {
                    $strAttr = ' data-click="' . $arrData['eval']['feInfoClick'] . '"';
                }

                $strInfoIcon = '<span class="info-icon" id="fieldInfo_' . $field . '" data-class="user-text" data-pos="auto" data-text="' . $arrData['eval']['feInfo'] . '"' . $strAttr . '><i class="mdi mdi-information-outline"></i></span>';

                $temp = preg_replace('/<\/label>/', $strInfoIcon . '</label>', $temp);
            }

            if( $arrData['eval']['addonText'] )
            {
                $temp = preg_replace('/<input([A-Za-z0-9\s\-="öäüÖÄÜß,;.:*_]+)>/', '<div class="input-group"><input$1><div class="input-group-append"><span class="input-group-text">' . $arrData['eval']['addonText'] . '</span></div></div>', $temp);
            }

            if( $arrData['eval']['feClass'] )
            {
                $temp = preg_replace('/class="widget/', 'class="widget ' . $arrData['eval']['feClass'], $temp);
            }

            if( $arrData['eval']['feInputClass'] )
            {
                $temp = preg_replace('/<input([A-Za-z0-9\s\-,;.:_="]+)class="/', '<input$1class=" ' . $arrData['eval']['feInputClass'] . ' ', $temp);
            }

            if( $arrData['inputType'] !== 'checkbox' )
            {
                preg_match_all('/<input([A-Za-z0-9\s\-="öäüÖÄÜß,;.:*_@âÂôÔáÁàÀóÓòÒúÚÙùß\/]+)>/', $temp, $arrMatches);

                $formField = $arrMatches[0][0];

                if( preg_match('/class="/', $formField) )
                {

                    $formField = preg_replace('/class="/', 'class="form-control ', $formField);
                }
                else
                {
                    $formField = preg_replace('/' . preg_quote($arrMatches[1][0], '/') . '/', trim($arrMatches[1][0]) . ' class="form-control"', $formField);
                }

                if( $arrData['eval']['feMask'] )
                {
                    $attr = '';

                    if( isset($arrData['eval']['feMaskReverse']) && $arrData['eval']['feMaskReverse'] === TRUE )
                    {
                        $attr = ' data-masek-reverse="true"';
                    }

                    $formField = preg_replace('/' . preg_quote($arrMatches[1][0], '/') . '/', trim($arrMatches[1][0]) . ' data-mask="' . $arrData['eval']['feMask'] . '"' . $attr, $formField);
                }

                $temp = preg_replace('/' . preg_quote($arrMatches[0][0], '/') . '/', $formField, $temp);
            }
            else
            {
                if( $arrData['eval']['feLegend'] )
                {
                    $temp = preg_replace('/<legend>([\s\n]{0,})' . preg_quote($arrData['label'][0], '/') . '([\s\n]{0,})<\/legend>/', '<legend>' . $arrData['eval']['feLegend'] . '</legend>', $temp);
                }

                if( $arrData['eval']['feLabel'] )
                {
                    $temp = preg_replace('/<label([A-Za-z0-9\s,;.:_="]{0,})>([\s\n]{0,})' . preg_quote($arrData['label'][0], '/') . '([\s\n]{0,})<\/label>/', '<label$1>' . $arrData['eval']['feLabel'] . '</label>', $temp);
                }

                $replaceOnClick = $arrData['eval']['feOnClick'];

                if( $replaceOnClick )
                {
                    $replaceOnClick = ' onclick="' . $replaceOnClick . '"';
                }

                if( preg_match('/onclick="/', $temp) )
                {
                    $temp = preg_replace('/ onclick="Backend.autoSubmit\(\'\'\)"/', $replaceOnClick, $temp);
                }
//                else
//                {
//                    $temp = preg_replace('')
//                }
            }

            if( preg_match('/<a/', $temp) )
            {
                if( preg_match('/target/', $temp) )
                {
                    $temp = preg_replace('/target="([A-Za-z_])"/', 'target="_blank"', $temp);
                }
                else
                {
                    $temp = preg_replace('/<a/', '<a target="_blank"', $temp);
                }
            }

            if( $arrData['eval']['mandatory'] )
            {
                $temp = preg_replace('/class="form-control/', 'class="form-control is-required', $temp);

                if( FALSE !== strpos($field, 'cn_') && $arrValue['changename'] !== '1' )
                {
                    $temp = preg_replace('/ required/', '', $temp);
                }
            }

            if( $field === 'iban' )
            {
                $icon = '<span class="info-icon" id="fieldInfo_iban" data-class="user-text" data-pos="auto" data-text="Diesen benötigen wir für die Auszahlung Ihres Poolbonus.<br>Eine Übermittlung per E-Mail, per WhatsApp oder per Fax ist auch möglich.<br>E-Mail: <strong>office@bestpreisagrar.info</strong><span>(Klick auf Icon für Email senden)</span><br><strong>Fax: 0662 / 23 10 48 - 20</strong><br><strong>WhatsApp: 0660 / 68 89 176</strong>" data-click="mailto:office@bestpreisagrar.info"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 330 330" style="enable-background:new 0 0 330 330;" xml:space="preserve">
<g>
<path d="M165,0C74.019,0,0,74.02,0,165.001C0,255.982,74.019,330,165,330s165-74.018,165-164.999C330,74.02,255.981,0,165,0z
M165,300c-74.44,0-135-60.56-135-134.999C30,90.562,90.56,30,165,30s135,60.562,135,135.001C300,239.44,239.439,300,165,300z"></path>
<path d="M164.998,70c-11.026,0-19.996,8.976-19.996,20.009c0,11.023,8.97,19.991,19.996,19.991
c11.026,0,19.996-8.968,19.996-19.991C184.994,78.976,176.024,70,164.998,70z"></path>
<path d="M165,140c-8.284,0-15,6.716-15,15v90c0,8.284,6.716,15,15,15c8.284,0,15-6.716,15-15v-90C180,146.716,173.284,140,165,140z"></path>
</g>
</svg></span>';

                $temp = preg_replace('/<\/label>/', $icon . '</label>', $temp);
            }

            $strFields .= $temp;
            $arrFields[ $strGroup ][ $field ] .= $temp;
            ++$row;
        }

        return $strFields;
    }
}