<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Controller;
use Contao\FilesModel;
use Contao\Input;
use MadeYourDay\RockSolidSlider\Module\Slider;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Fixtures\App\Document\Content;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class RSCEHelper extends \Frontend
{

    public static function getNewTableConfig($label, $contentCategory = 'texts', $standardFields = array('cssID'), $types = array('content'), $arrFields = array())
    {
        return array
        (
            'label'             => self::renderLabel( $label ),
            'types'             => $types,
            'contentCategory'   => $contentCategory,
            'standardFields'    => $standardFields,
            'fields'            => $arrFields
        );
    }



    public static function getStandardFieldConfig( $label, $eval = [] )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'standardField',
            'eval'          => $eval
        );
    }



    /**
     * Get Imagefield Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getImageFieldConfig( $label, $eval = [] )
    {
        $arrConfig = array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'fileTree',
            'eval'          => array
            (
                'filesOnly'         => true,
                'fieldType'         => 'radio',
                'tl_class'          => 'clr w50 hauto',
            )
        );

        if( $eval && count($eval) )
        {
            $arrConfig['eval'] = array_merge($arrConfig['eval'], $eval);
        }

        return $arrConfig;
    }



    public static function getTrblFieldConfig( $label, $options = array() )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'trbl',
            'options'       => count($options) ? $options : $GLOBALS['TL_CSS_UNITS'],
            'eval'          => array
            (
                'tl_class'      => 'w50'
            )
        );
    }



    public static function getVideoFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'fileTree',
            'eval'          => array
            (
                'multiple'          => true,
                'files'             => true,
                'fieldType'         => 'checkbox',
                'tl_class'          => 'clr w50 hauto',
            )
        );
    }



    /**
     * Get MultiSRC (Image Gallery) Config
     *
     * @param string|array $label
     * @param string       $orderFieldName
     *
     * @return array
     */
    public static function getImagesFieldConfig( $label, $orderFieldName = 'orderSRC' )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'fileTree',
            'eval'          => array
            (
                'multiple'          => true,
                'fieldType'         => 'checkbox',
                'orderField'        => $orderFieldName,
                'files'             => true,
                'isGallery'         => true,
                'extensions'        => \Config::get('validImageTypes'),
                'tl_class'          => 'clr',
            )
        );
    }



