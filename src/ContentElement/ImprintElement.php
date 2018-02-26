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
        $this->imprintText = \StringUtil::deserialize($this->imprintText, TRUE);

        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### IMPRINT ###';
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
                "website"       => $this->imprintWeb
            ],

            "addContactLabel"   => $this->addImprintContactLabel
        ];

        $strContent = TwigHelper::render('Website/imprint_intro.html.twig', $context)->getContent();

        foreach( $this->imprintText as $key => $value )
        {
            $strContent .= TwigHelper::render('Website/imprint_' . $value . '.html.twig')->getContent();
        }

        $this->Template->content = $strContent;
    }
}
