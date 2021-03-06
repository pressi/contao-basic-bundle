<?php

namespace IIDO\BasicBundle\EventListener;


use Contao\Controller;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use IIDO\BasicBundle\Model\NewsAreaOfApplicationModel;
use IIDO\BasicBundle\Model\NewsUsageModel;
use IIDO\BasicBundle\Pages\GlobalElementPage;
use IIDO\BasicBundle\Widget\LayoutWizardWidget;
use IIDO\BasicBundle\Widget\NewsAreaOfApplicationPickerWidget;
use IIDO\BasicBundle\Widget\NewsUsagePickerWidget;
use IIDO\BasicBundle\Model\IidoContentModel;
use IIDO\BasicBundle\Widget\TextFieldWidget;
use IIDO\BasicBundle\Widget\TagsFieldWidget;
use IIDO\BasicBundle\Model\NewsModel;
use IIDO\BasicBundle\Widget\MetaWizardWidget;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use IIDO\BasicBundle\Config\BundleConfig;


/**
 * @Hook("initializeSystem")
 */
class InitializeSystemListener implements ServiceAnnotationInterface
{
    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;


    /**
     * @var RequestStack
     */
    private $requestStack;



    public function __construct(RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }



    public function isBackend()
    {
        return $this->scopeMatcher->isBackendRequest( $this->requestStack->getCurrentRequest() );
    }



    public function isFrontend()
    {
        return $this->scopeMatcher->isFrontendRequest( $this->requestStack->getCurrentRequest() );
    }



    public function __invoke(): void
    {
        $db = Database::getInstance();

        if( !$db->tableExists('tl_iido_config') )
        {
            return;
        }

        $namespace  = BundleConfig::getNamespace() . '\\' . BundleConfig::getSubNamespace();
//        $iidoConfig = System::getContainer()->get('iido.basic.config');
        $objConfigTable = \Contao\Database::getInstance()->prepare('SELECT * FROM tl_iido_config WHERE id=?')->execute(1);

//        if( $this->isBackend() )
        if( TL_MODE === 'BE' )
        {
            $publicBundlePath = BundleConfig::getBundlePath( true );

            $GLOBALS['TL_CSS']['iido_backend']        = $publicBundlePath . '/css/backend.css|static';

            if( $objConfigTable->backendStyles )
            {
                $GLOBALS['TL_CSS']['iido_backend_styles']        = $publicBundlePath . '/css/backend-styles.css|static';
            }

//            if( $iidoConfig->get('enableLayout') )
            if( $objConfigTable->enableLayout )
            {
                $GLOBALS['TL_CSS'][]        = $publicBundlePath . '/scss/backend.scss|static';
            }

            $GLOBALS['TL_JAVASCRIPT'][] = $publicBundlePath . '/js/backend/IIDO.Backend.js|static';
        }

//        array_insert($GLOBALS['TL_CTE']['texts'], 0, array
//        (
//            'column_master' => ColumnMaster::class,
//        ));


        // FE modules
        $GLOBALS['FE_MOD']['navigationMenu']['articlenav'] = $namespace . '\Controller\Modules\ArticlenavModule';
//        $GLOBALS['FE_MOD']['news']['areaOfApplicationList'] = $namespace . '\Controller\Module\NewsAreasOfApplicationModule';



        // BE modules (with tables)
        $GLOBALS['BE_MOD']['system']['config-settings'] =
        [
            'tables'    => ['tl_iido_config']
        ];



        // Page types
        $GLOBALS['TL_PTY']['global_element'] = GlobalElementPage::class;



        // FE form fields
//        $GLOBALS['TL_FFL']['radioTable']        = $ns . '\FormField\RadioButtonTable';
//        $GLOBALS['TL_FFL']['databaseSelect']    = $ns . '\FormField\DatabaseSelect';
        $GLOBALS['TL_FFL']['pickdate']          = $namespace . '\FormField\PickDate';


        // BE form fields
        $GLOBALS['BE_FFL']['imageSize'] = $namespace . '\Widget\ImageSizeWidget';
        $GLOBALS['BE_FFL']['pageTree']  = $namespace . '\Widget\PageTreeWidget';
        $GLOBALS['BE_FFL']['fileTree']  = $namespace . '\Widget\FileTreeWidget';
        $GLOBALS['BE_FFL']['text']      = TextFieldWidget::class;
        $GLOBALS['BE_FFL']['iidoTag']   = TagsFieldWidget::class;
        $GLOBALS['BE_FFL']['metaWizard']        = MetaWizardWidget::class;
        $GLOBALS['BE_FFL']['layoutWizard']      = LayoutWizardWidget::class;

        $GLOBALS['BE_FFL']['newsAreaOfApplicationPicker']   = NewsAreaOfApplicationPickerWidget::class;
        $GLOBALS['BE_FFL']['newsUsagePicker']               = NewsUsagePickerWidget::class;


        // Models
        $GLOBALS['TL_MODELS']['tl_iido_config']     = $namespace . '\Model\ConfigModel';
        $GLOBALS['TL_MODELS']['tl_iido_content']    = IidoContentModel::class;

        $GLOBALS['TL_MODELS']['tl_news_areaOfApplication']  = NewsAreaOfApplicationModel::class;
        $GLOBALS['TL_MODELS']['tl_news_usage']              = NewsUsageModel::class;
        $GLOBALS['TL_MODELS']['tl_news']                    = NewsModel::class;


        // Maintenance
        $GLOBALS['TL_MAINTENANCE'][] = $namespace . '\Maintenance\ConfigMaintenance';


//        $GLOBALS['TL_HOOKS']['getSystemMessages'][]     = array(MessagesListener, 'devModeCheck');


        // Group permissions
        $GLOBALS['TL_PERMISSIONS'][] = 'newsAreaOfApplication';
        $GLOBALS['TL_PERMISSIONS'][] = 'newsAreaOfApplication_default';
        $GLOBALS['TL_PERMISSIONS'][] = 'newsAreaOfApplication_roots';

//        $GLOBALS['TL_PERMISSIONS'][] = 'newsUsage';
//        $GLOBALS['TL_PERMISSIONS'][] = 'newsUsage_default';
//        $GLOBALS['TL_PERMISSIONS'][] = 'newsUsage_roots';
    }
}