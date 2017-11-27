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
use IIDO\BasicBundle\Helper\StylesheetHelper;
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
        $isInFiles  = preg_match('/^files/', $arrFile['name']);
        $isInBundle = preg_match('/^' . preg_quote($this->bundlePathPublic, '/') . '/', $arrFile['name']);

        if( $strMode === \Combiner::CSS )
        {
            if( $isInFiles || $isInBundle )
            {
                $strContent = StylesheetHelper::renderStyleVars( $strContent );

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


}
