<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Twig;


use IIDO\BasicBundle\Helper\BasicHelper;


class AppExtension extends \Twig_Extension
{

    /**
     * get Twig Template Filter
     *
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return array
        (
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('replaceNL', array($this, 'replaceNewLineFilter')),
            new \Twig_SimpleFilter('masterStylesheetExists', array($this, 'checkIfMasterStylesheetExists')),
            new \Twig_SimpleFilter('masterTemplateExists', array($this, 'checkIfMasterTemplateExists'))
        );
    }



    /**
     * get Twig Template Functions
     *
     * @return array|\Twig_Function[]
     */
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

    public function checkIfMasterTemplateExists( $text )
    {
        $file       = preg_replace(array('/\s\(([A-Za-z0-9\s\-]{1,})\)/'), array(''), $text);
        $fileName   = trim( strtolower( preg_replace(array('/ \/ /'), array('/'), $file) ) );

        $rootDir        = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
        $arrTemplates   = $this->getTemplateFolders();
        $websiteDir     = 'global';

        if( count($arrTemplates) === 1 )
        {
            $websiteDir = $arrTemplates[0];

            if( file_exists($rootDir . '/templates/' . $websiteDir . '/' . $fileName) )
            {
                $text = '<span class"file-exists" style="text-decoration:line-through">' . $text . '</span>';
            }
        }
        elseif( count( $arrTemplates ) > 1 )
        {
            $text = $text . ' <span class="exists-folder">' . implode(",", $arrTemplates) . '</span>';
        }

        return $text;
    }



    protected function getTemplateFolders()
    {
        return scan( BasicHelper::getRootDir() .  '/templates' );
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
        return BasicHelper::getLanguage();
    }



    public function getName()
    {
        return 'app_extension';
    }
}