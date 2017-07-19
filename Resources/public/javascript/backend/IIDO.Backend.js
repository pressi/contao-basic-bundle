/**********************************************************/
/*                                                        */
/*  (c) 2017 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <mail@stephanpressl.at>  */
/*                                                        */
/**********************************************************/
var IIDO        = IIDO          || {};
IIDO.Backend    = IIDO.Backend  || {};

(function(backend)
{
    backend.setMainFilterLabel = function(labelName, fieldName, labelTag)
    {
        var field       = $('ctrl_' + fieldName);

        if( field )
        {
            var varValue    = (field.value).trim();

            if( varValue.length )
            {
                if( varValue !== labelName )
                {
                    field.set("value", labelName.trim() );
                }
            }
            else
            {
                field.set("value", labelName.trim() );
            }

            labelTag.toggleClass("active");
            $(labelTag).getSiblings().removeClass("active");
        }
    };



    backend.setSubFilterLabel = function(labelName, fieldName, labelTag)
    {
        var field       = $('ctrl_' + fieldName);

        if( field )
        {
            var varValue    = (field.value).trim();

            if( varValue.length )
            {
                var arrValue    = varValue.split(","),
                    newValue    = varValue;

                if( arrValue.indexOf(labelName) === -1 )
                {
                    newValue = varValue + ',' + labelName;
                }
                else
                {
                    // var rgxp = new RegExp("(,)" + labelName);
                    //
                    // newValue = newValue.replace(rgxp, '');
                    //
                    // if( newValue.indexOf(labelName) != -1 )
                    // {
                    //     rgxp        = new RegExp(labelName + "(,)");
                    //     newValue    = newValue.replace(rgxp, '');
                    // }
                    newValue = newValue.replace(labelName, '').replace(',,', ',').replace(/,$/, '').replace(/^,/, '');
                }

                field.set("value", newValue.trim() );
            }
            else
            {
                field.set("value", labelName.trim() );
            }

            labelTag.toggleClass("active");
        }
    };

})(IIDO.Backend);

// $(document).addEvent("domready", function() {});