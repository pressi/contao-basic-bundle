<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;

use IIDO\BasicBundle\Helper\BasicHelper;

/**
 * Class Content Table
 * @package IIDO\BasicBundle\Table
 */
class ArticleTable extends \Backend
{

    protected $strTable             = 'tl_article';



    public function editArticle( $row, $href, $label, $title, $icon, $attributes )
    {
        $objClass   = new \tl_article();
        $isAllowed  = $objClass->editArticle( $row, $href, $label, $title, $icon, $attributes );

        if( strlen($isAllowed) && $row['noContent'] )
        {
            return \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return $isAllowed;
    }
}
