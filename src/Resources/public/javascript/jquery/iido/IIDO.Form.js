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