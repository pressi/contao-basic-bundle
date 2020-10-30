<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\NewsArchiveModel;
use Contao\PageModel;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Model\NewsModel;


/**
 * Class ParseArticlesListener
 *
 * @package IIDO\BasicBundle\EventListener
 *
 * @Hook("parseArticles")
 */
class ParseArticlesListener
{
    public function __invoke( FrontendTemplate $template, array $newsEntry, Module $module ): void
    {
        $alias      = $newsEntry['alias'];
        $strLang    = strtolower( BasicHelper::getLanguage() );

        $generateUrl    = false;
        $jumpTo         = 0;

        if( $strLang === 'en' || $strLang === 'en_us' )
        {
            $generateUrl = true;

            $objArchive = NewsArchiveModel::findByPk( $newsEntry['pid'] );
            $jumpToEN   = $objArchive->jumpToEN;

            $aliasEN = $newsEntry['aliasEN'];

            if( $aliasEN )
            {
                $alias = $aliasEN;
            }

            if( $jumpToEN )
            {
                $jumpTo = $jumpToEN;
            }

            if( $strLang === 'en_us' )
            {
                $jumpToUS   = $objArchive->jumpToUS;
                $aliasUS    = $newsEntry['aliasUS'];

                if( $aliasUS )
                {
                    $alias = $aliasUS;
                }

                if( $jumpToUS )
                {
                    $jumpTo = $jumpToUS;
                }
            }
        }

        if( $generateUrl )
        {
            if( $jumpTo )
            {
                $objJumpTo = PageModel::findByPk( $jumpTo );
                $template->link = $objJumpTo->getFrontendUrl('/'. $alias );
            }
            else
            {
                $template->link = preg_replace('/' . $newsEntry['alias'] . '.html$/', $alias . '.html', $template->link);
            }
        }


        $template->hidden = false;

        $qm = $getQM = (int) Input::get('qm');

        if( $qm )
        {
            $min = 0;
            $max = 0;

            $ranges = [];
            $arrQM  = [];

//            $objNews = NewsModel::findAll();
            $objNews = NewsModel::findPublishedByPid(1);

            while( $objNews->next() )
            {
                $qm = $objNews->squareMeters;

                if( !$qm )
                {
                    continue;
                }

                if( $qm < $min || $min === 0 )
                {
                    $min = $qm;
                }

                if( $qm > $max )
                {
                    $max = $qm;
                }

                $arrQM[ $qm ][] = $objNews->current();
            }

            $step = round(($max - $min) / 10);

            $from = (int) $min;
            for($i =1; $i<=10; $i++)
            {
                $counter = 0;
                $to = ($i === 10 ? (int) $max : ($from + $step));

                foreach( $arrQM as $qmeters => $projects)
                {
                    if( $qmeters >= $from && $qmeters <= $to)
                    {
                        $counter = ($counter + count($projects));
                    }
                }

                $ranges[] = [
                    'from'      => $from,
                    'to'        => $to,
                    'projects'  => $counter
                ];

                $from = ($from + $step + 1);
            }

            $proQM      = (int) $newsEntry['squareMeters'];

            $qmStart    = 0;
            $qmEnd      = 0;

            foreach( $ranges as $range )
            {
                if( $getQM >= (int) $range['from'] && $getQM <= (int) $range['to'] )
                {
                    $qmStart    = $range['from'];
                    $qmEnd      = $range['to'];
                    break;
                }
            }

            if( $newsEntry['qm'] === '' || $proQM < $qmStart || $proQM > $qmEnd )
            {
                $template->hidden = true;
            }
        }
    }

}

//class_alias(NewsModel::class, 'NewsModel');