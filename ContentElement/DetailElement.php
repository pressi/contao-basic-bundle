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

namespace IIDO\BasicBundle\ContentElement;


use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "product/project detail".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
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
        $elementType    = $this->elementType ?: 'project';

        if( strlen($getItem) )
        {
            $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->pid, "main");

            if( $objArticles )
            {
                while( $objArticles->next() )
                {
                    $objElements = \ContentModel::findBy(array('pid=?', 'invisible=?', 'type=?'), array($objArticles->id, '', 'rsce_' . $elementType));

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
