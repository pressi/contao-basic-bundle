<?php
/******************************************************************
 *
 * (c) 2015 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

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
            if( $objClass->type !== "submit" && $objClass->type !== "rocksolid_antispam" && $objClass->type !== "" && !preg_match('/rsas-field/', $strBuffer) )
            {
                $isSelect  = FALSE;
                $strBuffer = preg_replace('/class="widget/', 'class="field widget', $strBuffer);

                if( preg_match('/select/', $strBuffer) )
                {
                    $isSelect  = TRUE;
                    $strBuffer = preg_replace_callback('/<div([A-Za-z0-9\s\-_="\{\}:]{0,})class="field ([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:]{0,})>/', 'self::replaceSelectDiv', $strBuffer, -1, $count);
                    $strBuffer = preg_replace_callback('/<select([A-Za-z0-9\s\-_="\{\}:]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:]{0,})>/', 'self::replaceSelect', $strBuffer, -1, $count);
                }

                if( !preg_match('/checkbox/', $strBuffer) )
                {
                    $strBuffer = preg_replace_callback('/<label([A-Za-z0-9\s\-_="\{\}:]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:]{0,})>/', 'self::replaceLabel', $strBuffer, -1, $count);
                    $strBuffer = preg_replace('/<\/label>/', '</label><div class="control">' . ($isSelect ? '<div class="select">' : ''), $strBuffer, -1, $count);

                    if( $count )
                    {
                        $strBuffer = $strBuffer . '</div>' . ($isSelect ? '</div>' : '');
                    }

                    if( preg_match('/<input/', $strBuffer) )
                    {
                        $strBuffer = preg_replace('/<input([A-Za-z0-9\s\-_="\{\}:,.;]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"([A-Za-z0-9\s\-_="\{\}:,.;]{0,})>/', '<input$1class="input $2"$3>', $strBuffer, -1, $count);
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
