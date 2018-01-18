<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Twig;


class AppExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array
        (
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('replaceNL', array($this, 'replaceNewLineFilter')),
            new \Twig_SimpleFilter('masterStylesheetExists', array($this, 'checkIfMasterStylesheetExists'))
        );
    }



    public function getFunctions()
    {
        return array
        (
            new \Twig_SimpleFunction('websiteTitle', array($this, 'getWebsiteTitleFunction')),
            new \Twig_SimpleFunction('socialmedia', array($this, 'getSocialmediaFunction')),
            new \Twig_SimpleFunction('ua', array($this, 'getUAFunctions')),
            new \Twig_SimpleFunction('lang', array($this, 'getLanguageFunctions'))
        );
    }



    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }



    public function replaceNewLineFilter( $text )
    {
        return \Controller::replaceInsertTags( str_replace(array('{{br}}','\n','$lt;br&gt;'), array('<br>','<br>','<br>'), $text) );
    }



    public function checkIfMasterStylesheetExists( $text )
    {
        $rootDir    = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
        $fileName   = trim( strtolower( preg_replace(array('/ \/ /', '/ \(HTC\)/'), array('/', '.htc'), $text) ) );

        if( !preg_match('/.htc$/', $fileName) )
        {
            $fileName = $fileName . '.css';
        }

        if( file_exists($rootDir . '/files/master/css/' . $fileName) )
        {
            $text = '<span class"file-exists" style="text-decoration:line-through">' . $text . '</span>';
        }

        return $text;
    }



    public function getWebsiteTitleFunction()
    {
        return $GLOBALS['TL_CONFIG']['websiteTitle'];
    }



    public function getSocialmediaFunction()
    {
//        $objTemplate    = new \FrontendTemplate('ce_iido_socialmedia');
//        $objTemplate->links = array(); //\IIDO\WebsiteBundle\Helper\Socialmedia::getSocialmediaLinks();

//        if( count($objTemplate->links) > 0 )
//        {
//            return $objTemplate->getResponse();
//        }

        return \Controller::replaceInsertTags('{{iido::socialmedia::icons}}');
    }

    public function getUAFunctions()
    {
        return \Environment::get('agent')->class;
    }



    public function getLanguageFunctions()
    {
        return $GLOBALS['TL_LANGUAGE'];
    }



    public function getName()
    {
        return 'app_extension';
    }
}