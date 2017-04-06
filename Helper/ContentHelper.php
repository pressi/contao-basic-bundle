<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;

class ContentHelper
{

    public static function generateImageHoverTags( $strContent, $objRow )
    {
        $hoverTags = '<div class="image-hover-container"></div>';

        if( $objRow->caption || preg_match('/figcaption/', $strContent) )
        {
            $strContent = preg_replace('/<\/a>([\s\n]{0,})<figcaption/' , $hoverTags . '</a>$1<figcaption', $strContent);
        }
        else
        {
            $strContent = preg_replace('/<\/a>([\s\n]{0,})<\/figure>/' , $hoverTags . '</a>$1</figure>', $strContent);
        }

        return $strContent;
    }

}