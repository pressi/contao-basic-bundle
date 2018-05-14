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

                "companies"     => array()
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
}
