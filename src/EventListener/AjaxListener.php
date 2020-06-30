<?php


namespace IIDO\BasicBundle\EventListener;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\NoContentResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Config\IIDOConfig;
use IIDO\BasicBundle\Widget\NewsAreaOfApplicationPickerWidget;
use IIDO\BasicBundle\Widget\NewsUsagePickerWidget;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class AjaxListener extends Backend implements ServiceAnnotationInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @var ContaoFramework
     */
    private $framework;



    /**
     * AjaxListener constructor.
     *
     * @param LoggerInterface $logger
     * @param ContaoFramework $framework
     */
    public function __construct(LoggerInterface $logger, ContaoFramework $framework)
    {
        $this->logger = $logger;
        $this->framework = $framework;

        parent::__construct();
    }



    /**
     * @Hook("executePostActions")
     */
    public function onExecutePostActions($action, DataContainer $dc)
    {
        if( 'reloadNewsAreasOfApplicationWidget' === $action )
        {
            $this->reloadNewsAreasOfApplicationWidget($dc);
        }
        elseif( 'reloadNewsUsageWidget' === $action )
        {
            $this->reloadNewsUsageWidget($dc);
        }
        elseif( 'toggleSubpalette' === $action )
        {
            $this->toggleSubpalette( $dc );
        }
    }



    private function toggleSubpalette( DataContainer $dc )
    {
        $this->import(BackendUser::class, 'User');

        // Check whether the field is a selector field and allowed for regular users (thanks to Fabian Mihailowitsch) (see #4427)
        if (!\is_array($GLOBALS['TL_DCA'][$dc->table]['palettes']['__selector__']) || !\in_array(Input::post('field'), $GLOBALS['TL_DCA'][$dc->table]['palettes']['__selector__']) || ($GLOBALS['TL_DCA'][$dc->table]['fields'][Input::post('field')]['exclude'] && !$this->User->hasAccess($dc->table . '::' . Input::post('field'), 'alexf')))
        {
            $this->log('Field "' . Input::post('field') . '" is not an allowed selector field (possible SQL injection attempt)', __METHOD__, TL_ERROR);

            throw new BadRequestHttpException('Bad request');
        }

        if( $dc instanceof \DC_YamlConfigFile )
        {
            $val = (Input::post('state') == 1);
            IIDOConfig::persist(Input::post('field'), $val);

            if( Input::post('load') )
            {
                IIDOConfig::set(Input::post('field'), $val);

//                IIDOConfig::getInstance()->save();

                throw new ResponseException( $this->convertToResponse($dc->edit(false, Input::post('id'))) );
            }

//            throw new NoContentResponseException();
        }
    }



    /**
     * Reload the news categories widget.
     *
     * @param DataContainer $dc
     */
    private function reloadNewsAreasOfApplicationWidget(DataContainer $dc)
    {
        /**
         * @var Database
         * @var Input    $input
         */
        $db = $this->framework->createInstance(Database::class);
        $input = $this->framework->getAdapter(Input::class);

        $id = $input->get('id');
        $field = $dc->inputName = $input->post('name');

        // Handle the keys in "edit multiple" mode
        if ('editAll' === $input->get('act')) {
            $id = \preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $field);
            $field = \preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $field);
        }

        $dc->field = $field;

        // The field does not exist
        if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field])) {
            $this->logger->log(
                LogLevel::ERROR,
                \sprintf('Field "%s" does not exist in DCA "%s"', $field, $dc->table),
                ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
            );

            throw new BadRequestHttpException('Bad request');
        }

        $row = null;
        $value = null;

        // Load the value
        if ('overrideAll' !== $input->get('act') && $id > 0 && $db->tableExists($dc->table)) {
            $row = $db->prepare('SELECT * FROM '.$dc->table.' WHERE id=?')->execute($id);

            // The record does not exist
            if ($row->numRows < 1) {
                $this->logger->log(
                    LogLevel::ERROR,
                    \sprintf('A record with the ID "%s" does not exist in table "%s"', $id, $dc->table),
                    ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
                );

                throw new BadRequestHttpException('Bad request');
            }

            $value = $row->$field;
            $dc->activeRecord = $row;
        }

        // Call the load_callback
        if (\is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['load_callback'])) {
            /** @var System $systemAdapter */
            $systemAdapter = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['load_callback'] as $callback) {
                if (\is_array($callback)) {
                    $value = $systemAdapter->importStatic($callback[0])->{$callback[1]}($value, $dc);
                } elseif (\is_callable($callback)) {
                    $value = $callback($value, $dc);
                }
            }
        }

        // Set the new value
        $value = $input->post('value', true);

        // Convert the selected values
        if ($value) {
            /** @var StringUtil $stringUtilAdapter */
            $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
            $value = $stringUtilAdapter->trimsplit("\t", $value);
            $value = \serialize($value);
        }

        /** @var NewsAreaOfApplicationPickerWidget $strClass */
        $strClass = $GLOBALS['BE_FFL']['newsAreaOfApplicationPicker'];

        /** @var NewsAreaOfApplicationPickerWidget $objWidget */
        $objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$field], $dc->inputName, $value, $field, $dc->table, $dc));

        throw new ResponseException(new Response($objWidget->generate()));
    }



    /**
     * Reload the news categories widget.
     *
     * @param DataContainer $dc
     */
    private function reloadNewsUsageWidget(DataContainer $dc)
    {
        /**
         * @var Database
         * @var Input    $input
         */
        $db = $this->framework->createInstance(Database::class);
        $input = $this->framework->getAdapter(Input::class);

        $id = $input->get('id');
        $field = $dc->inputName = $input->post('name');

        // Handle the keys in "edit multiple" mode
        if ('editAll' === $input->get('act')) {
            $id = \preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $field);
            $field = \preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $field);
        }

        $dc->field = $field;

        // The field does not exist
        if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field])) {
            $this->logger->log(
                LogLevel::ERROR,
                \sprintf('Field "%s" does not exist in DCA "%s"', $field, $dc->table),
                ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
            );

            throw new BadRequestHttpException('Bad request');
        }

        $row = null;
        $value = null;

        // Load the value
        if ('overrideAll' !== $input->get('act') && $id > 0 && $db->tableExists($dc->table)) {
            $row = $db->prepare('SELECT * FROM '.$dc->table.' WHERE id=?')->execute($id);

            // The record does not exist
            if ($row->numRows < 1) {
                $this->logger->log(
                    LogLevel::ERROR,
                    \sprintf('A record with the ID "%s" does not exist in table "%s"', $id, $dc->table),
                    ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
                );

                throw new BadRequestHttpException('Bad request');
            }

            $value = $row->$field;
            $dc->activeRecord = $row;
        }

        // Call the load_callback
        if (\is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['load_callback'])) {
            /** @var System $systemAdapter */
            $systemAdapter = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['load_callback'] as $callback) {
                if (\is_array($callback)) {
                    $value = $systemAdapter->importStatic($callback[0])->{$callback[1]}($value, $dc);
                } elseif (\is_callable($callback)) {
                    $value = $callback($value, $dc);
                }
            }
        }

        // Set the new value
        $value = $input->post('value', true);

        // Convert the selected values
        if ($value) {
            /** @var StringUtil $stringUtilAdapter */
            $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
            $value = $stringUtilAdapter->trimsplit("\t", $value);
            $value = \serialize($value);
        }

        /** @var NewsUsagePickerWidget $strClass */
        $strClass = $GLOBALS['BE_FFL']['newsUsagePicker'];

        /** @var NewsUsagePickerWidget $objWidget */
        $objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$field], $dc->inputName, $value, $field, $dc->table, $dc));

        throw new ResponseException(new Response($objWidget->generate()));
    }



    /**
     * Convert a string to a response object
     *
     * @param string $str
     *
     * @return Response
     */
    protected function convertToResponse($str)
    {
        return new Response(Controller::replaceOldBePaths($str));
    }
}
