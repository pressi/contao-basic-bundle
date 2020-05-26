<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\ArticleModel;
use Contao\Config;
use Contao\ContentModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Config\BundleConfig;


class ElementHelper
{
    public static function checkIfFirstElementOfType( $element, $type, $isAlias = false )
    {
        $objElements = ContentModel::findBy( ['pid=?', 'ptable=?'], [ $element->pid, $element->ptable ] );

        if( $objElements )
        {
            if( $objElements->count() === 1 )
            {
                return true;
            }
            else
            {
                $firstElement = $objElements->first();

                if( $firstElement->id === $element->id )
                {
                    return true;
                }
                else
                {
                    $objElements->reset();

                    $checkElement = true;
                    $isFirst = false;

                    while( $objElements->next() )
                    {
                        if( $objElements->id === $element->id )
                        {
                            if( $checkElement )
                            {
                                $isFirst = true;
                                break;
                            }
                            else
                            {
                                break;
                            }
                        }
                        else
                        {
                            $objAliasElement = false;
                            if( $isAlias && $objElements->type === 'alias' )
                            {
                                $objAliasElement = ContentModel::findByPk( $objElements->cteAlias );
                            }

                            if( ($objAliasElement && $objAliasElement->type === $type) || $objElements->type === $type )
                            {
                                $checkElement = false;
                            }
                        }
                    }

                    if( $isFirst )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }



    public static function checkIfLastElementOfType( $element, $type, $isAlias = false )
    {
        $objElements = ContentModel::findBy( ['pid=?', 'ptable=?'], [ $element->pid, $element->ptable ] );

        if( $objElements )
        {
            if( $objElements->count() === 1 )
            {
                return true;
            }
            else
            {
                $lastElement = $objElements->last();

                if( $lastElement->id === $element->id )
                {
                    return true;
                }
                else
                {
                    $objElements->reset();

                    $checkElement = false;
                    $isLast = false;

                    while( $objElements->next() )
                    {
                        if( $checkElement )
                        {
                            if( $objElements->type !== $type )
                            {
                                $isLast = true;
                            }

                            break;
                        }
                        else
                        {
                            if( $objElements->id === $element->id )
                            {
                                $checkElement = true;
                            }
                        }
                    }

                    if( $isLast )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}