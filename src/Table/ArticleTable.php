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
                $strNewColumn = preg_replace('/ \[([A-Za-z0-9]{0,})\]/', '', $strNewColumn);

                $strLabel   = preg_replace('/\[([A-Za-z\s\-_:]{0,})\]/', '[' . $strNewColumn . ']', trim($strLabel));
            }
        }

        $strLabel = preg_replace('/<span([A-za-z0-9\s\-,;.:_#="]{0,})>\[([A-Za-z\sÜÄÖöäüß]{0,})\]<\/span>/u', '', $strLabel);

        $objParentPage = \PageModel::findByPk( $row['pid'] );

        if( $objParentPage->submenuNoPages && $objParentPage->submenuSRC === 'articles' )
        {
            $navTitle = '';

            if( $row['navTitle'] )
            {
                $navTitle = '[' . $row['navTitle'] . ']';
            }

            $inNav      = '';
            $addon      = '';
            $navColor   = '#999';

            if( $row['hideInMenu'] )
            {
                $inNav = 'nicht in Navigation';
            }

            if( $row['navLinkMode'] )
            {
                $inNav      = $GLOBALS['TL_LANG']['tl_article']['options']['navLinkMode'][ $row['navLinkMode'] ];
                $navColor   = '#5b7ba5';

                if( $row['navLinkMode'] === 'extern' )
                {
                    $addon = '<span class="extern-link" style="margin-left:20px;color:#c5c5c5;">(' . $row['navLinkUrl'] . ')</span>';
                }
            }

            $strLabel =  preg_replace('/<\/a>/' , '</a><span style="display:inline-block;width:150px;text-indent:0;padding-left:8px;">', $strLabel) .'</span>';
            $strLabel .= ' <span class="nav-title" style="display:inline-block;color:#999;margin-left:15px;width:120px;">' . $navTitle . '</span>';

            $strLabel .= '<span class="in-nav" style="color:' . $navColor . ';">' . $inNav . '</span>' .$addon;
        }

        return $strLabel;
    }
}
