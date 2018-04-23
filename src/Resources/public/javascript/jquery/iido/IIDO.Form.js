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

    form.init = function()
    {
        var lbForms = document.querySelectorAll(".open-form-in-lightbox");

        if( lbForms.length )
        {
            for(var num=0; num<lbForms.length; num++)
            {
                var formTag = lbForms[ num ];

                formTag.addEventListener("submit", function(e)
                {
                    e.preventDefault();

                    var sendFields = '',
                        formFields = this.querySelectorAll("input.text,select,textarea");

                    if( formFields.length )
                    {
                        for(var i=0; i<formFields.length; i++)
                        {
                            var formField = formFields[ i ];

                            sendFields += (i===0 ? '?' : '&') + formField.getAttribute("name") + '=' + formField.value;
                        }
                    }

                    var openPage = formTag.getAttribute("action") + sendFields;
                    var options = {
                        src: openPage,
                        type: "ajax",

                        opts: {
                            margin    : 0,
                            minHeight : 600,

                            type : "ajax",

                            infobar : false,
                            buttons : false,

                            slideShow  : false,
                            fullScreen : false,
                            thumbs     : false,
                            closeBtn   : true,

                            focus : false,

                            fitToView:true,
                            width:'100%',
                            height:'100%',

                            filter:'#main > .inside',
                            selector:'#main > .inside',
                            slideClass: 'form-modal'
                        }
                    };

                    $.fancybox.open(options);

                    return false;
                });
            }
        }
    };



    form.checkPhone = function( inputValue )
    {
        var phoneReg = /^(\+|\()?(\d+[ \+\(\)\/-]*)+$/;

        if( inputValue.length < 6 || (inputValue.length > 6 && !phoneReg.test(inputValue)) )
        {
            return false;
        }

        return true;
    };



    form.checkEmail = function( inputValue )
    {
        var emailReg = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

        if( !emailReg.test(inputValue) || inputValue === '' )
        {
            return false;
        }

        return true;
    };

})(window, jQuery, IIDO.Form);

document.addEventListener("DOMContentLoaded", function()
{
    IIDO.Form.init();
})