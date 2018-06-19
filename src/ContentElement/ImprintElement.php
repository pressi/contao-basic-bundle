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


use IIDO\BasicBundle\Helper\TwigHelper;


/**
 *
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class ImprintElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_imprint';



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

            $objTemplate->wildcard  = '### IMPRINT / PRIVACY POLICY ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        $this->imprintText          = \StringUtil::deserialize($this->imprintText, TRUE);
        $this->privacyPolicyText    = \StringUtil::deserialize($this->privacyPolicyText, TRUE);

        $this->imageCopyrights      = \StringUtil::deserialize($this->imprintImageCopyrights, TRUE);

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        $strContent = '';

        $isActiveInstagram  = key_exists("instagram", $this->privacyPolicySocialmediaText);
        $isActiveYoutube    = key_exists("youtube", $this->privacyPolicySocialmediaText);
        $isActiveFacebook   = key_exists("facebook", $this->privacyPolicySocialmediaText);
        $isActiveTwitter    = key_exists("twitter", $this->privacyPolicySocialmediaText);

        if( $this->imprintWeb && !preg_match('/^http/', $this->imprintWeb) )
        {
            $this->imprintWebLink = 'http://' . $this->imprintWeb;
        }

        $context    =
        [
            "content" =>
            [
                "headline"      => $this->headline,
                "companyName"   => $this->imprintCompanyName,
                "subline"       => $this->imprintSubline,
                "street"        => $this->imprintStreet,
                "postal"        => $this->imprintPostal,
                "city"          => $this->imprintCity,

                "phone"         => $this->imprintPhone,
                "fax"           => $this->imprintFax,
                "email"         => $this->imprintEmail,
                "website"       => $this->imprintWeb,
                "websiteLink"   => $this->imprintWebLink,

                "mitglied"              => $this->imprintMitglied,
                "berufsrecht"           => $this->imprintBerufsrecht,
                "behoerde"              => $this->imprintBehoerde,
                "beruf"                 => $this->imprintBeruf,
                "country"               => $this->imprintCountry,
                "objectOfTheCompany"    => $this->imprintObjectOfTheCompany,
                "VATnumber"             => $this->imprintVATnumber,

                "companies"             => $this->renderLinkList($this->imageCopyrights),

                "companyWording"        => $this->imprintCompanyWording,
                "managingDirector"      => $this->imprintManagingDirector,
                "section"               => $this->imprintSection,
                "occupationalGroup"     => $this->imprintOccupationalGroup,
                "companyRegister"       => $this->imprintCompanyRegister,
                "firmengericht"         => $this->imprintFirmengericht,

                "additionalText"        =>  \StringUtil::encodeEmail( \StringUtil::toHtml5($this->imprintAddText) ),

                "linkToPrivacyPolicy"   => ($this->privacyPolicyPage ? \PageModel::findByPk( $this->privacyPolicyPage )->getFrontendUrl() : '')
            ],

            "addContactLabel"   => $this->addImprintContactLabel,

            "socialmedia" =>
            [
                "instagram"     => $isActiveInstagram,
                "youtube"       => $isActiveYoutube,
                "facebook"      => $isActiveFacebook,
                "twitter"       => $isActiveTwitter
            ]
        ];


        if( count($this->imprintText) )
        {
            $strContent = TwigHelper::render('Website/imprint_intro.html.twig', $context)->getContent();
        }

        foreach( $this->imprintText as $key => $value )
        {
            $strContent .= TwigHelper::render('Website/imprint_' . $value . '.html.twig', $context)->getContent();
        }

        if( count($this->imprintText) && $this->privacyPolicyPage )
        {
            $strContent .= TwigHelper::render('Website/imprint_to_privacy-policy.html.twig', $context)->getContent();
        }



        // Privacy policy

        foreach( $this->privacyPolicyText as $key => $value )
        {
            $strContent .= TwigHelper::render('Website/imprint_' . $value . '.html.twig', $context)->getContent();
        }

        if( count($this->privacyPolicySocialmediaText) && ($isActiveInstagram || $isActiveFacebook || $isActiveTwitter || $isActiveYoutube) )
        {
            $strContent = TwigHelper::render('Website/imprint_privacy-policy_socialmedia.html.twig', $context)->getContent();
        }

        foreach( $this->privacyPolicySocialmediaText as $key => $value )
        {
            $strContent .= TwigHelper::render('Website/imprint_privacy-policy_socialmedia_' . $value . '.html.twig', $context)->getContent();
        }

//        if( count($this->privacyPolicyText) )
//        {
//            $strContent .= '<p>Quelle: Erstellt mit dem <a href="https://www.firmenwebseiten.at/datenschutz-generator/" target="_blank">Datenschutz Generator von firmenwebseiten.at</a> in Kooperation mit <a href="http://checkmallorca.de" target="_blank">checkmallorca.de</a></p>';
//        }

        $this->Template->content = $strContent;
    }



    protected function renderLinkList( $arrLinks )
    {
        foreach( $arrLinks as $key => $arrValue)
        {
            $strLink = $arrValue['link'];

            if( strlen($strLink) && !preg_match('/^http/', $strLink) )
            {
                $strLink = 'http://' . $strLink;
            }

            if( !strlen($strLink) )
            {
                $strLink = 0;
            }

            $arrLinks[ $key ] = array
            (
                'name'      => trim($arrValue['title']),
                'link'      => trim($strLink),
                'linkName'  => trim($arrValue['linkTitle'] ? :$arrValue['link']),
                'titleLink' => ($arrValue['titleLink'] ?: 0)
            );
        }

        return $arrLinks;
    }
}
