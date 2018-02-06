<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use IIDO\BasicBundle\Helper\StylesheetHelper;
use \MatthiasMullie\Minify;


/**
 * IIDO System Listener
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class CombinerListener extends DefaultListener
{

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
