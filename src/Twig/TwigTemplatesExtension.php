<?php


namespace IIDO\BasicBundle\Twig;


use Contao\Controller;
use IIDO\BasicBundle\Helper\BasicHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


class TwigTemplatesExtension extends AbstractExtension
{
    /**
     * get Twig Template Filter
     *
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return array
        (
            new TwigFilter('price', array($this, 'priceFilter')),
            new TwigFilter('replaceNL', array($this, 'replaceNewLineFilter')),
//            new TwigFilter('masterStylesheetExists', array($this, 'checkIfMasterStylesheetExists')),
//            new TwigFilter('masterTemplateExists', array($this, 'checkIfMasterTemplateExists'))
        );
    }



    /**
     * get Twig Template Functions
     *
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return array
        (
            new TwigFunction('websiteTitle', array($this, 'getWebsiteTitleFunction')),
            new TwigFunction('socialmedia', array($this, 'getSocialmediaFunction')),
            new TwigFunction('ua', array($this, 'getUAFunctions')),
            new TwigFunction('lang', array($this, 'getLanguageFunctions')),

            new TwigFunction('getTrans', [$this, 'getTrans']),
            new TwigFunction('renderClass', [$this, 'renderClass']),
            new TwigFunction('generateRoute', [$this, 'generateRoute'])
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



//    public function checkIfMasterStylesheetExists( $text )
//    {
//        $rootDir    = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
//        $fileName   = trim( strtolower( preg_replace(array('/ \/ /', '/ \(HTC\)/'), array('/', '.htc'), $text) ) );
//
//        if( !preg_match('/.htc$/', $fileName) )
//        {
//            $fileName = $fileName . '.css';
//        }
//
//        if( file_exists($rootDir . '/files/master/css/' . $fileName) )
//        {
//            $text = '<span class"file-exists" style="text-decoration:line-through">' . $text . '</span>';
//        }
//
//        return $text;
//    }

//    public function checkIfMasterTemplateExists( $text )
//    {
//        $file       = preg_replace(array('/\s\(([A-Za-z0-9\s\-]{1,})\)/'), array(''), $text);
//        $fileName   = trim( strtolower( preg_replace(array('/ \/ /'), array('/'), $file) ) );
//
//        $rootDir        = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
//        $arrTemplates   = $this->getTemplateFolders();
//        $websiteDir     = 'global';
//
//        if( count($arrTemplates) === 1 )
//        {
//            $websiteDir = $arrTemplates[0];
//
//            if( file_exists($rootDir . '/templates/' . $websiteDir . '/' . $fileName) )
//            {
//                $text = '<span class"file-exists" style="text-decoration:line-through">' . $text . '</span>';
//            }
//        }
//        elseif( count( $arrTemplates ) > 1 )
//        {
//            $text = $text . ' <span class="exists-folder">' . implode(",", $arrTemplates) . '</span>';
//        }
//
//        return $text;
//    }



    protected function getTemplateFolders()
    {
        return scan( BasicHelper::getRootDir() .  '/templates' );
    }



    public function getWebsiteTitleFunction()
    {
        global $objPage;
        return $GLOBALS['TL_CONFIG']['websiteTitle']?:$objPage->rootTitle;
    }



    public function getSocialmediaFunction()
    {
//        $objTemplate    = new \FrontendTemplate('ce_iido_socialmedia');
//        $objTemplate->links = array(); //\IIDO\WebsiteBundle\Helper\Socialmedia::getSocialmediaLinks();

//        if( count($objTemplate->links) > 0 )
//        {
//            return $objTemplate->getResponse();
//        }

        return '';
//        return \Controller::replaceInsertTags('{{iido::socialmedia::icons}}');
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




    public function getTrans( $table, $field, $key )
    {
        Controller::loadLanguageFile( $table );
        return $GLOBALS['TL_LANG'][ $table ]['options'][ $field ][ $key ];
    }



    public function renderClass( $strClass )
    {
        $strClass = preg_replace('/ /', '-', $strClass);

        return $strClass;
    }



    public function generateRoute( $router, $routeName, $varName, $varValue )
    {
        return $router->generate($routeName, [$varName=>$varValue]);
    }
}