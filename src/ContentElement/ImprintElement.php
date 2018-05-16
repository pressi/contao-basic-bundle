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

                "mitglied"              => $this->imprintMitglied,
                "berufsrecht"           => $this->imprintBerufsrecht,
                "behoerde"              => $this->imprintBehoerde,
                "beruf"                 => $this->imprintBeruf,
                "country"               => $this->imprintCountry,
                "objectOfTheCompany"    => $this->imprintObjectOfTheCompany,
                "VATnumber"             => $this->imprintVATnumber,

                "companies"     => $this->renderLinkList($this->imageCopyrights),

                "companyWording"        => $this->imprintCompanyWording,
                "managingDirector"      => $this->imprintManagingDirector,
                "section"               => $this->imprintSection,
                "occupationalGroup"     => $this->imprintOccupationalGroup,
                "companyRegister"       => $this->imprintCompanyRegister,
                "firmengericht"         => $this->imprintFirmengericht,

                "additionalText"        =>  \StringUtil::encodeEmail( \StringUtil::toHtml5($this->imprintAddText) )
            ],

            "addContactLabel"   => $this->addImprintContactLabel
        ];


        if( count($this->imprintText) )
        {
            $strContent = TwigHelper::render('Website/imprint_intro.html.twig', $context)->getContent();
        }

        foreach( $this->imprintText as $key => $value )
        {
            $strContent .= TwigHelper::render('Website/imprint_' . $value . '.html.twig', $context)->getContent();
        }

        foreach( $this->privacyPolicyText as $key => $value )
        {
            $strContent .= TwigHelper::render('Website/imprint_' . $value . '.html.twig', $context)->getContent();
        }

        $this->Template->content = $strContent;
    }



    protected function renderLinkList( $arrLinks )
    {
        foreach( $arrLinks as $key => $arrValue)
        {
            $strLink = $arrValue[ 1 ];

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
                'name'      => trim($arrValue[ 0 ]),
                'link'      => trim($strLink),
                'linkName'  => trim($arrValue[1]),
                'titleLink' => ($arrValue[ 2 ] ?: 0)
            );
        }

        return $arrLinks;
    }
}
