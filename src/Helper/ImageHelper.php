<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\FilesModel;
use IIDO\BasicBundle\Config\BundleConfig;


class ImageHelper
{

    public static function getImage( $imageSRC, $arrSize = array(), $addDefaultAttr = false, $defaultImageObject = '', $strAttributes = '' ): string
    {
        $assetImage = '';

        if( preg_match('/assets\/images\//', $imageSRC) )
        {
            $assetImage = $imageSRC;

            if( $defaultImageObject )
            {
                $imageSRC = $defaultImageObject;
            }
        }

        if( !$imageSRC instanceof \FilesModel )
        {
            $imageSRC = str_replace('%20', ' ', $imageSRC);
            $objImage = FilesModel::findByPk( $imageSRC );

            if( !$objImage )
            {
                $objImage = FilesModel::findByPath( $imageSRC );
            }
        }
        else
        {
            $objImage = $imageSRC;
        }

        if( $objImage )
        {
            $arrMeta    = \Frontend::getMetaData($objImage->meta, BasicHelper::getLanguage());
            $attributes = $strAttributes;

            if( $addDefaultAttr )
            {
                $attributes .= ' data-default="' . ($assetImage?:$objImage->path) . '"';
            }

            $script = '';

            list($imagePath, $srcImagePath) = $assetImage ? array($assetImage, '') : self::getImagePath( $objImage, $arrSize, true );

            if( $srcImagePath )
            {
                $attributes .= 'srcset="' . self::renderImagePath($srcImagePath) . '"';

                $script = '<script>window.respimage&&window.respimage({elements:[document.images[document.images.length-1]]})</script>';
            }

            return \Image::getHtml( $imagePath, $arrMeta['alt'], trim($attributes) ) . $script;
        }

        return '';
    }



    public static function getImagePath( $imageSRC, $arrSize = array(), $returnArray = false )
    {
        if( $imageSRC instanceof \FilesModel )
        {
            $objImage = $imageSRC;
        }
        else
        {
            $objImage = \FilesModel::findByPk( $imageSRC );
        }

        if( !$objImage )
        {
            $imageSRC = preg_replace('/%20/', ' ', $imageSRC);
            $objImage = \FilesModel::findByPath( $imageSRC );
        }

        if( $objImage )
        {
            if( count($arrSize) )
            {
                $objFactory = \System::getContainer()->get('contao.image.image_factory');
                /* @var $objFactory \Contao\CoreBundle\Image\ImageFactory */

                $src = $objFactory->create( BasicHelper::getRootDir( true ) . $objImage->path, $arrSize )->getUrl( BasicHelper::getRootDir() );

                return $returnArray ? array(self::convertImage( $src ), $src) : self::convertImage( $src );
            }

            return $returnArray ? array(self::convertImage( $objImage->path ), $objImage->path) : $objImage->path;
        }

        return $returnArray ? array(false, '') : false;
    }



    protected static function convertImage( $imagePath )
    {
        if( BundleConfig::isActiveBundle('postyou/contao-webp-bundle') )
        {
            if( \Config::get('useWebP') && WebPHelper::hasWebPSupport() )
            {
                if( !preg_match('/^assets\//', $imagePath) )
                {
                    self::copyImageToWebPPath( $imagePath );
                }

                $imagePath = WebPHelper::getWebPImage( $imagePath );
            }
        }

        return $imagePath;
    }



    public static function copyImageToWebPPath( $imagePath )
    {
        $realIimagePath = $imagePath;

        $arrPath    = explode("/", $imagePath);
        $imageName  = array_pop( $arrPath );

        $imagePath  = 'assets/images/webp/' . $imageName;

        if( !is_dir( TL_ROOT . '/assets/images/webp') )
        {
            if( !mkdir( $concurrentDirectory = TL_ROOT . '/assets/images/webp' ) && !is_dir( $concurrentDirectory ) )
            {
                throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $concurrentDirectory ) );
            }
        }

        copy( $realIimagePath, $imagePath );

        return $imagePath;
    }



    public static function renderImagePath( $strPath )
    {
        return preg_replace('/ /', '%20', $strPath);
    }



    public static function getImageMetaFromPath( $singleSRC )
    {
        $objImage = \FilesModel::findByPath( $singleSRC );

        if( $objImage )
        {
            $arrMeta = \StringUtil::deserialize($objImage->meta, TRUE);

            return $arrMeta[ BasicHelper::getLanguage() ];
        }

        return array();
    }
}