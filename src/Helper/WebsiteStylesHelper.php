<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use IIDO\BasicBundle\Model\WebsiteStyleModel;


class WebsiteStylesHelper
{

    public static function updateConfigFileRow( $projectAlias, $varName, $varValue )
    {
        $filePath = WebsiteStyleModel::getConfigFilePath( $projectAlias );

        echo "<pre>";
        print_r( $filePath );
        exit;
    }



    public static function updateConfigFileRows( $projectAlias, $arrFields )
    {
        $filePath       = WebsiteStyleModel::getConfigFilePath( $projectAlias );
        $fileContent    = file_get_contents( $filePath );
        $arrFileRows    = explode("\n", $fileContent);
        $arrNewFileRows = array();

        $openWizard     = false;
        $row            = 0;

        $arrCustomColors    = array();
        $arrHeadlineStyles  = array();


        foreach( $arrFileRows as $fileRow )
        {
            $fileRow    = trim($fileRow);

            if( strlen($fileRow) )
            {
                if( preg_match('/^\@/', $fileRow) )
                {
                    $arrNewFileRows[ $row ] = '';
                    $row++;

                    $arrNewFileRows[ $row ] = $fileRow;
                    $row++;

                    continue;
                }

                if( preg_match('/^\/\//', $fileRow) )
                {
                    if( $row > 0 )
                    {
                        $arrNewFileRows[ $row ] = '';
                        $arrNewFileRows[ ($row+1) ] = '';

                        $row = ($row+2);
                    }

                    $arrNewFileRows[ $row ] = $fileRow;
                    $row++;

                    $openWizard     = false;
                    $legendParts    = explode(";", trim(preg_replace('/^\/\//', '', $fileRow)));

                    if( $legendParts[1] )
                    {
                        $openWizard         = true;
                        $configFieldName    = $legendParts[1];
                        $configFields       = explode(",", $legendParts[2]);

                        $rowName            = '$' . $configFieldName;

                        $subrow     = 1;
                        $fullNum    = 1;
                        $count      = count( $arrFields[ $legendParts[1] ] );

                        foreach( $arrFields[ $legendParts[1] ] as $arrMcFields )
                        {
                            if( self::isEmptyRow($arrMcFields) )
                            {
                                continue;
                            }

                            $num    = 0;
                            foreach($arrMcFields as $fieldKey => $fieldValue)
                            {
                                $fieldType = 'text';

                                $arrMcFieldPart = explode("-", $configFields[ $num ]);
                                $fieldType = $arrMcFieldPart[1]?:'';

                                $putRow =  $rowName . $subrow . '_' . $fieldKey . ': ' . self::renderFieldValue($fieldValue, $fieldType) . ';' . (($arrMcFieldPart[1]) ? ' //' . $arrMcFieldPart[1] : '');
//                                echo "<pre>"; print_r( $putRow ); exit;
                                $arrNewFileRows[ $row ] = $putRow;
                                $row++;

                                $num++;
                            }

                            if( $count == $fullNum )
                            {
                                break;
                            }

                            $arrNewFileRows[ $row ] = '';

                            $row++;
                            $subrow++;

                            $fullNum++;
                        }
                    }

                    continue;
                }

                if( $openWizard )
                {
                    continue;
                }

                $arrField       = explode(':', $fileRow);
                $arrRowParts    = explode("//", $arrField[1]);

                $strFieldName   = preg_replace('/^\$/', '', $arrField[0]);
                $strFieldValue  = $arrFields[ $strFieldName ];
                $strFieldType   = trim($arrRowParts[1]);
                $quotField      = true;

                if( !$strFieldType )
                {
                    $strFieldType = 'text';
                }

                switch( $strFieldType )
                {
                    case "unit":
                        $strFieldValue = $strFieldValue['value'] . $strFieldValue['unit'];
                        $quotField      = false;
                        break;

                    case "color":
                        $strFieldValue  = ColorHelper::compileColor( $strFieldValue);
                        $quotField      = false;

                        if( $strFieldValue === "transparent" )
                        {
                            $strFieldValue  = '';
                            $quotField      = true;
                        }
                        break;
                }
//echo "<pre>";
//                print_r( $fileRow );
//                echo "<br>";
//                print_r( $strFieldValue );
//echo "</pre>";
                if( strlen($strFieldValue) )
                {
                    $putRow     = $fileRow;
                    $newValue   = $strFieldValue;

                    if( $quotField )
                    {
                        $newValue = "'" . $newValue . "'";
                    }

                    $putRow = preg_replace('/: ([a-zA-Z0-9%\'#\(\)]{0,});/', ': ' . $newValue . ';', $putRow);

//                    $arrFileRows[ $row ] = $putRow;
                    $arrNewFileRows[ $row ] = $putRow;
//                    fputs($putRow, $configFile);
                }
                else
                {
                    $putRow = preg_replace('/: ([a-zA-Z0-9%\'#\(\)]{0,});/', ": '';", $fileRow);

                    $arrNewFileRows[ $row ] = $putRow;
                }
            }

            $row++;
        }
//echo "<pre>"; print_r( $arrNewFileRows ); exit;
        file_put_contents($filePath, implode("\n", $arrNewFileRows));


        $varsFilePath   = WebsiteStyleModel::getConfigFilePath( $projectAlias, "ext-variables" );
        $varsRows       = explode("\n", file_get_contents($varsFilePath));
        $newVarRows     = array();

        $varRNum        = 0;
        foreach( $varsRows as $varRow )
        {
            if( strlen($varRow) )
            {
                if( preg_match('/^\@/', $varRow) )
                {
                    $newVarRows[ $varRNum ] = $varRow;
                    $varRNum++;

                    $newVarRows[ $varRNum ] = '';
                    $varRNum++;
//                    continue;
//                    break;
                }
                else
                {
                    break;
                }
            }
//            else
//            {
//                break;
//            }

            $varRNum++;
        }

        $arrCustomColors = WebsiteStylesHelper::getConfigFieldValue( $projectAlias, 'customColor' );

        if( count($arrCustomColors) )
        {
            foreach($arrCustomColors as $ccIndex => $customColor)
            {
                $newVarRows[ $varRNum ] = '$customColor_' . $customColor['varName'] . ': $customColor' . ($ccIndex+1) . '_color;';
                $varRNum++;
            }
        }

        $arrHeadlineStyles = WebsiteStylesHelper::getConfigFieldValue( $projectAlias, 'headlineStyle' );

        if( count($arrHeadlineStyles) )
        {
            $newVarRows[ $varRNum ] = '';
            $varRNum++;

            $arrHlClasses = array();
            $arrHlTagClasses = array();
            $arrHL = array();

            for($i=1; $i <= count($arrHeadlineStyles); $i++)
            {
                $arrHL[] = '$headlineStyle' . $i;

                $key = ($i-1);

                if( $arrHeadlineStyles[ $key ]['classes'] )
                {
                    $arrHlClasses[] = $arrHeadlineStyles[ $key ]['classes'];
                }

                if( $arrHeadlineStyles[ $key ]['tagClasses'] )
                {
                    $arrHlTagClasses[] = $arrHeadlineStyles[ $key ]['tagClasses'];
                }
            }

            if( count( $arrHL ) )
            {
                $newVarRows[ $varRNum ] = '$headlineStylesClasses: (' . implode('_classes, ', $arrHL) . '_classes);';
                $varRNum++;

                $newVarRows[ $varRNum ] = '$headlineStylesTagClasses: (' . implode('_tagClasses, ', $arrHL) . '_tagClasses);';
                $varRNum++;

                $newVarRows[ $varRNum ] = '$headlineStylesColors: (' . implode('_color, ', $arrHL) . '_color);';
                $varRNum++;

                $newVarRows[ $varRNum ] = '$headlineStylesSizes: (' . implode('_size, ', $arrHL) . '_size);';
                $varRNum++;

                $newVarRows[ $varRNum ] = '$headlineStylesAligns: (' . implode('_align, ', $arrHL) . '_align);';
                $varRNum++;
            }

            $newVarRows[ $varRNum ] = '';
            $varRNum++;

            $newVarRows[ $varRNum ] = '$countHeadlineStylesClasses: ' . count($arrHlClasses) . ';';
            $varRNum++;

            $newVarRows[ $varRNum ] = '$countHeadlineStylesTagClasses: ' . count($arrHlTagClasses) . ';';
        }

        file_put_contents($varsFilePath, implode("\n", $newVarRows));

        \Controller::reload();
    }



