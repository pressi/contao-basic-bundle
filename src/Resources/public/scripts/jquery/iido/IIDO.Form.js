/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Form = IIDO.Form || {};

(function (window, $, form)
{

    form.initForm = function()
    {
        this.initNumberFields();
        this.initDateFields();
        this.initIBANFields();
        this.initUIDFields();

        // email
        // $('.alpha-no-spaces').mask("A", {
        //     translation: {
        //         "A": { pattern: /[\w@\-.+]/, recursive: true }
        //     }
        // });
    };


    form.initNumberFields = function()
    {
        var fields = document.querySelectorAll('input[type="number"]');

        if( fields.length )
        {
            for( var i=0; i<fields.length; i++ )
            {
                var field = fields[i];

                field.setAttribute('type', 'text');
                field.setAttribute('inputmode', 'numeric');
                field.classList.add('type-number');

                $(field).mask('#.###', {reverse: true});
                // IIDO.Form.initNumberField( fields[i] );
            }
        }
    };



    form.initDateFields = function()
    {
        var fields = document.querySelectorAll('input.date-field');

        if( fields.length )
        {
            for( var i=0; i<fields.length; i++ )
            {
                var field = fields[i];

                field.classList.add('type-date');

                $(field).mask('00.00.0000');
            }
        }
    };



    form.initIBANFields = function()
    {
        var fields = document.querySelectorAll('input.iban-field');

        if( fields.length )
        {
            for( var i=0; i<fields.length; i++ )
            {
                var field = fields[i];

                field.classList.add('type-iban');

                $(field).mask('ZZ00 0000 0000 0000 0000', {
                    translation:
                        {
                            'Z': {pattern: /[A-Za-z0-9]/,}
                        },
                    onKeyPress: function(cep, event, currentField, options)
                    {
                        if( cep.length === 1 && cep !== 'A' )
                        {
                            cep = 'AT' + cep;
                        }
                        else if( cep.length === 2 && cep !== 'AT' )
                        {
                            cep = 'AT' + cep;
                        }
                        else if( cep.length > 2 )
                        {
                            var patt = new RegExp(/^AT/);

                            if( !patt.test(cep) )
                            {
                                cep = 'AT' + cep;
                            }
                        }

                        event.currentTarget.value = cep.toUpperCase();
                    }
                });

                field.addEventListener('focus', function()
                {
                    this.classList.remove("error");

                    if( this.value === '' )
                    {
                        this.value = 'AT';
                    }
                });

                field.addEventListener('blur', function()
                {
                    console.log( this.value.length );
                    if( this.value === 'AT' )
                    {
                        this.value = '';
                        this.classList.remove("filled-out");
                    }
                    else if( this.value.length > 2 && this.value.length < 24 )
                    {
                        this.classList.add("error");
                    }
                });
            }
        }
    };



    form.initUIDFields = function()
    {
        var fields = document.querySelectorAll('input.uid-number-field');

        if( fields.length )
        {
            for( var i=0; i<fields.length; i++ )
            {
                var field = fields[i];

                field.classList.add('type-uid-number');

                $(field).mask('ZZZ00000000', {
                    translation:
                        {
                            'Z': {pattern: /[A-Za-z0-9]/,}
                        },
                    onKeyPress: function(cep, event, currentField, options)
                    {
                        if( cep.length === 1 && cep !== 'A' )
                        {
                            cep = 'ATU' + cep;
                        }
                        else if( cep.length === 2 && cep !== 'AT' )
                        {
                            cep = 'ATU' + cep;
                        }
                        else if( cep.length === 3 && cep !== 'ATU' )
                        {
                            cep = 'ATU' + cep;
                        }
                        else if( cep.length > 3 )
                        {
                            var patt = new RegExp(/^ATU/);

                            if( !patt.test(cep) )
                            {
                                cep = 'ATU' + cep;
                            }
                        }

                        event.currentTarget.value = cep.toUpperCase();
                    }
                });

                field.addEventListener('focus', function()
                {
                    this.classList.remove("error");

                    if( this.value === '' )
                    {
                        this.value = 'ATU';
                    }
                });

                field.addEventListener('blur', function()
                {
                    if( this.value === 'ATU' )
                    {
                        this.value = '';
                        this.classList.remove("filled-out");
                    }
                    else if( this.value.length > 3 && this.value.length < 11 )
                    {
                        this.classList.add("error");
                    }
                });
            }
        }
    };



    form.initNumberField = function( formField )
    {
        formField.setAttribute('type', 'text');
        formField.classList.add('type-number');

        $(formField).mask('#.###', {reverse: true});

        // var locale      = 'de-DE',
        //     numericKeys = '0123456789';
        //
        //
        // // restricts input to numeric keys 0-9
        // formField.addEventListener('keypress', function(e)
        // {
        //     var event = e || window.event;
        //     var target = event.target;
        //
        //     if (event.charCode == 0)
        //     {
        //         return;
        //     }
        //
        //     if (-1 == numericKeys.indexOf(event.key))
        //     {
        //         // Could notify the user that 0-9 is only acceptable input.
        //         event.preventDefault();
        //         return;
        //     }
        // });
        //
        // var blurField = function(e)
        // {
        //     var event = e || window.event;
        //     var target = event.target;
        //
        //     var tmp = target.value.replace(/,/g, '');
        //     var val = parseInt(tmp).toLocaleString(locale);
        //
        //     if (tmp === '')
        //     {
        //         target.value = '';
        //     }
        //     else
        //     {
        //         target.value = val;
        //     }
        // };
        //
        // // add the thousands separator when the user blurs
        // formField.addEventListener('blur', function(e)
        // {
        //     blurField(e);
        // });
        // blurField({target:formField});
        //
        // // strip the thousands separator when the user puts the input in focus.
        // formField.addEventListener('focus', function(e)
        // {
        //     var event = e || window.event;
        //     var target = event.target;
        //     var val = target.value.replace(/[,.]/g, '');
        //
        //     target.value = val;
        // });
    };



    form.initInputIntegerChooser = function( prefix )
    {
        if( prefix === undefined || prefix === 'undefined' || prefix === null )
        {
            prefix = '';
        }

        var intChoosers = document.querySelectorAll(prefix + 'div.widget.widget-int-chooser');

        if( intChoosers.length )
        {
            for( var i=0; i<intChoosers.length; i++ )
            {
                var intChooser  = intChoosers[ i ],
                    intPlus     = intChooser.querySelector('.control > .button.is-static.addon-plus'),
                    intMinus    = intChooser.querySelector('.control > .button.is-static.addon-minus');

                intPlus.addEventListener('click', function()
                {
                    var textInput = this.parentNode.previousElementSibling.querySelector("input.text");

                    textInput.value = (parseInt( textInput.value ) + 1);
                });

                intMinus.addEventListener('click', function()
                {
                    var textInput   = this.parentNode.nextElementSibling.querySelector("input.text"),
                        inputValue  = (parseInt( textInput.value ) - 1);

                    if( inputValue < 0 )
                    {
                        inputValue = 0;
                    }

                    textInput.value = inputValue;
                });
            }
        }
    };


})(window, jQuery, IIDO.Form);