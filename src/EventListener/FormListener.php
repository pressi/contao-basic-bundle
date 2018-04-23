<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use IIDO\BasicBundle\Config\BundleConfig;


/**
 * Class Page Hook
 * @package IIDO\Customize\Hook
 */
class FormListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;


    protected $bundlePathPublic;
    protected $bundlePath;

    private $rootDir;



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework        = $framework;
        $this->bundlePathPublic = BundleConfig::getBundlePath(TRUE);
        $this->bundlePath       = BundleConfig::getBundlePath();
        $this->rootDir          = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
    }



    public function parseCustomizeWidget($strBuffer, $objClass)
    {
        if( \System::getContainer()->get('request_stack')->getCurrentRequest()->get("_scope") === ContaoCoreBundle::SCOPE_FRONTEND )
        {
            if( $objClass->type !== "submit" && $objClass->type !== "rocksolid_antispam" && $objClass->type !== "" && !preg_match('/rsas-field/', $strBuffer) && $objClass->type !== "radioTable")
            {
                $isSelect   = FALSE;
                $isRadio    = FALSE;
                $strBuffer  = preg_replace('/class="widget/', 'class="field widget', $strBuffer);

                if( preg_match('/select/', $strBuffer) )
                {
                    $isSelect  = TRUE;
                    $strBuffer = preg_replace_callback('/<div([A-Za-z0-9\s\-_="\{\}:]{0,})class="field ([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:]{0,})>/', 'self::replaceSelectDiv', $strBuffer, -1, $count);
                    $strBuffer = preg_replace_callback('/<select([A-Za-z0-9\s\-_="\{\}:]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:]{0,})>/', 'self::replaceSelect', $strBuffer, -1, $count);
                }

                if( preg_match('/type="radio"/', $strBuffer) )
                {
                    $isRadio = TRUE;
                }

                if( !preg_match('/checkbox/', $strBuffer) )
                {
                    $strBuffer = preg_replace_callback('/<label([A-Za-z0-9\s\-_="\{\}:]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:]{0,})>/', 'self::replaceLabel', $strBuffer, -1, $count);

                    if( !$isRadio )
                    {
                        $strBuffer = preg_replace('/<\/label>/', '</label><div class="control">' . ($isSelect ? '<div class="select">' : ''), $strBuffer, -1, $count);
                    }

                    if( $count )
                    {
                        $strBuffer = $strBuffer . '</div>' . ($isSelect ? '</div>' : '');
                    }

                    if( preg_match('/<input([A-Za-z0-9\s\-_="\{\}:,.;]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"/', $strBuffer, $arrInputMatches) )
                    {
                        if( !$isRadio )
                        {
                            $strBuffer = preg_replace('/<input([A-Za-z0-9\s\-_="\{\}:,.;]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:,.;]{0,})>/', '<input$1class="input $2"$3>', $strBuffer, -1, $count);
                        }
                        else
                        {
                            preg_match_all('/<span>(.*)<\/span>/', $strBuffer, $arrRadioMatches);

                            if( count($arrRadioMatches[0]) )
                            {
                                foreach($arrRadioMatches[1] as $strRadioField)
                                {
                                    $strNewRadioField = $strRadioField;

                                    if( preg_match('/<label([A-Za-z0-9\s\-_,;.:\/\(\)=\"\']{0,})>/', $strRadioField, $arrRadioInputMatches) )
                                    {
                                        $strNewRadioField = preg_replace('/<label([A-Za-z0-9\s\-_,;.:\/\(\)=\"\']{0,})>/', '', $strRadioField, -1, $count);

                                        if( $count )
                                        {
                                            $radioMatch = $arrRadioInputMatches[0];

                                            if( preg_match('/class="/', $radioMatch) )
                                            {
                                                $radioMatch = preg_replace('/class="/', 'class="radio ', $radioMatch);
                                            }
                                            else
                                            {
                                                $radioMatch = preg_replace('/<label/', '<label class="radio"', $radioMatch);
                                            }

                                            $strNewRadioField = $radioMatch . $strNewRadioField;
                                        }
                                    }

                                    $strBuffer = preg_replace('/' . preg_quote($strRadioField, '/') . '/', $strNewRadioField, $strBuffer);
                                }

                                $strBuffer = preg_replace('/<span><label/', '<div class="control"><label', $strBuffer, 1);
                                $strBuffer = preg_replace(array('/<span><label/', '/<\/label><\/span>/'), array('<label', '</label>'), $strBuffer, -1);
                                $strBuffer = preg_replace('/<\/fieldset>/', '</div></fieldset>', $strBuffer, 1);
                            }
                        }
                    }
                    elseif( preg_match('/<textarea/', $strBuffer) )
                    {
                        $strBuffer = preg_replace('/<textarea([A-Za-z0-9\s\-_="\{\}:,.;]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:,.;]{0,})>/', '<textarea$1class="textarea $2"$3>', $strBuffer, -1, $count);
                    }
                }
                else
                {
                    $strBuffer = preg_replace('/<input([A-Za-z0-9\s\-_="\{\}:]{0,})class="checkbox/', '<input$1class="checkbox is-checkbox', $strBuffer);
                }

                if( $objClass->mandatory )
                {
                    if( $objClass->placeholder )
                    {
                        $strBuffer = preg_replace('/placeholder="' . preg_quote($objClass->placeholder, '/') . '"/', 'placeholder="' . $objClass->placeholder . ' *"', $strBuffer);
                    }

                    if( preg_match('/select/', $strBuffer) )
                    {
                        $arrOptions = $objClass->options;

                        if( strlen($arrOptions[0]['value']) === 0 )
                        {
                            $strBuffer = preg_replace('/<option value="">' . preg_quote($arrOptions[0]['label'], '/') . '<\/option>/', '<option value="">' . $arrOptions[0]['label'] . ' *</option>', $strBuffer);
                        }
                    }
                }
            }
        }

        return $strBuffer;
    }



    public function getCustomizeForm($objRow, $strBuffer)
    {
//        echo "<pre>";
//        print_r( $objRow );
//        exit;
        return $strBuffer;
    }



    public function loadCustomizeFormField($objWidget, $strForm, $arrForm)
    {
        if( $objWidget->type === "submit" )
        {
            $objWidget->class = "btn-secondary";
        }
//        elseif( $objWidget->type === "select" )
//        {
//            if( preg_match('/selected-get/', $objWidget->class) )
//            {
//                $value      = \Input::get( $objWidget->name );
//                $arrOptions = $objWidget->options;
//
//                foreach( $arrOptions as $num => $option)
//                {
//                    if( $option['value'] === $value )
//                    {
////                        \Input::setPost($objWidget->name, $value);
//
//                        $arrOptions[ $num ]['selected'] = 'selected';
//                        break;
//                    }
//                }
//
//                $objWidget->options = $arrOptions;
//            }
//        }

//        else
//        {
//            $strClass = trim($objWidget->class . ' field');
//            switch( $objWidget->type )
//            {
//                case "text":
//                    $strClass = trim( $strClass . ' input');
//                    break;
//            }
//            $objWidget->class = $strClass;
//        }
        return $objWidget;
    }



    public function compileCustomizeFormFields( $arrFields, $formId, $objClass )
    {
        foreach($arrFields as $num => $arrField)
        {
            if( preg_match('/selected-get/', $arrField->class) )
            {
                $value      = \Input::get( $arrField->name );
                $arrOptions = \StringUtil::deserialize($arrField->options, TRUE);

                foreach( $arrOptions as $key => $option)
                {
                    if( $option['value'] === $value )
                    {
//                        \Input::setPost($arrField->name, $value);

                        $arrOptions[ $key ]['default']  = '1';
                        break;
                    }
                }

                $arrFields[ $num ]->options = serialize( $arrOptions );
            }
        }

        return $arrFields;
    }



    private function replaceLabel( $matches )
    {
        return $this->replaceTag('label', $matches, 'label' );
    }



    private function replaceSelect( $matches )
    {
        return $this->replaceTag('select', $matches );
    }



    private function replaceSelectDiv( $matches )
    {
        return $this->replaceTag('div', $matches, 'field' );
    }



    protected function replaceTag( $tagName, $matches, $firstClass = '' )
    {
        return preg_replace('/ class=""/', '', '<' . $tagName . $matches[ 1 ] . 'class="' . trim($firstClass . ' ' . $this->replaceClass($matches[2])) . '"' . $matches[ 3 ] . '>');
    }



    protected function replaceClass( $className )
    {
        $className = trim(preg_replace('/select/', '', $className, -1, $count));
        $className = preg_replace('/  /', ' ', $className);

        if( $count )
        {
            $className = preg_replace('/widget-/', 'widget-select', $className);
        }

        return $className;
    }

}
