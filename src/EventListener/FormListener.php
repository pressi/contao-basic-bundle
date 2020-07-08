<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;



use Contao\Form;
use Contao\Input;
use Contao\StringUtil;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Contao\CoreBundle\ServiceAnnotation\Hook;


/**
 * IIDO Form Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class FormListener implements ServiceAnnotationInterface
{

    /**
     * @Hook("compileFormFields")
     */
    public function onCompileFormFields(array $fields, string $formId, Form $form): array
    {
        $getRoom = urldecode( Input::get('room') );

        foreach( $fields as $key => $field )
        {
            if( $field->name === 'room' )
            {
                $arrOptions = StringUtil::deserialize( $field->options );

                foreach( $arrOptions as $optKey => $option )
                {
                    if( $option['value'] == $getRoom )
                    {
                        $option['default'] = true;
                    }

                    $arrOptions[ $optKey ] = $option;
                }

                $field->options = serialize($arrOptions);
            }

            $fields[ $key ] = $field;
        }

        return $fields;
    }

}
