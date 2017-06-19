<?php


namespace IIDO\BasicBundle\Controller;

use Contao\Encryption;
use Contao\Environment;
use IIDO\BasicBundle\ConnectTool;
use Doctrine\DBAL\DBALException;
use Patchwork\Utf8;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the iido process.
 *
 * @author Stephan Preßl <https://github.com/pressi>
 *
 * @Route("/contao", defaults={"_scope" = "backend", "_token_check" = true})
 */
class ConnectionController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $context = [
        'initialized'   => false
    ];



    /**
     * Handles the installation process.
     *
     * @return Response
     *
     * @Route("/iido", name="contao_iido")
     */
    public function indexAction()
    {
        if( $this->container->has('contao.framework') )
        {
            $this->container->get('contao.framework')->initialize();
        }

        $connectTool = $this->container->get('contao.connect_tool');

        if( $connectTool->connectionLost() )
        {
            return $this->render('noConection.html.twig');
        }

        if( $connectTool->isLocked() )
        {
            return $this->render('locked.html.twig');
        }

        if( !$this->container->get('contao.connect_tool_user')->isAuthenticated() )
        {
            return $this->login( $connectTool );
        }

        if (null !== ($response = $this->setUpClient( $connectTool )))
        {
            return $response;
        }

        $this->setConnectVarsToContext( $connectTool );

        return $this->render('main.html.twig', $this->context);
    }



    /**
     * Renders a form to log in.
     *
     * @return Response|RedirectResponse
     */
    private function login( $connectTool )
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if( 'tl_login' !== $request->request->get('FORM_SUBMIT') )
        {
            return $this->render('login.html.twig');
        }

        $verified       = $connectTool->checkPassword( $request->request->get('password') );

        if (!$verified)
        {
            $connectTool->increaseLoginCount();

            return $this->render('login.html.twig', [
                'error' => $this->trans('invalid_password'),
            ]);
        }

        $connectTool->resetLoginCount();
        $this->container->get('contao.connect_tool_user')->setAuthenticated(true);
        $this->container->get('contao.connect_tool_user')->setPassword( $request->request->get('password') );

        return $this->getRedirectResponse();
    }



    /**
     * Renders a template.
     *
     * @param string $name
     * @param array  $context
     *
     * @return Response
     */
    private function render($name, $context = [])
    {
        return new Response(
            $this->container->get('twig')->render(
                '@IIDOBasic/'.$name,
                $this->addDefaultsToContext($context)
            )
        );
    }



    /**
     * Translate a key.
     *
     * @param string $key
     *
     * @return string
     */
    private function trans($key)
    {
        return $this->container->get('translator')->trans($key);
    }



    /**
     * Returns a redirect response to reload the page.
     *
     * @return RedirectResponse
     */
    private function getRedirectResponse()
    {
        return new RedirectResponse($this->container->get('request_stack')->getCurrentRequest()->getRequestUri());
    }



    /**
     * Adds the default values to the context.
     *
     * @param array $context
     *
     * @return array
     */
    private function addDefaultsToContext(array $context)
    {
        $context = array_merge($this->context, $context);

        if (!isset($context['request_token']))
        {
            $context['request_token'] = $this->getRequestToken();
        }

        if (!isset($context['language']))
        {
            $context['language'] = $this->container->get('translator')->getLocale();
        }

        if (!isset($context['ua']))
        {
            $context['ua'] = Environment::get('agent')->class;
        }

        if (!isset($context['path']))
        {
            $context['path'] = $this->container->get('request_stack')->getCurrentRequest()->getBasePath();
        }

        return $context;
    }



    /**
     * Returns the request token.
     *
     * @return string
     */
    private function getRequestToken()
    {
        if (!$this->container->hasParameter('contao.csrf_token_name'))
        {
            return '';
        }

        return $this->container
            ->get('security.csrf.token_manager')
            ->getToken($this->getContainerParameter('contao.csrf_token_name'))
            ->getValue();
    }



    /**
     * Returns a parameter from the container.
     *
     * @param string $name
     *
     * @return mixed
     */
    private function getContainerParameter($name)
    {
        if ($this->container->hasParameter($name))
        {
            return $this->container->getParameter($name);
        }

        return null;
    }



    /**
     * Set connect variables to context
     *
     * @var ConnectTool $connectTool
     */
    private function setConnectVarsToContext( $connectTool )
    {
        $arrData = $connectTool->getData();

        $this->context['themes']        = (array) $arrData->themes;
        $this->context['templates']     = (array) $arrData->rsce_templates;
    }



    /**
     * Set up the client.
     *
     * @var ConnectTool $connectTool
     *
     * @return Response|RedirectResponse|null
     */
    private function setUpClient( $connectTool )
    {
        if ( $connectTool->isConnectToolInitialized() )
        {
            $this->context['initialized'] = true;

            return null;
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ('tl_config' !== $request->request->get('FORM_SUBMIT'))
        {
            return null;
        }

        $customerName   = $request->request->get('customer_name');
        $customerEmail  = $request->request->get('customer_email');
        $customerAlias  = $request->request->get('customer_alias');

        $themeID        = $request->request->get('theme');
        $RSCE_Templates = $request->request->get('rsce_templates');

        // All fields are mandatory
        if ('' === $customerName || '' === $customerEmail || '' === $customerAlias || '' === $themeID)
        {
            $this->context['config_error'] = $this->trans('config_error');

            return null;
        }

        $arrData = $connectTool->getActionData("getContaoInit", array('themeID=' . $themeID) );

        $connectTool->setUpFilesFolder( $arrData->folders );
        $connectTool->setUpTemplates( $arrData->templates );

//        $connectTool->getFilesFromMaster( $arrData->files );

        $arrTheme   = (array) $arrData->theme;
        unset($arrTheme['layouts']);
        unset($arrTheme['modules']);
        unset($arrTheme['imageSizes']);
        unset($arrTheme['imageSizeItems']);

        $connectTool->createNewOneModelEntry("Theme", $arrTheme, array('master_ID' => $themeID));

        // Create Layouts
        $connectTool->createNewModelEntry("Layout", (array) $arrData->theme->layouts, array('master_ID' => 'field_id'));

        // Create Modules
        $connectTool->createNewModelEntry("Module", (array) $arrData->theme->modules, array('master_ID' => 'field_id'));

        // Create Image Sizes
        $connectTool->createNewModelEntry("ImageSize", (array) $arrData->theme->imageSizes);

        // Create Image Size Items
        $connectTool->createNewModelEntry("ImageSizeItem", (array) $arrData->theme->imageSizeItems);



        // Create Pages
        $connectTool->createPages( (array) $arrData->pages );


        // Backend Users
        foreach((array) $arrData->users as $username => $arrUser)
        {
            $arrUser = (array) $arrUser;
            $arrUser['password'] = md5($arrUser['password']);

            $connectTool->createNewOneModelEntry("User", $arrUser);
        }


        // Backend User & Group (Redakteur)
        $arrUser = (array) $arrData->user;
        $arrUser['password'] = md5($arrUser['password']);

        $objUserGroup   = $connectTool->createNewOneModelEntry("UserGroup", (array) $arrData->user_group);
        $this->container->get("contao.connect_tool")->setBackendUserGroup( $objUserGroup );

        $objUser        = $connectTool->createNewOneModelEntry("User", $arrUser);
        $this->container->get("contao.connect_tool")->setBackendUser( $objUser );



        // Get RSCE Templates
//        if( is_array($RSCE_Templates) && count($RSCE_Templates) )
//        {
//            foreach($RSCE_Templates as $fileName)
//            {
//                echo "<pre>";
//                print_r( $fileName );
//                exit;
//            }
//        }



        // Set Contao Settings
        foreach( (array) $arrData->settings as $varName => $varValue)
        {
            if( is_array($varValue) )
            {
                $varValue = serialize($varValue);
            }

            $varValue = $connectTool->replaceVars( $varValue );

            $connectTool->persistConfig( $varName, $varValue);
        }

        $connectTool->persistConfig( 'iido_initSystem', TRUE);
        $connectTool->persistConfig( 'clientID', $arrData->clientID);

        return $this->getRedirectResponse();
    }

}
