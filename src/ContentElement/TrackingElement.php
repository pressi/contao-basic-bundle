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
 * Front end content element "flip".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class TrackingElement extends \ContentElement
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_tracking';



    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            return "TRACKING";
        }

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        $useFacebook    = FALSE;
        $fbMode = $fbAction = $fbObject = '';

        $useGoogle      = FALSE;
        $arrGoogleTrackingTag = array();
        $gMode = $gEvent = $gCategory = $gAction = $gLabel = $gTitle = $gLocation = $gPage = '';


        if( $this->enableFacebookPixelTracking )
        {
            $useFacebook    = TRUE;
            $fbMode         = $this->fbq_mode;
            $fbAction       = $this->fbq_action;
            $fbObject       = $this->fbq_object;
        }


        if( $this->enableGoogleTracking )
        {
            $useGoogle      = TRUE;
            $gMode          = 'send';
            $gEvent         = $this->gt_event;

            $gCategory      = $this->gt_category;
            $gAction        = $this->gt_action;
            $gLabel         = $this->gt_label;

            $gTitle         = $this->gt_pageTitle;
            $gLocation      = $this->gt_location;
            $gPage          = $this->gt_page?:\Environment::get("request");

            $arrGoogleTrackingTag = array($gMode, $gEvent, $gCategory, $gAction, $gLabel);

            if( $gEvent === 'pageview' )
            {
                $arrConfig = array('hitType' => $gEvent, 'title' => $gTitle, 'page' => $gPage);

                if( $gLocation )
                {
                    $arrConfig['location'] = $gPage;
                }

                $arrGoogleTrackingTag = array($gMode, $arrConfig);
            }
        }

        $this->Template->useFacebook            = $useFacebook;
        $this->Template->facebookTrackingTag    = array($fbMode, $fbAction, $fbObject);

        $this->Template->useGoogle              = $useGoogle;
        $this->Template->googleTrackingTag      = $arrGoogleTrackingTag;
    }
}
