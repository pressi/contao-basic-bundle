<?php


namespace IIDO\BasicBundle\Helper;



class StylesHelper
{

    public static function getArticleStyles( $objArticle, $onlyOwnStyles = false, $returnAsArray = false, $writeInFile = true, $selector = '' )
    {
        $arrOwnStyles       = array();
        $addBackgroundImage = ($objArticle->bgImage);
        $arrBackgroundSize  = \StringUtil::deserialize($objArticle->bgSize, true);

        if( $addBackgroundImage && is_array($arrBackgroundSize) && strlen($arrBackgroundSize[2]) && $arrBackgroundSize[2] != '-' )
        {
            $bgSize = $arrBackgroundSize[2];

            if( $arrBackgroundSize[2] == 'own' )
            {
                unset($arrBackgroundSize[2]);
                $bgSize = implode(" ", $arrBackgroundSize);
            }

            $bgSize = preg_replace('/^_/', '', $bgSize);

            $arrOwnStyles[] = '-webkit-background-size:' . $bgSize . ';-moz-background-size:' . $bgSize . ';-o-background-size:' . $bgSize . ';background-size:' . $bgSize . ';';
        }

        if( $addBackgroundImage && $objArticle->bgAttachment )
        {
            $arrOwnStyles[] = 'background-attachment:' . $objArticle->bgAttachment . ';';
        }

        if( $onlyOwnStyles )
        {
            return $returnAsArray ? $onlyOwnStyles : implode("", $arrOwnStyles);
        }

        $rootDir    = BasicHelper::getRootDir();
        $strImage   = '';

        if( $addBackgroundImage )
        {
            $objImage   = \FilesModel::findByUuid( $objArticle->bgImage );

            if( $objImage && file_exists($rootDir . '/' . $objImage->path) )
            {
                $strImage = $objImage->path;
            }
        }

        $arrStyles = array
        (
            'background'        => TRUE,
            'bgcolor'           => ColorHelper::renderColorConfig( $objArticle->bgColor ),

            'bgimage'           => $strImage,
            'bgrepeat'          => $addBackgroundImage ? $objArticle->bgRepeat          : '',
            'bgposition'        => $addBackgroundImage ? $objArticle->bgPosition        : '',

            'gradientAngle'     => $objArticle->gradientAngle,
            'gradientColors'    => $objArticle->gradientColors
        );

        if( count($arrOwnStyles) )
        {
            $arrStyles['own'] = implode("", $arrOwnStyles);
        }

        if( strlen($selector) )
        {
            $arrStyles['selector'] = $selector;
        }

        if( !$returnAsArray )
        {
            $objStyleSheets     = new \StyleSheets();
            $arrStyles          = $objStyleSheets->compileDefinition($arrStyles, $writeInFile);
        }

        return $arrStyles;
    }

}