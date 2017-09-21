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

namespace IIDO\BasicBundle\Table;


/**
 * Class Content Table
 *
 * @package IIDO\BasicBundle\Table
 * @author Stephan Preßl <https://github.com/pressi>
 */
class ArticleTable extends \Backend
{

    /**
     * Table Name
     */
    protected $strTable = 'tl_article';


    /**
     * Return the edit article button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
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