    public static function getConfigFileFieldType( $projectAlias, $varName)
    {
    }



    public static function getConfigFieldValue( $projectAlias, $varName )
    {
        $filePath       = WebsiteStyleModel::getConfigFilePath( $projectAlias );
        $fileContent    = file_get_contents( $filePath );
        $arrFileRows    = explode("\n", $fileContent);

        $varValue       = '';
        $getFields      = false;
        $getKey         = 0;

        foreach( $arrFileRows as $fileRow )
        {
            if( !strlen($fileRow) )
            {
                if( $getFields )
                {
                    $getKey++;
                }

                continue;
            }

            if( preg_match('/\/\//', $fileRow) && preg_match('/' . $varName . ';/', $fileRow) )
            {
                $getFields  = true;
                $getKey     = 0;

                $varValue = array();
                $varValue[ $getKey ] = array();
            }
            elseif( preg_match('/' . $varName . '/', $fileRow) )
            {
                $arrValueKey    = explode(":", $fileRow);
                $arrValue       = explode(";", $arrValueKey[1]);

                $rowValue       = trim($arrValue[0]);

                if( $getFields )
                {
                    $arrNameParts   = explode("_", $arrValueKey[0]);
                    $getName        = trim($arrNameParts[1]);

                    $varValue[ $getKey ][ $getName ] = preg_replace('/\'/', '', $rowValue);
                }
                else
                {
                    $varValue   = preg_replace('/\'/', '', $rowValue);
                }
            }
        }

        return $varValue;
    }



    public static function renderFieldValue( $value, $type )
    {
        $quotes = true;

        if( $type === "color" )
        {
            $quotes = false;
        }
        elseif( $type === "align" )
        {
            $value = preg_replace('/^header_/', '', $value);

            $quotes = false;
        }
        elseif( $type === "unit" )
        {
            $value = $value['value'] . $value['unit'];

            if( strlen($value) )
            {
                $quotes = false;
            }
        }
        elseif( $type === "ocColor" )
        {
            if( strlen($value) )
            {
                $value = '#' . $value;

                $quotes = false;
            }
        }

        return $quotes ? "'" . $value . "'" : $value;
    }



    protected static function isEmptyRow( $arrRow )
    {
        $isEmpty = true;

        foreach($arrRow as $strRow)
        {
            if( strlen(trim($strRow)) )
            {
                $isEmpty = false;
                break;
            }
        }

        return $isEmpty;
    }

}