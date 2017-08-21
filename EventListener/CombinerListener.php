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

namespace IIDO\BasicBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use \MatthiasMullie\Minify;


/**
 * IIDO System Listener
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class CombinerListener
{
    use ScopeAwareTrait;


    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;


    protected $bundlePathPublic;
    protected $bundlePath;

    protected $resourcePath     = '/app/Resources';



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;

        $this->bundlePathPublic = BundleConfig::getBundlePath(true);
        $this->bundlePath       = BundleConfig::getBundlePath();

    }



    /**
     * get a combined file
     *
     * @var string $strContent
     * @var string $strKey
     * @var string $strMode
     *
     * @return string
     */
    public function getCustomizeCombinedFile($strContent, $strKey, $strMode, &$arrFile)
    {
        global $objPage;

        $objLayout  = BasicHelper::getPageLayout( $objPage );
        $objTheme   = \ThemeModel::findByPk( $objLayout->pid );

        $isInFiles  = preg_match('/^files/', $arrFile['name']);
        $isInBundle = preg_match('/^' . preg_quote($this->bundlePathPublic, '/') . '/', $arrFile['name']);

        if( $strMode === \Combiner::CSS )
        {
            if( $isInFiles || $isInBundle )
            {
                $themeVars = deserialize($objTheme->vars, TRUE);

                $themeVars = array_merge($themeVars, $this->getDefaultsCSS());

                if( count($themeVars) )
                {
                    foreach($themeVars as $arrValue)
                    {
                        $varName    = $arrValue['key'];
                        $varValue   = $arrValue['value'];
//                        $objCurPage = $arrValue['page'];
                        $add        = '';

                        if( !preg_match('/shrink/', $varName) )
                        {
                            $add = '1px';
                        }

                        if( preg_match('/color/', $varName) )
                        {
                            $add = '#fff';

//                            if( preg_match('/^page_color/', $varName) )
//                            {
//                                $varName .= '_' . $objCurPage->alias;
//                            }
                            $varValue       = preg_replace(array('/&#35;/', '/&#40;/', '/&#41;/'), array('#', '(', ')'), $varValue);

                            $strContent     = $this->replaceColorVariants($varName, $varValue, $strContent, $add);
                        }

                        $strContent = preg_replace('/\/\*#' . $varName . '#\*\/' . $add . '/', $varValue, $strContent);
                    }
                }

                $strContent = $this->replaceDefaultVars( $strContent );

                $objMinify = new Minify\CSS();
                $objMinify->add( $strContent );
                $strContent = $objMinify->minify();
            }
        }
        elseif( $strMode === \Combiner::JS )
        {
            if( $isInFiles || $isInBundle )
            {
                if( !preg_match('/min' . \Combiner::JS . '$/', trim($arrFile['name'])) )
                {
                    $objMinify = new Minify\JS();
                    $objMinify->add( $strContent );
                    $strContent = $objMinify->minify();
                }
            }
        }

        return $strContent;
    }



    protected function getDefaultsCSS()
    {
        global $objPage;

//        $objRootPage    = \PageModel::findByPk( $objPage->rootId );
        $arrVariables   = array();
        $pageColor      = ColorHelper::getPageColor( NULL );

        $arrVariables[] = array
        (
            'key'   => 'page_color',
            'value' => $pageColor,
//            'page'  => $objColorPage
        );

        $objPages = \PageModel::findPublishedByPid( $objPage->rootId );

        while( $objPages->next() )
        {
            $pageColor = ColorHelper::getCurrentPageColor( $objPages );

            if( $pageColor != "transparent" )
            {
                $arrVariables[] = array
                (
                    'key'   => 'page_color_' . $objPages->alias,
                    'value' => $pageColor
                );

                $rgb = ColorHelper::convertHexColor($pageColor);

                if( count($rgb) )
                {
                    $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_' . $objPages->alias . '_trans50',
                        'value' => $rgba . ' 0.5)'
                    );

                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_' . $objPages->alias . '_trans60',
                        'value' => $rgba . ' 0.6)'
                    );

                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_' . $objPages->alias . '_trans70',
                        'value' => $rgba . ' 0.7)'
                    );

                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_' . $objPages->alias . '_trans80',
                        'value' => $rgba . ' 0.8)'
                    );

                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_' . $objPages->alias . '_trans90',
                        'value' => $rgba . ' 0.9)'
                    );
                }
            }

            $objSubPages = \PageModel::findPublishedByPid( $objPages->id );

            if( $objSubPages && $objSubPages->count() )
            {
                while( $objSubPages->next() )
                {
                    $pageColor = ColorHelper::getCurrentPageColor( $objSubPages->current() );

                    if( $pageColor != "transparent" )
                    {
                        $arrVariables[] = array
                        (
                            'key'   => 'page_color_' . $objSubPages->alias,
                            'value' => $pageColor
                        );

                        $rgb = ColorHelper::convertHexColor($pageColor);

                        if( count($rgb) )
                        {
                            $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objSubPages->alias . '_trans50',
                                'value' => $rgba . ' 0.5)'
                            );

                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objSubPages->alias . '_trans60',
                                'value' => $rgba . ' 0.6)'
                            );

                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objSubPages->alias . '_trans70',
                                'value' => $rgba . ' 0.7)'
                            );

                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objSubPages->alias . '_trans80',
                                'value' => $rgba . ' 0.8)'
                            );

                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objSubPages->alias . '_trans90',
                                'value' => $rgba . ' 0.9)'
                            );
                        }
                    }

                    $objSubSubPages = \PageModel::findPublishedByPid( $objSubPages->id );

                    if( $objSubSubPages && $objSubSubPages->count() )
                    {
                        while( $objSubSubPages->next() )
                        {
                            $pageColor = ColorHelper::getCurrentPageColor( $objSubSubPages->current() );

                            if( $pageColor != "transparent" )
                            {
                                $arrVariables[] = array
                                (
                                    'key'   => 'page_color_' . $objSubSubPages->alias,
                                    'value' => $pageColor
                                );

                                $rgb = ColorHelper::convertHexColor($pageColor);

                                if( count($rgb) )
                                {
                                    $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubSubPages->alias . '_trans50',
                                        'value' => $rgba . ' 0.5)'
                                    );

                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubSubPages->alias . '_trans60',
                                        'value' => $rgba . ' 0.6)'
                                    );

                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubSubPages->alias . '_trans70',
                                        'value' => $rgba . ' 0.7)'
                                    );

                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubSubPages->alias . '_trans80',
                                        'value' => $rgba . ' 0.8)'
                                    );

                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubSubPages->alias . '_trans90',
                                        'value' => $rgba . ' 0.9)'
                                    );
                                }
                            }

                        }
                    }

                }
            }

        }

        return $arrVariables;
    }



    protected function replaceDefaultVars( $strContent )
    {
        preg_match_all('/\/\*#[^*]+#\*\//', $strContent, $arrChunks);

        foreach ($arrChunks[0] as $strChunk)
        {
            $strKey = strtolower(substr($strChunk, 3, -3));

            switch( $strKey )
            {
                case "page_content_width":
                    $strContent    = str_replace($strChunk . '1px', '100%', $strContent);
                    break;
            }
        }

        return $strContent;
    }



    protected function replaceColorVariants( $varName, $varValue, $strContent, $add )
    {
        $varValueDark   = ColorHelper::mixColors($varValue, '#000000', 20.0);
        $varValueLight  = ColorHelper::mixColors($varValue, '#ffffff', 90.0);

        $strContent     = preg_replace('/\/\*#' . $varName . '_darker#\*\/' . $add . '/', $varValueDark, $strContent);
        $strContent     = preg_replace('/\/\*#' . $varName . '_lighter#\*\/' . $add . '/', $varValueLight, $strContent);

        $rgb = ColorHelper::convertHexColor($varValue);
        
        if( count($rgb) )
        {
            $rgba = 'rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',';

            $strContent = preg_replace('/\/\*#' . $varName . '_trans95#\*\/' . $add . '/', $rgba . '0.95)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans90#\*\/' . $add . '/', $rgba . '0.9)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans85#\*\/' . $add . '/', $rgba . '0.85)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans80#\*\/' . $add . '/', $rgba . '0.8)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans75#\*\/' . $add . '/', $rgba . '0.75)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans70#\*\/' . $add . '/', $rgba . '0.7)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans65#\*\/' . $add . '/', $rgba . '0.65)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans60#\*\/' . $add . '/', $rgba . '0.6)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans55#\*\/' . $add . '/', $rgba . '0.55)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans50#\*\/' . $add . '/', $rgba . '0.5)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans45#\*\/' . $add . '/', $rgba . '0.45)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans40#\*\/' . $add . '/', $rgba . '0.4)', $strContent);
        }

        return $strContent;
    }


}
