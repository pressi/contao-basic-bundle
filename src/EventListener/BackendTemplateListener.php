<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\ArticleModel;
use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FilesModel;
use Contao\Input;
use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Config\IIDOConfig;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


/**
 * Class Backend Template Hook
 * @package IIDO\Customize\Hook
 */
class BackendTemplateListener extends DefaultListener implements ServiceAnnotationInterface
{

    /**
     * @Hook("outputBackendTemplate")
     */
    public function onOutputBackendTemplate($strContent, $strTemplate)
    {
        $config = \Config::getInstance();

        if( $config->isComplete() )
        {
            if( $strTemplate === "be_login" && IIDOConfig::get('customLogin') )
            {
                $arrImages  = scandir(BasicHelper::getRootDir( true ) . 'files/master/Library/Physio');
//                $arrImages  = ImageHelper::getImages( IIDOConfig::get('loginImageSRC') );
                $index      = rand(2, (count($arrImages) - 1));
                $strImage   = $arrImages[ $index ];
                $strLogo    = '';

                $objLogo    = IIDOConfig::get('loginLogoSRC');

                if( $objLogo )
                {
                    $objLogo = FilesModel::findByPk( $objLogo );

                    if( $objLogo )
                    {
                        $strLogo = '#container:before{content:"";position:absolute;top:-15em;right:0;left:0;height:300px;background: transparent url(' . $objLogo->path . ') no-repeat 50% 50px / auto 100px;}';
                    }
                }


                $strBackground  = '<div id="bg_container"><div class="logo"></div></div>';
                $strFooter      = '<footer id="footer">Powered by <a href="https://contao.org" target="_blank" class="contao-logo"></a></footer>';
                $strStyles      = '<style>body{background:#eaeaec;}
#bg_container{position:fixed;top:0;left:0;width:100vw;height:100vh;background-image:url(\'files/master/Library/Physio/' . $strImage . '\');background-size:cover;background-repeat:no-repeat;background-position:center center;}#container{position:relative;}
#main{border-bottom-left-radius:0;border-bottom-right-radius:0;box-shadow:0 0 20px rgba(0, 0, 0, 0.5);}
footer{max-width: 350px;margin: 0 auto;background:#f6f5f5;border-radius:2px;border-top-left-radius:0;border-top-right-radius:0;color:#f47c00;font-size:10px;text-align:right;padding:8px;box-sizing:border-box;line-height:15px;}
footer .contao-logo{display:block;float:right;width:56px;height:15px;background:url(\'/files/master/Library/Logos/contao-logo-corporate.svg\') no-repeat center center / auto 100%;margin:0 0 0 8px;}
' .  $strLogo . '
</style>';

                $strContent = preg_replace('/<body([A-Za-z0-9\s\-="\{\},;.:]{0,})>/', '<body$1>' . $strBackground, $strContent);
                $strContent = preg_replace('/<\/head>/', $strStyles . '</head>', $strContent);
                $strContent = preg_replace('/<\/main>/', $strFooter . '</main>', $strContent);
            }
            elseif( $strTemplate === "be_main" )
            {
                $strTableDiv = '';

                if( Input::get('do') === 'article' && IIDOConfig::get('enableLayout') )
                {
                    $objArticle = ArticleModel::findByPk( Input::get('id') );
                    $show = '';

                    if( $objArticle->start )
                    {
                        $stop = '';

                        if( $objArticle->stop )
                        {
                            $stop = '<div class="stop">bis: ' . date(Config::get('datimFormat'), $objArticle->stop) . '</div>';
                        }

                        $show = '<div class="show"><div class="label">Anzeigen</div>
    <div class="start">ab: ' . date(Config::get('datimFormat'), $objArticle->start) . '</div>' . $stop . '
</div>';
                    }

                    $strTableDiv = '<div class="article-table">
    <div class="name"><div class="label">Artikel</div> <div class="value">' . $objArticle->title . '</div></div>' . $show . '
</div>';
                }

                $strContent = preg_replace('/<table class="tl_header_table/', $strTableDiv . '<table class="tl_header_table', $strContent);

                $strErrorMessage = "";

//				if( $config->get("dps_setDefaultSettings") && $config->get("dps_websiteStatusIsDevelopment") )
//				{
//					$strErrorMessage	.= '<p class="tl_error tl_permalert">' . (($this->User->isAdmin) ? '<a href="' . $this->addToUrl('websiteIsLive=1') . '" class="tl_submit">' . $GLOBALS['TL_LANG']['MSC']['websiteToLive'] . '</a>' : '') . $GLOBALS['TL_LANG']['MSC']['websiteInDevelopment'] . '</p>';
//				}

//				if( !in_array("multicolumnwizard", \ModuleLoader::getActive()) )
//				{
//					$strErrorMessage	.= '<p class="tl_info tl_permalert">' . (($this->User->isAdmin) ? '<a href="' . $this->addToUrl('do=composer&amp;install=menatwork/contao-multicolumnwizard') . '" class="tl_submit">Module installieren</a>' : '') . 'Bitte installieren Sie das Module "multicolumnwizard" um alle Funktionen verwenden zu können!</p>';
//				}

//				if( strlen($strErrorMessage) )
//				{
//					$strContent			= preg_replace('/<\/div>([\s\n]{0,})<\/div>([\s\n]{0,})<div id="container"/', $strErrorMessage . '</div></div><div id="container"', $strContent);
//				}
            }

//            $scripts = '<script src="assets/jquery/js/jquery.min.js"></script>
//<script>jQuery = jQuery.noConflict();</script>
//<script src="bundles/iidobasic/javascript/jquery/jquery.hc-sticky.min.js"></script>
//<script>setTimeout(function(){var fromTop = jQuery("#header").height(); jQuery("#left").hcSticky({top:fromTop});jQuery("#container").css("padding-top", fromTop);}, 500);</script>';


//            $strContent = preg_replace('/<\/body>/', $scripts . '</body>', $strContent);
        }

        return $strContent;
    }



    /**
     * @Hook("parseBackendTemplate")
     */
    public function onParseBackendTemplate($strContent, $strTemplate)
    {
        if( $strTemplate === "be_welcome" )
        {
            $strFieldPrefix = BundleConfig::getTableFieldPrefix();

            if( \Config::get( $strFieldPrefix . 'enableSupportForm') )
            {
                $this->checkSupportForm();

                $strAddress = '';
                $strContact = '';

                if( \Config::get( $strFieldPrefix . 'supportCompany') )
                {
                    $strAddress .= '<strong>' . \Config::get( $strFieldPrefix . 'supportCompany') . '</strong><br>';
                }

                if( \Config::get( $strFieldPrefix . 'supportEmployee') )
                {
                    $strAddress .= \Config::get( $strFieldPrefix . 'supportEmployee') . '<br>';
                }

                if( \Config::get( $strFieldPrefix . 'supportStreet') )
                {
                    $strAddress .= \Config::get( $strFieldPrefix . 'supportStreet') . '<br>';
                }

                if( \Config::get( $strFieldPrefix . 'supportPostal') )
                {
                    $strAddress .= \Config::get( $strFieldPrefix . 'supportPostal');
                }

                if( \Config::get( $strFieldPrefix . 'supportCity') )
                {
                    $strAddress .= \Config::get( $strFieldPrefix . 'supportCity') . '<br>';
                }


                if( \Config::get( $strFieldPrefix . 'supportMail') )
                {
                    $strContact .= '<a href="mailto:' . \Config::get( $strFieldPrefix . 'supportMail') . '" target="_blank">' . \Config::get( $strFieldPrefix . 'supportMail') . '</a><br>';
                }

                if( \Config::get( $strFieldPrefix . 'supportPhone') )
                {
                    $strContact .= '<a href="tel:' . \Config::get( $strFieldPrefix . 'supportPhone') . '" target="_blank">' . \Config::get( $strFieldPrefix . 'supportPhone') . '</a><br>';
                }

                $strBackend = '<div class="backend-welcome-container">
    <h2>Support kontaktieren</h2>
    
    <div class="support-form half-column">
    
        <form action="' . \Environment::get("request") . '" method="post">
            <input type="hidden" name="FORM_SUBMIT" value="iido_welcome_support">
            <input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
        
            <div class="form-widget widget">
                <label for="ctrl_support_subject">Betreff</label>
                <input name="subject" type="text" class="tl_text" id="ctrl_support_subject" required>        
            </div>
            
            <div class="form-widget widget">
                <label for="ctrl_support_message">Nachricht</label>
                <textarea name="message" class="tl_textarea" id="ctrl_support_message" required></textarea>        
            </div>
            
            <div class="form-widget widget widget-submit">
                <button type="submit" class="tl_submit">Support kontaktieren</button>
            </div>
        
        </form>
        
    </div>
    <div class="support-contact half-column">
         <p>' . $strAddress . '</p>
         <p>' . $strContact . '</p>
    </div>
</div>';

                $strContent = preg_replace('/<div([A-Za-z0-9\s\-,;.:_\/="]{0,})id="tl_versions/', $strBackend . '<div$1id="tl_versions', $strContent);
            }
        }

        return $strContent;
    }



    protected function checkSupportForm()
    {
        if( \Input::post("FORM_SUBMIT") === "iido_welcome_support" )
        {
            $adminEmail = \Config::get("adminEmail");
            $objUser    = \BackendUser::getInstance();
            $objEmail   = new \Email();

            $objEmail->subject  = 'Support Anfrage (' . \Config::get("websiteTitle") . ')';

            $objEmail->fromName = 'Website: ' . \Config::get("websiteTitle");
            $objEmail->from     = $objUser->email;

            $objEmail->html     = 'Betreff: ' . \Input::post("subject") . '<br><br>
            Nachricht:<br>' . \Input::post("message") . '<br><br><br>
            Backend-Benutzer: ' . $objUser->username . ' (' . $objUser->email . ')<br><br><br>
            Gesendet von: ' . \Environment::get("base") . ' am ' . date(\Config::get("dateFormat"), time()) . ' um ' . date(\Config::get("timeFormat"), time()) . ' Uhr';

            if( $adminEmail === "development@prestep.at" || $adminEmail === "mail@stephanpresl.at" )
            {
                $objEmail->sendTo( $adminEmail );
            }
            else
            {
                $objEmail->sendTo( $adminEmail, "development@prestep.at" );
            }
        }
    }

}
