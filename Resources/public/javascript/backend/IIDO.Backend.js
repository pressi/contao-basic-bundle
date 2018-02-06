/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO        = IIDO          || {};
IIDO.Backend    = IIDO.Backend  || {};

// AjaxRequest.toggleFieldset = function(el, id, table)
// {
//     el.blur();
//     var fs = $('pal_' + id);
//
//     if (fs.hasClass('collapsed'))
//     {
//         fs.removeClass('collapsed');
//         new Request.Contao().post({'action':'toggleFieldset', 'id':id, 'table':table, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
//     }
//     else
//     {
//         var form        = fs.getParent('form'),
//             inp         = fs.getElements('[required]'),
//             collapse    = true;
//
//         for (var i=0; i<inp.length; i++)
//         {
//             if (!inp[i].get('value'))
//             {
//                 collapse = false;
//                 break;
//             }
//         }
//
//         if (!collapse)
//         {
//             if (typeof(form.checkValidity) === 'function') form.getElement('button[type="submit"]').click();
//         }
//         else
//             {
//             fs.addClass('collapsed');
//             new Request.Contao().post({'action':'toggleFieldset', 'id':id, 'table':table, 'state':0, 'REQUEST_TOKEN':Contao.request_token});
//         }
//     }
//
//     jQuery("#left").hcSticky("reinit");
//
//     return false;
// };

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
                var arrValue    = varValue.split(/[,\s]+/g).join().split(","),
                    newValue    = varValue;

                if( arrValue.indexOf(labelName) === -1 )
                {
                    newValue = varValue + ', ' + labelName;
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
                    // var rgxp = '(^[ ]{1})';

                    newValue = newValue.replace(labelName, '').replace(',,', ',').replace(', ,', ',').trim().replace(/,$/, '').replace(/^,/, '');
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