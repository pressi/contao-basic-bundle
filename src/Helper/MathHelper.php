<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;
/**
 * Class Helper
 *
 * @package IIDO\BasicBundle
 * @TODO: überarbeiten!! funktioniert nicht!!
 */
class MathHelper
{

    public static function calculate($str)
    {
        $stack  = $v = [];

        $v[ 0 ] = $v[ 1 ] = $op = NULL;

        if( preg_match_all('#[ ()+*/^-]|(\-?\d+)#', $str, $m) )
            foreach( $m[ 0 ] as $tk )
                switch( $tk )
                {
                    case '+':
                    case '-':
                    case '*':
                    case '/':
                    case '^':
                        $op = $tk;
                        break;
                    case '(':
                        array_push($stack, [ $v[ 0 ], $v[ 1 ], $op ]);
                        $v[ 0 ] = $v[ 1 ] = $op = NULL;
                        break;
                    case ')':
                        $kResult = $v[ 0 ];
                        list($v[ 0 ], $v[ 1 ], $op) = array_pop($stack);
                        $v[ is_null($v[ 0 ]) ? 0 : 1 ] = $kResult;
                        $bracketMode                   = TRUE;
                    default:
                        if( !$bracketMode )
                        {
                            $v[ is_null($op) && is_null($v[ 0 ]) ? 0 : 1 ] = $tk;
                        }
                        if( !is_null($v[ 1 ]) )
                        {
                            $v[ 0 ] = self::math($v[ 0 ], $v[ 1 ], $op);
                            $op     = $v[ 1 ] = NULL;
                        }
                        $bracketMode = FALSE;
                        break;
                }

        return $v[ 0 ];
    }


    public static function math($v1, $v2, $op)
    {
        return (($op == '+') * ($v1 + $v2)) + (($op == '*') * ($v1 * $v2)) + (($op == '/') * ($v1 / $v2)) + (($op == '-') * ($v1 - $v2)) + (($op == '^') * pow($v1, $v2));
    }
}