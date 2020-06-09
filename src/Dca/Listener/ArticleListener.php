<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Dca\Listener;


use Contao\Database;
use Contao\DataContainer;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class ArticleListener implements ServiceAnnotationInterface
{
    /**
     * @Callback(table="tl_article", target="config.onsubmit")
     */
    public function saveTable( DataContainer $dc ): void
    {
        // Return if there is no active record (override all)
        if( !$dc->activeRecord )
        {
            return;
        }

        $activeRecord = $dc->activeRecord;

        if( $activeRecord->articleType !== 'default' )
        {
            $templateName = '';

            switch( $activeRecord->articleType )
            {
                case 'header':
                    $templateName = 'mod_article_header';
                    break;
            }

            if( $templateName )
            {
                if( $activeRecord->customTpl !== $templateName )
                {
                    Database::getInstance()->prepare("UPDATE tl_article SET customTpl=? WHERE id=?")
                        ->execute($templateName, $dc->id);
                }
            }
        }

    }



    /**
     * @Callback(table="tl_article", target="list.label.label")
     */
    public function loadLabel( array $row, string $label, DataContainer $dc, $columns ): string
    {
        $articleClass = new \tl_article();
        $label = $articleClass->addIcon( $row, $label );

        if( $row['articleType'] !== 'default' )
        {
            $typeLabel = $GLOBALS['TL_LANG']['tl_article']['options']['articleType'][ $row['articleType'] ];

            $label = preg_replace('/\[(.*)\]/', '[' . $typeLabel . ']', $label);
        }

//        $label =  str_replace('</a>', '</a><span class="article-title">', $label) . '</span>';
        $label =  '<span class="article-title">' . $label . '</span>';

//        $label .= '<span class="article-col-width"></span>';

        return $label;
    }
}