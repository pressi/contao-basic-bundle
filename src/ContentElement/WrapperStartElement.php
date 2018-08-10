<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\ContentElement;


/**
 * Front end content element "wrapper start".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class WrapperStartElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_wrapperStart';



    /**
     * Generate configurator element
     *
     * @return string
     */
    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### HTML WRAPPER - START ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        $objSiblings    = \ContentModel::findPublishedByPidAndTable( $this->pid, 'tl_article');
        $countSeparator = 0;
        $hasSeparator   = false;

        if( $objSiblings )
        {
            $isStart    = false;
            $isStop     = false;

            while( $objSiblings->next() )
            {
                if( !$isStart )
                {
                    if( $objSiblings->id === $this->id )
                    {
                        $isStart = true;
                        continue;
                    }
                }
                else
                {
                    if( !$isStop )
                    {
                        if( $objSiblings->type === "iido_wrapperSeparator" )
                        {
                            $countSeparator++;
                            continue;
                        }
                    }

                    if( $objSiblings->type === "iido_wrapperStop" )
                    {
                        $isStop = true;
                        break;
                    }
                }
            }
        }

        if( $countSeparator )
        {
            $hasSeparator = true;
        }

        $cssID = \StringUtil::deserialize($this->cssID, TRUE);
        $cssClass = $cssID[1];

        if( strlen( trim($cssClass) ) )
        {
            $this->Template->class          = preg_replace('/' . $cssClass . '/', '', $this->Template->class);
        }

        $this->Template->addClasses     = ' inside-' . ($countSeparator + 1);
        $this->Template->colClasses     = $cssClass;
        $this->Template->hasSeparator   = $hasSeparator;
    }
}
