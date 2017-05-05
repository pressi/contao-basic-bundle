<?php
/**********************************************************************
 *
 * (c) 2015 Stephan Preßl <mail@stephanpressl.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by Stephan Preßl
 *
 **********************************************************************/

namespace IIDO\BasicBundle\Helper;

/**
 * Description
 *
 */
class ImageHelper extends \Backend
{

	/**
	 * Return the image array
	 * @param $multiSRC
     * @param $orderSRC
	 * @return array
	 */
	static public function getMultipleImages( $multiSRC, $orderSRC )
	{
        global $objPage;

		$arrImages  = array();
		$noGallery  = FALSE;
		$objFiles   = FALSE;

        $multiSRC = deserialize($multiSRC);

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
						'caption'   => $arrMeta['caption']
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
							'caption'   => $arrMeta['caption']
						);

						$auxDate[] = $objFile->mtime;
					}
				}
			}

            if ($orderSRC != '')
            {
                $tmp = deserialize($orderSRC);

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
