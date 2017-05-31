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
                            $varValue = preg_replace('/&#35;/', '#', $varValue);
                        }

                        $strContent = preg_replace('/\/\*#' . $varName . '#\*\/' . $add . '/', $varValue, $strContent);
                    }
                }

                $objMinify = new Minify\CSS();
                $objMinify->add( $strContent );
                $strContent = $objMinify->minify();
            }
        }
        elseif( $strMode === \Combiner::JS )
        {
            if( $isInFiles || $isInBundle )
            {
                $objMinify = new Minify\JS();
                $objMinify->add( $strContent );
                $strContent = $objMinify->minify();
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
                            }

                        }
                    }

                }
            }

        }

        return $arrVariables;
    }

    
}
