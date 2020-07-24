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
use Contao\StringUtil;
use IIDO\BasicBundle\Config\BundleConfig;


class ImageHelper
{

    public static function getImage( $imageSRC, $arrSize = array(), $addDefaultAttr = false, $defaultImageObject = '', $strAttributes = '' ): string
    {
        $assetImage = '';

        if( !$imageSRC instanceof \FilesModel )
        {
            if( preg_match('/assets\/images\//', $imageSRC) )
            {
                $assetImage = $imageSRC;

                if( $defaultImageObject )
                {
                    $imageSRC = $defaultImageObject;
                }
            }

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



    /**
     * Return the image array
     * @param $multiSRC
     * @param $orderSRC
     * @return array
     */
    static public function getMultipleImages( $multiSRC, $orderSRC = '' )
    {
        global $objPage;

        $arrImages  = array();
        $noGallery  = FALSE;
        $objFiles   = FALSE;

        $multiSRC = \StringUtil::deserialize($multiSRC);

        if( is_array($multiSRC) || !empty($multiSRC) )
        {
            $objFiles = \FilesModel::findMultipleByUuids( $multiSRC );

            if ($objFiles === null)
            {
                if (!\Validator::isUuid($multiSRC[0]) )
                {
                    $noGallery = TRUE;
                }

                $noGallery = TRUE;
            }
        }

        if( !$noGallery && $objFiles )
        {
            // Get all images
            while ($objFiles->next())
            {
                // Continue if the files has been processed or does not exist
                if (isset($arrImages[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path))
                {
                    continue;
                }

                // Single files
                if ($objFiles->type == 'file')
                {
                    $objFile = new \File($objFiles->path, true);

                    if (!$objFile->isImage)
                    {
                        continue;
                    }

                    $arrMeta = \Frontend::getMetaData($objFiles->meta, $objPage->language);

                    if (empty($arrMeta))
                    {
                        if ($objPage->rootFallbackLanguage !== null)
                        {
                            $arrMeta = \Frontend::getMetaData($objFiles->meta, $objPage->rootFallbackLanguage);
                        }
                    }

                    // Use the file name as title if none is given
                    if ($arrMeta['title'] == '')
                    {
                        $arrMeta['title'] = specialchars($objFile->basename);
                    }

                    // Add the image
                    $arrImages[$objFiles->path] = array
                    (
                        'id'        => $objFiles->id,
                        'uuid'      => $objFiles->uuid,
                        'name'      => $objFile->basename,
                        'singleSRC' => $objFiles->path,
                        'alt'       => $arrMeta['title'],
                        'imageUrl'  => $arrMeta['link'],
                        'caption'   => $arrMeta['caption'],
                        'meta'      => $arrMeta,
                        'model'     => $objFiles->current()
                    );

                    $auxDate[] = $objFile->mtime;
                }

                // Folders
                else
                {
                    $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

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

                        $objFile = new \File($objSubfiles->path, true);

                        if (!$objFile->isImage)
                        {
                            continue;
                        }

                        $arrMeta = \Frontend::getMetaData($objSubfiles->meta, $objPage->language);

                        if (empty($arrMeta))
                        {
                            if ($objPage->rootFallbackLanguage !== null)
                            {
                                $arrMeta = \Frontend::getMetaData($objSubfiles->meta, $objPage->rootFallbackLanguage);
                            }
                        }

                        // Use the file name as title if none is given
                        if ($arrMeta['title'] == '')
                        {
                            $arrMeta['title'] = specialchars($objFile->basename);
                        }

                        // Add the image
                        $arrImages[$objSubfiles->path] = array
                        (
                            'id'        => $objSubfiles->id,
                            'uuid'      => $objSubfiles->uuid,
                            'name'      => $objFile->basename,
                            'singleSRC' => $objSubfiles->path,
                            'alt'       => $arrMeta['title'],
                            'imageUrl'  => $arrMeta['link'],
                            'caption'   => $arrMeta['caption'],
                            'meta'      => $arrMeta,
                            'model'     => $objFiles->current()
                        );

                        $auxDate[] = $objFile->mtime;
                    }
                }
            }

            if ($orderSRC != '')
            {
                $tmp = StringUtil::deserialize($orderSRC, true);

                if (!empty($tmp) && is_array($tmp))
                {
                    // Remove all values
                    $arrOrder = array_map(function(){}, array_flip($tmp));

                    // Move the matching elements to their position in $arrOrder
                    foreach ($arrImages as $k=>$v)
                    {
                        if (array_key_exists($v['uuid'], $arrOrder))
                        {
                            $arrOrder[$v['uuid']] = $v;
                            unset($arrImages[$k]);
                        }
                    }

                    // Append the left-over images at the end
                    if (!empty($arrImages))
                    {
                        $arrOrder = array_merge($arrOrder, array_values($arrImages));
                    }

                    // Remove empty (unreplaced) entries
                    $arrImages = array_values(array_filter($arrOrder));
                    unset($arrOrder);
                }
            }
        }

        return $arrImages;
    }
}