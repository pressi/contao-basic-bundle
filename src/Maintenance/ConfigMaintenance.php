<?php


namespace IIDO\BasicBundle\Maintenance;


use Contao\System;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Model\ConfigModel;
use IIDO\BasicBundle\View\BackendFormView;


/**
 * BasicBundle Settings Maintenance
 *
 * This is used on the maintenance page in the backend
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class ConfigMaintenance extends \Backend implements \executable
{
	/**
	 * @return boolean True if the module is active
	 */
	public function isActive(): bool
	{
		return \Input::get('act') == 'iido_settings';
	}



	/**
	 * Generate the module
	 *
	 * @return string
	 */
	public function run()
	{
	    $router = System::getContainer()->get('router');
	    /* @var $router \Symfony\Component\Routing\RouterInterface */

		$objTemplate = new \BackendTemplate('be_maintenance_iido');
		$objTemplate->isActive  = $this->isActive();
		$objTemplate->action    = ampersand(\Environment::get('request'));

		$isActiveMaster = BasicHelper::isActiveBundle('2do/contao-master-connect-bundle');

		$objTemplate->showMasterConnect = $isActiveMaster;
		$objTemplate->masterConnectLink = $isActiveMaster ? $router->generate('iido.backend.masterconnect') : '';

		$objTemplate->configSettingsLink = $router->generate('contao_backend', ['do' => 'config-settings', 'table' => 'tl_iido_config', 'act' => 'edit', 'id' => 1, 'rt' => REQUEST_TOKEN, 'ref' => TL_REFERER_ID]);

        $objSettings = ConfigModel::findAll();

        if( !$objSettings )
        {
            $objSettings = new ConfigModel();

            $objSettings->id        = 1;
            $objSettings->tstamp    = time();

//            $objSettings->styleFiles   = 'fonts,icons,animate,core,buttons,form,forms,layout,hamburgers,hamburgers.min.css,navigation,content,style,styles,page,sidekick,responsive';

            $objSettings->save();
        }


//		if (\Input::get('act') === 'iido_settings')
//		{
//            $objSettings = ConfigModel::findAll();
//
//            if( !$objSettings )
//            {
//                $objSettings = new ConfigModel();
//                $objSettings = $objSettings->save();
//            }
//            else
//            {
//                $objSettings = $objSettings->current();
//            }
//
//		    $beForm = new BackendFormView(ConfigModel::getTable(), $objSettings );
//
//		    $objTemplate->settingsForm = $beForm->renderPalette('default', true);
//		}

//		$this->loadLanguageFile('rocksolid_custom_elements');

		return $objTemplate->parse();
	}
}
