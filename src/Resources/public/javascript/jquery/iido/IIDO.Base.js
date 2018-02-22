/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Base = IIDO.Base || {};

(function (window, $, base) {

    base.addEvent = function(element, events, func)
    {
        events = events.split(' ');

        for (var i = 0; i < events.length; i++)
        {
            if(element.addEventListener)
            {
                element.addEventListener(events[i], func, false);
            }
            else
            {
                // element.attachEvent('on' + events[i], func);

                element.on(events[i], function(e) { func(e) });
            }
        }
    };



    base.getZoomLevel = function()
    {
        if (document.documentElement.clientWidth && window.innerWidth)
        {
            return document.documentElement.clientWidth / window.innerWidth;
        }

        return 1;
    };



    base.eventPreventDefault = function(event)
    {
        event = event || window.event;

        if(event.preventDefault)
        {
            event.preventDefault();
        }
        else
        {
            event.returnValue = false;
        }
    };



    base.toggleElementClass = function(element, className)
    {
        return ((element.hasClass(className)) ? element.removeClass(className) : element.addClass(className));
    };



    base.getSiblings = function( elem )
    {
        var siblings = [];
        var sibling = elem.parentNode.firstChild;

        for (; sibling; sibling = sibling.nextSibling)
        {
            if (sibling.nodeType !== 1 || sibling === elem) continue;
            siblings.push(sibling);
        }
        return siblings;
    };

})(window, jQuery, IIDO.Base);