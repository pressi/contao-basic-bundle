<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
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



    /**
     * Add an image to each page in the tree
     *
     * @param array  $row
     * @param string $label
     *
     * @return string
     */
    public function addIcon($row, $label)
    {
        $strArticleTable    = new \tl_article();
        $strLabel           = $strArticleTable->addIcon( $row, $label );

        if( $row['articleType'] !== "content" )
        {
            \Controller::loadLanguageFile( $this->strTable );

            $strNewColumn   = $GLOBALS['TL_LANG'][ $this->strTable ]['options']['articleType'][ $row['articleType'] ];

            if( strlen($strNewColumn) )
            {
                $strLabel   = preg_replace('/\[([A-Za-z\s\-_:]{0,})\]/', '[' . $strNewColumn . ']', trim($strLabel));
            }
        }

        return $strLabel;
    }
}
