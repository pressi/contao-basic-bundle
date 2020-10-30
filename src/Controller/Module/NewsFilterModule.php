<?php

namespace IIDO\BasicBundle\Controller\Module;


use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Model\NewsModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @FrontendModule(NewsFilterModule::TYPE,
 *     category="news",
 *     template="mod_iido_newsFilter",
 *     renderer="forward"
 * )
 */
class NewsFilterModule extends AbstractFrontendModuleController
{
    public const TYPE = 'iidoBasic_newsFilter';



    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        ScriptHelper::addInternScript('filter');
//        echo "<pre>"; print_R( $GLOBALS['TL_JAVASCRIPT']); exit;

        $objNews = NewsModel::findPublishedByPid(1);

        $min = 0;
        $max = 0;

        $ranges = [];
        $arrQM  = [];

        if( $objNews )
        {
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

//                $ranges[] = $qm;
            }
        }
//echo "<pre>"; print_r( $GLOBALS['TL_LANGUAGE']); exit;

        $calcSteps = 10;
        $step = round(($max - $min) / $calcSteps);

        $from = (int) $min;
        for($i =1; $i<=$calcSteps; $i++)
        {
            $counter = 0;
            $to = ($i === $calcSteps ? (int) $max : ($from + $step));

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

//        $count = count( $arrQM[ $min ] );

        $template->min      = $min;
        $template->max      = $max;
        $template->ranges   = $ranges;
        $template->projects = $arrQM;

//        $template->countProjects = $count;
        $template->countProjects = $ranges[0]['projects'];

        return $template->getResponse();
    }

}