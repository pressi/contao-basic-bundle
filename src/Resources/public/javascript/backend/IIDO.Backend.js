/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO        = IIDO          || {};
IIDO.Backend    = IIDO.Backend  || {};

/*
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
*/

(function(backend)
{
    backend.init = function()
    {
        this.initExplanation();
    };



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



    backend.initExplanation = function()
    {
        if( $$(".be_explanation") )
        {
            this.initExplanationCollapsibles();
        }
    };



    backend.initExplanationCollapsibles = function()
    {
        $$('.be_explanation .toggle').addEvent('click', function()
        {
            var $toggle = $(this);

            $toggle.parent().toggleClass('open');
        });
    };



    backend.listWizard = function(id)
    {
        var ul = $(id),
            makeSortable = function(ul)
            {
                new Sortables(ul, {
                    constrain: true,
                    opacity: 0.6,
                    handle: '.drag-handle'
                });
            },

            addEventsTo = function(li)
            {
                var command, clone, input, previous, next;

                li.getElements('button').each(function(bt)
                {
                    if (bt.hasEvent('click')) return;
                    command = bt.getProperty('data-command');

                    switch (command)
                    {
                        case 'copy':
                            bt.addEvent('click', function()
                            {
                                Backend.getScrollOffset();

                                var rowNum = (li.getSiblings().length + 1);

                                clone = li.clone(true).inject(li, 'after');

                                var inputs = clone.getElements("input");

                                for(var ii=0; ii<inputs.length; ii++)
                                {
                                    var attr = inputs[ ii ].getAttribute("name");

                                    attr = attr.replace(/\[([0-9]{1,})\]\[\]$/, '[' + rowNum + '][]');

                                    inputs[ ii ].setAttribute("name", attr);
                                }

                                // if (input = li.getFirst('input'))
                                // {
                                //     clone.getFirst('input').value = input.value;
                                // }
                                addEventsTo(clone);
                            });
                            break;
                        case 'delete':
                            bt.addEvent('click', function()
                            {
                                Backend.getScrollOffset();
                                if (ul.getChildren().length > 1)
                                {
                                    li.destroy();
                                }
                            });
                            break;
                        case null:
                            bt.addEvent('keydown', function(e)
                            {
                                if (e.event.keyCode == 38)
                                {
                                    e.preventDefault();
                                    if (previous = li.getPrevious('li'))
                                    {
                                        li.inject(previous, 'before');
                                    }
                                    else
                                    {
                                        li.inject(ul, 'bottom');
                                    }
                                    bt.focus();
                                }
                                else if (e.event.keyCode == 40)
                                {
                                    e.preventDefault();
                                    if (next = li.getNext('li'))
                                    {
                                        li.inject(next, 'after');
                                    }
                                    else
                                    {
                                        li.inject(ul.getFirst('li'), 'before');
                                    }
                                    bt.focus();
                                }
                            });
                            break;
                    }
                });
            };

        makeSortable(ul);

        ul.getChildren().each(function(li) {
            addEventsTo(li);
        });
    };
    

})(IIDO.Backend);

$(document).addEvent("domready", function()
{
    IIDO.Backend.init();
});