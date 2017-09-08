<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace IIDO\BasicBundle\ContentElement;
use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "text".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class DetailElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_details';


    /**
     * Generate the content element
     */
    protected function compile()
    {
        global $objPage;

        $foundOne       = false;
        $getItem        = \Input::get("auto_item");

        if( strlen($getItem) )
        {
            $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->pid, "main");

            if( $objArticles )
            {
                while( $objArticles->next() )
                {
                    $objElements = \ContentModel::findBy(array('pid=?', 'invisible=?', 'type=?'), array($objArticles->id, '', 'rsce_project'));

                    if( $objElements )
                    {
                        while( $objElements->next() )
                        {
                            $rsceData = json_decode($objElements->rsce_data, true);

                            if( (key_exists('alias', $rsceData) && $rsceData['alias'] === $getItem) || $objElements->id === $getItem )
                            {
                                $arrData    = json_decode($objElements->rsce_data, true);
//                                $arrImages  = ImageHelper::getMultipleImages($objElements->multiSRC, $objElements->orderSRC);

                                $this->Template->data       = $arrData;

//                                $this->Template->images     = $arrImages;
                                $this->Template->multiSRC   = $objElements->multiSRC;

                                $foundOne = true;
                                break;
                            }
                        }
                    }

                    if( $foundOne )
                    {
                        break;
                    }
                }
            }
        }

        if( $foundOne )
        {
            $this->Template->backlink = \PageModel::findByPk( $objPage->pid )->getFrontendUrl();
        }
    }
}