//    public static function getImagesOrderFieldConfig( $label )
//    {
//        return array
//        (
//            'label'         => self::renderLabel( $label ),
//        );
//    }



    /**
     * Get Textareafield Config
     *
     * @param string|array $label
     * @param bool         $rte
     *
     * @return array
     */
    public static function getTextareaConfig( $label, $rte = true)
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'textarea',
            'eval'          => array
            (
                'helpwizard'        => true,
                'rte'               => 'tinyMCE',
                'tl_class'          => 'clr'
            ),
            'explanation'   => 'insertTags'
        );
    }



    /**
     * Get Textfield Config
     *
     * @param string|array $label
     * @param bool         $newLine
     * @param bool         $isLong
     * @param array        $eval
     *
     * @return array
     */
    public static function getTextFieldConfig( $label, $newLine = false, $isLong = false, array $eval = array() )
    {
        $defaultEval = array
        (
            'tl_class'      => ($isLong ? 'long' : 'w50') . ($newLine ? ' clr': '')
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => $defaultEval
        );
    }



    /**
     * Get Double Textfield Config
     * @param string|array $label
     * @param bool         $newLine
     * @param bool         $isLong
     *
     * @return array
     */
    public static function getDoubleTextFieldConfig( $label, $newLine = false, $isLong = false )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => array
            (
                'maxlength'         => 255,
                'multiple'          => true,
                'size'              => 2,
                'tl_class'          => ($isLong ? 'long' : 'w50') . ($newLine ? ' clr': '')
            )
        );
    }



    /**
     * Get Colorfield Config
     *
     * @param string|array $label
     * @param bool         $newLine
     *
     * @return array
     */
    public static function getColorFieldConfig( $label, $newLine = false )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => array
            (
                'maxlength'         => 64,
                'multiple'          => true,
                'size'              => 2,
                'colorpicker'       => true,
                'isHexColor'        => true,
                'decodeEntities'    => true,
                'tl_class'          => 'w50 wizard' . ($newLine ? ' clr': '')
            ),
        );
    }



    /**
     * Get Selectfield Config
     *
     * @param string|array $label
     * @param array        $arrOptions
     * @param bool         $includeBlank
     * @param array        $eval
     * @param string       $default
     * @param string       $reference
     *
     * @return array
     */
    public static function getSelectFieldConfig( $label, $arrOptions, $includeBlank = false, array $eval = array(), $default = '', $reference = '' )
    {
        $arrConfig = array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'select',
            'options'       => $arrOptions,
            'eval'          => array
            (
                'includeBlankOption'    => $includeBlank,
                'tl_class'              => 'w50'
            )
        );

        $arrConfig['eval'] = array_merge($arrConfig['eval'], $eval);

        if( strlen($default) )
        {
            $arrConfig['default'] = $default;
        }

        if( $reference )
        {
            $arrConfig['reference'] = $reference;
        }

        return $arrConfig;
    }



    /**
     * Get Checkboxfield Config
     *
     * @param string|array $label
     * @param array        $arrOptions
     * @param bool         $submitOnChange
     * @param bool         $clear
     *
     * @return array
     */
    public static function getCheckboxFieldConfig( $label, $arrOptions = array(), $submitOnChange = false, $clear = false )
    {
        $arrConfig = array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'checkbox',
            'eval'          => array
            (
                'tl_class'          => ($clear ?  'clr ' : '') . 'w50'
            )
        );

        if( count($arrOptions) )
        {
            $arrConfig['options']           = $arrOptions;
            $arrConfig['eval']['multiple']  = true;
        }
        else
        {
            $arrConfig['eval']['tl_class']  = $arrConfig['eval']['tl_class'] . ' m12';
        }

        if( $submitOnChange )
        {
            $arrConfig['eval']['submitOnChange']  = true;
        }

        return $arrConfig;
    }



    /**
     * Get Linkfield Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getLinkFieldConfig( $label, $class = '' )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => array
            (
                'rgxp'              => 'url',
                'decodeEntities'    => true,
                'maxlength'         => 255,
                'dcaPicker'         => true,
                'tl_class'          => 'w50 wizard ' . $class
            )
        );
    }



    /**
     * Get NewsPicker Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getNewsPickerFieldConfig( $label )
    {
        $arrNewsOptions     = array();
        $objArchive         = \NewsArchiveModel::findAll();

        if( $objArchive )
        {
            while( $objArchive->next() )
            {
                $objNews = \NewsModel::findBy('pid', $objArchive->id);

                if( $objNews )
                {
                    $arrNewsOptions[ $objArchive->title ] = array();

                    while( $objNews->next() )
                    {
                        $arrNewsOptions[ $objArchive->title ][ $objNews->id ] = $objNews->headline . '(' . $objNews->id . ')';
                    }
                }
            }
        }

        if( count($arrNewsOptions) )
        {
            $arrNewsDefaultOptions = array
            (
                'Allgemein' => array
                (
                    'latest'        => 'Immer aktuellste News anzeigen'
                )
            );


            $arrNewsOptions = array_merge($arrNewsDefaultOptions, $arrNewsOptions);
        }

        $arrConfig = array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'select',
            'options'       => $arrNewsOptions,
            'eval'          => array
            (
                'includeBlankOption'    => true,
                'tl_class'              => 'w50'
            )
        );

        if( count($arrNewsOptions) === 0 )
        {
            $arrConfig['eval']['blankOptionLabel'] = 'Keine News vorhanden!';
        }
        else
        {
            $arrConfig['eval']['blankOptionLabel'] = 'News wählen';
        }

        return $arrConfig;
    }



    /**
     * Get ImageSize Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getImageSizeFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'imageSize',
            'reference'     => &$GLOBALS['TL_LANG']['MSC'],
            'eval'          => array
            (
                'rgxp'               => 'natural',
                'includeBlankOption' => true,
                'nospace'            => true,
                'helpwizard'         => true,
                'tl_class'           => 'clr w50'
            ),
            'options_callback' => function ()
            {
                return \System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(\BackendUser::getInstance());
            },
        );
    }



    /**
     * Get ImageAlign Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getImageAlignFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'default'       => 'above',
            'inputType'     => 'radioTable',
            'options'       => array('above', 'left', 'right', 'below'),
            'eval'          => array
            (
                'cols'          => 4,
                'tl_class'      => 'w50'
            ),
            'reference'     => &$GLOBALS['TL_LANG']['MSC'],
        );
    }



    /**
     * Get PagePicker Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getPagePickerFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'pageTree',
            'foreignKey'    => 'tl_page.title',
            'eval'          => array
            (
                'fieldType'     => 'radio'
            ),
            'relation'      => array
            (
                'type'          => 'hasOne',
                'load'          => 'lazy'
            )
        );
    }


    /**
     * Get Input Unit Field Config
     *
     * @param string|array $label
     * @param array        $arrUnits
     * @param bool         $newLine
     * @param array        $eval
     *
     * @return array
     */
    public static function getUnitFieldConfig( $label, array $arrUnits = array(), $newLine = false, array $eval = array() )
    {
        if( !count($arrUnits) )
        {
            $arrUnits = $GLOBALS['TL_CSS_UNITS'];
        }

        $defaultEval = array
        (
            'maxlength'     => 200,
            'tl_class'      => ($newLine ?  'clr ' : '') . 'w50'
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'inputUnit',
            'options'       => $arrUnits,
            'eval'          => $defaultEval
        );
    }



    public static function getHeadlineFieldConfig( $label, $newLine = true, $eval = array() )
    {
        return self::getUnitFieldConfig( $label, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], $newLine, $eval);
    }



    /**
     * Render Field Label
     *
     * @param string $strLabel
     *
     * @return array
     */
    protected static function renderLabel( $strLabel )
    {
        if( !is_array($strLabel) )
        {
            $strLabel = array($strLabel, '');
        }

        return $strLabel;
    }



    /**
     * Get Group (Legend) Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getGroupConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'group',
        );
    }



    /**
     * Get Picture
     *
     * @param object $objClass
     * @param        $image
     * @param array  $arrSize
     *
     * @return mixed
     */
    public static function getPicture( &$objClass, $image, $arrSize = array() )
    {
        return $objClass->getImageObject($image, $arrSize);
    }



    public static function getPictureSRC( &$objClass, $image, $arrSize = array() )
    {
        $objPicture = self::getPicture($objClass, $image, $arrSize);

        return $objPicture->src;
    }



    public static function getImageTag( $image, $arrSize = array(), &$objClass = false, $returnPath = false, $floating = '', $showCaption = false, array $options = [] )
    {
        $strContent = '';

        if( $image )
        {

            if( is_string($image) && $objClass )
            {
                $image      = $objClass->getImageObject($image, $arrSize);
            }

            if( is_object($image) && $image instanceof FilesModel )
            {
                $image = $objClass->getImageObject($image->uuid, $arrSize);
            }

            if( $returnPath )
            {
                $strContent = $image->picture['img']['singleSRC']?:$image->picture['img']['src'];
            }
            else
            {
//                $image->useAsBG = true;
//                $image->insertImage = true;

                $strContent = $objClass->insert('image', (array) $image);


//                $floatClass = '';
//
//                if( $floating )
//                {
//                    $floatClass = ' float_' . $floating;
//                }
//
//                $strCaption = '';
//                if( $showCaption )
//                {
////                    $arrMeta = \Contao\StringUtil::deserialize( $image->meta, true );
//
//                    if( $image->caption )
//                    {
////                        $arrLangMeta = $arrMeta[ \IIDO\BasicBundle\Helper\BasicHelper::getLanguage() ];
//
////                        $strCaption = '<figcaption class="caption">' . $arrLangMeta['caption'] . '</figcaption>';
//                        $strCaption = '<figcaption class="caption">' . ContentHelper::renderText( $image->caption, true ) . '</figcaption>';
//                    }
//                }
//
//                $strContent = '<figure class="image_container' . $floatClass . '"><img src="' . trim($image->src?:$image->picture['img']['src']) . '" alt="' .  trim($image->alt?:$image->picture['alt']) . '"' . $image->imgSize . '>' . $strCaption . '</figure>';
            }
        }

        return $strContent;
    }



    public static function getImages( $arrImages )
    {
        $images     = array();

        if( $arrImages )
        {
            $arrImages  = \StringUtil::deserialize( $arrImages, TRUE );

            if( is_array($arrImages) && count($arrImages) )
            {
                $objFiles = \FilesModel::findMultipleByUuids( $arrImages );

                if( $objFiles && $objFiles->count() )
                {
                    while ($objFiles->next())
                    {
                        // Continue if the files has been processed or does not exist
                        if (isset($images[$objFiles->path]) || !file_exists(BasicHelper::getRootDir() . '/' . $objFiles->path))
                        {
                            continue;
                        }

                        // Single files
                        if ($objFiles->type == 'file')
                        {
                            $objFile = new \File($objFiles->path);

                            if (!$objFile->isImage)
                            {
                                continue;
                            }

                            // Add the image
                            $images[$objFiles->path] = array
                            (
                                'id'         => $objFiles->id,
                                'uuid'       => $objFiles->uuid,
                                'name'       => $objFile->basename,
                                'singleSRC'  => $objFiles->path,
                                'title'      => \StringUtil::specialchars($objFile->basename),
                                'filesModel' => $objFiles->current()
                            );

                            $auxDate[] = $objFile->mtime;
                        }

                        // Folders
                        else
                        {
                            $objSubfiles = \FilesModel::findByPid($objFiles->uuid, array('order' => 'name'));

                            if ($objSubfiles === null)
                            {
                                continue;
                            }

                            while ($objSubfiles->next())
                            {
                                // Skip subfolders
                                if ($objSubfiles->type == 'folder')
                                {
                                    continue;
                                }

                                $objFile = new \File($objSubfiles->path);

                                if (!$objFile->isImage)
                                {
                                    continue;
                                }

                                // Add the image
                                $images[$objSubfiles->path] = array
                                (
                                    'id'         => $objSubfiles->id,
                                    'uuid'       => $objSubfiles->uuid,
                                    'name'       => $objFile->basename,
                                    'singleSRC'  => $objSubfiles->path,
                                    'title'      => \StringUtil::specialchars($objFile->basename),
                                    'filesModel' => $objSubfiles->current()
                                );

                                $auxDate[] = $objFile->mtime;
                            }
                        }
                    }
                }
            }
        }

        return $images;
    }



    public static function getImagesArray( $imagesField, $orderField, $likeFancybox = false )
    {
        $arrImages = ImageHelper::getMultipleImages( $imagesField, $orderField );

        if( $likeFancybox )
        {
            foreach($arrImages as $num => $arrImage)
            {
                $strTitle = '';

                if( !preg_match('/(jpg|jpeg|png|gif|tif|tiff)$/', strtolower($arrImage['meta'][ BasicHelper::getLanguage() ]['title'])) )
                {
                    $strTitle = $arrImage['meta'][ BasicHelper::getLanguage() ]['title'];
                }

                unset( $arrImages[ $num ] );

                $arrImages[ $num ] = array();
                $arrImages[ $num ]['src']   = $arrImage['singleSRC'];

                if( $strTitle )
                {
                    $arrImages[ $num ]['opts']  = array
                    (
                        'caption'       => $strTitle
                    );
                }
            }
        }

        return $arrImages;
    }



    public static function addButtonStylesConfig( $fieldName, &$arrConfig, $fieldNameIsLegend = false, $append = true )
    {
        Controller::loadLanguageFile("tl_content");

        $intOffset = array_search( $fieldName, array_keys($arrConfig['fields']) );

        array_insert($arrConfig['fields'], ($intOffset + 1), array
        (
            'buttonStyle'   => self::getSelectFieldConfig('Button-Stil', $GLOBALS['TL_LANG']['tl_content']['options']['buttonStyle'], false, array('tl_class'=>'clr w50')),
            'buttonType'    => self::getSelectFieldConfig('Button-Typ', $GLOBALS['TL_LANG']['tl_content']['options']['buttonType']),
            'buttonSize'    => self::getSelectFieldConfig('Button-Größe', $GLOBALS['TL_LANG']['tl_content']['options']['buttonSize'], false, array(), 'm')
        ));

        return $arrConfig;
    }



    public static function getSlider( $sliderID, $configID )
    {
//        $objSlider = SliderModel::findByPk( $sliderID );

        $objElement = new Slider( \ModuleModel::findByPk($configID) );

        $objElement->rsts_id = $sliderID;

        return $objElement->generate();

//        global $objPage;
//
//        $slides     = array();
//        $multiSRC   = array();
//        $orderSRC   = array();
//        $objSlider  = SliderModel::findByPk( $sliderID );
//        $objConfig  = SliderModel::findByPk( $configID );
//
//        if( $objSlider->type === 'image' )
//        {
//            $multiSRC = \StringUtil::deserialize($objSlider->multiSRC);
//            $orderSRC = $objSlider->orderSRC;
//        }
//        else
//        {
//            $objSlides = SlideModel::findPublishedByPid( $sliderID );
//
//            if( $objSlides )
//            {
//                $pids       = array();
//                $idIndexes  = array();
//
//                while( $objSlides->next() )
//                {
//                    $slide = $objSlides->row();
//                    $slide['text'] = '';
//                    if ($slide['type'] === 'content') {
//                        $pids[] = $slide['id'];
//                        $idIndexes[(int)$slide['id']] = count($slides);
//                    }
//
//                    if (
//                        in_array($slide['type'], array('image', 'video')) &&
//                        trim($slide['singleSRC']) &&
//                        ($file = \FilesModel::findByUuid($slide['singleSRC'])) &&
//                        ($fileObject = new \File($file->path, true)) &&
//                        ($fileObject->isGdImage || $fileObject->isImage)
//                    ) {
//                        $meta = \Frontend::getMetaData($file->meta, $objPage->language);
//                        $slide['image'] = new \stdClass;
//                        \Controller::addImageToTemplate($slide['image'], array(
//                            'id' => $file->id,
//                            'name' => $fileObject->basename,
//                            'singleSRC' => $file->path,
//                            'alt' => $meta['title'],
//                            'imageUrl' => $meta['link'],
//                            'caption' => $meta['caption'],
//                            'size' => isset($objConfig->imgSize) ? $objConfig->imgSize : $objConfig->size,
//                        ));
//                    }
//
//                    if ($slide['type'] === 'video' && $slide['videoURL'] && empty($slide['image'])) {
//                        $slide['image'] = new \stdClass;
//                        if (preg_match(
//                            '(^
//						https?://  # http or https
//						(?:
//							www\\.youtube\\.com/(?:watch\\?v=|v/|embed/)  # Different URL formats
//							| youtu\\.be/  # Short YouTube domain
//						)
//						([0-9a-z_\\-]{11})  # YouTube ID
//						(?:$|&|/)  # End or separator
//					)ix',
//                            html_entity_decode($slide['videoURL']), $matches)
//                        ) {
//                            $video = $matches[1];
//                            $slide['image']->src = '//img.youtube.com/vi/' . $video . '/0.jpg';
//                        }
//                        else {
//                            // Grey dummy image
//                            $slide['image']->src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAJCAMAAAAM9FwAAAAAA1BMVEXGxsbd/8BlAAAAFUlEQVR42s3BAQEAAACAkP6vdiO6AgCZAAG/wrlvAAAAAElFTkSuQmCC';
//                        }
//                        $slide['image']->imgSize = '';
//                        $slide['image']->alt = '';
//                        $slide['image']->picture = array(
//                            'img' => array('src' => $slide['image']->src, 'srcset' => $slide['image']->src),
//                            'sources' => array(),
//                        );
//                    }
//
//                    if ($slide['type'] !== 'video' && $slide['videoURL']) {
//                        $slide['videoURL'] = '';
//                    }
//
//                    if ($slide['type'] === 'video' && $slide['videos']) {
//                        $videoFiles = deserialize($slide['videos'], true);
//                        $videoFiles = \FilesModel::findMultipleByUuids($videoFiles);
//                        $videos = array();
//                        foreach ($videoFiles as $file) {
//                            $videos[] = $file;
//                        }
//                        $slide['videos'] = $videos;
//                    }
//                    else {
//                        $slide['videos'] = null;
//                    }
//
//                    if (
//                        trim($slide['backgroundImage']) &&
//                        ($file = \FilesModel::findByUuid($slide['backgroundImage'])) &&
//                        ($fileObject = new \File($file->path, true)) &&
//                        ($fileObject->isGdImage || $fileObject->isImage)
//                    ) {
//                        $meta = \Frontend::getMetaData($file->meta, $objPage->language);
//                        $slide['backgroundImage'] = new \stdClass;
//                        \Controller::addImageToTemplate($slide['backgroundImage'], array(
//                            'id' => $file->id,
//                            'name' => $fileObject->basename,
//                            'singleSRC' => $file->path,
//                            'alt' => $meta['title'],
//                            'imageUrl' => $meta['link'],
//                            'caption' => $meta['caption'],
//                            'size' => $slide['backgroundImageSize'],
//                        ));
//                    }
//                    else {
//                        $slide['backgroundImage'] = null;
//                    }
//
//                    if ($slide['backgroundVideos']) {
//                        $videoFiles = deserialize($slide['backgroundVideos'], true);
//                        $videoFiles = \FilesModel::findMultipleByUuids($videoFiles);
//                        $videos = array();
//                        foreach ($videoFiles as $file) {
//                            $videos[] = $file;
//                        }
//                        $slide['backgroundVideos'] = $videos;
//                    }
//
//                    if ($objConfig->rsts_navType === 'thumbs') {
//                        $slide['thumb'] = new \stdClass;
//                        if (
//                            trim($slide['thumbImage']) &&
//                            ($file = \FilesModel::findByUuid($slide['thumbImage'])) &&
//                            ($fileObject = new \File($file->path, true)) &&
//                            ($fileObject->isGdImage || $fileObject->isImage)
//                        ) {
//                            \Controller::addImageToTemplate($slide['thumb'], array(
//                                'id' => $file->id,
//                                'name' => $fileObject->basename,
//                                'singleSRC' => $file->path,
//                                'size' => $objConfig->rsts_thumbs_imgSize,
//                            ));
//                        }
//                        elseif (
//                            in_array($slide['type'], array('image', 'video')) &&
//                            trim($slide['singleSRC']) &&
//                            ($file = \FilesModel::findByUuid($slide['singleSRC'])) &&
//                            ($fileObject = new \File($file->path, true)) &&
//                            ($fileObject->isGdImage || $fileObject->isImage)
//                        ) {
//                            \Controller::addImageToTemplate($slide['thumb'], array(
//                                'id' => $file->id,
//                                'name' => $fileObject->basename,
//                                'singleSRC' => $file->path,
//                                'size' => $objConfig->rsts_thumbs_imgSize,
//                            ));
//                        }
//                        elseif (!empty($slide['image']->src)) {
//                            $slide['thumb'] = clone $slide['image'];
//                        }
//                        elseif (!empty($slide['backgroundImage']->src)) {
//                            $slide['thumb'] = clone $slide['backgroundImage'];
//                        }
//                    }
//
//                    $slides[] = $slide;
//
//                }
//
//                if (count($pids))
//                {
//                    $slideContents = \ContentModel::findPublishedByPidsAndTable($pids, SlideModel::getTable());
//                    if ($slideContents) {
//                        while ($slideContents->next()) {
//                            $slides[$idIndexes[(int)$slideContents->pid]]['text'] .= \Controller::getContentElement($slideContents->current());
//                        }
//                    }
//                }
//
//                echo "<pre>"; print_R( $slides ); exit;
//            }
//        }
    }



    public static function isFirstOfType( $strType, $intId, $intPid )
    {
        $objElements = \ContentModel::findBy(array('pid=?', 'ptable=?', 'invisible=?', 'type=?'), array($intPid, 'tl_article', '', $strType));

        if( $objElements )
        {
            if( $objElements->first()->id === $intId )
            {
                return true;
            }
        }

        return false;
    }



    public static function isLastOfType( $strType, $intId, $intPid )
    {
        $objElements = \ContentModel::findBy(array('pid=?', 'ptable=?', 'invisible=?', 'type=?'), array($intPid, 'tl_article', '', $strType));

        if( $objElements )
        {
            if( $objElements->last()->id === $intId )
            {
                return true;
            }
        }

        return false;
    }



    public static function getItemPageNum( $strType, $intId, $intPid, $perPage)
    {
        $pageNum        = 0;
        $counter        = 0;
        $objElements    = \ContentModel::findBy(array('pid=?', 'ptable=?', 'invisible=?', 'type=?'), array($intPid, 'tl_article', '', $strType));

        while( $objElements->next() )
        {
            $counter++;

            if( $counter > $perPage )
            {
                $counter = 1;
                $pageNum++;
            }

            if( $intId == $objElements->id )
            {
                break;
            }
        }

        return (string) $pageNum;
    }



    public static function getSortedImages( $object, $imageFieldName = 'multiSRC', $orderFieldName = 'orderSRC')
    {
        $images = \StringUtil::deserialize($object->$imageFieldName);
        $tmp    = \StringUtil::deserialize($object->$orderFieldName);

        if (!empty($tmp) && is_array($tmp))
        {
            // Remove all values
            $order = array_map(function(){}, array_flip($tmp));

            // Move the matching elements to their position in $order
            foreach ($images as $k => $v)
            {
                if (array_key_exists($v, $order))
                {
                    $order[$v] = $v;
                    unset($images[$k]);
                }
            }

            // Append the left-over images at the end
            if (!empty($images))
            {
                $order = array_merge($order, array_values($images));
            }

            // Remove empty (unreplaced) entries
            $images = array_values(array_filter($order));
        }

        return $images;
    }



    public static function getLayoutFields(): array
    {
        return ['layout_cond_width', 'layout_col_mobile', 'layout_col_tablet', 'layout_col_desktop', 'layout_col_wide', 'layout_align_mobile', 'layout_align_tablet', 'layout_align_desktop', 'layout_align_wide'];
    }



    public static function getAnimationFieldsConfig( $objElement = false ): array
    {
        $arrFields = [
            'animation_legend'      => self::getGroupConfig('Animation'),

//            'addAnimation'          => self::getCheckboxFieldConfig('Animation hinzufügen', [], true)
            'addAnimation'          => [ 'inputType' => 'standardField' ]
        ];

        if( !$objElement )
        {
            if( Input::get('act') === 'edit' && Input::get('id') )
            {
                $objElement = \ContentModel::findByPk( Input::get('id') );
            }
        }

        if( $objElement )
        {
//            $rsceData = json_decode($objElement->rsce_data, true);

            if( $objElement->addAnimation )
            {
//                $arrFields['animationType'] => [ 'inputType' => 'standardField' ]
//                ,animateRun,animationWait,animationOffset
            }
        }

        return $arrFields;
    }

}
