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



    base.getStyle = function( elem, property )
    {
        var propValue = elem.style[ property ];

        if( propValue === undefined || propValue === "undefined" || propValue === null || propValue === '' )
        {
            propValue = window.getComputedStyle(elem, null).getPropertyValue( property );
        }

        return propValue;
    };



    base.getBodyScrollTop = function()
    {
        var doc = document.documentElement;

        return (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    };



    base.each = function(array, callback)
    {
        if (!array || !array.length)
        {
            return;
        }

        for (var i = 0; i < array.length; i++)
        {
            callback(array[i], i);
        }
    };



    base.addClass = function(element, className)
    {
        var elements = ('length' in element && !('className' in element))
            ? element
            : [element];
        var classNames = (typeof className === 'string')
            ? className.split(' ')
            : className;
        for (var i = 0; i < elements.length; i++)
        {
            for (var j = 0; j < classNames.length; j++)
            {
                if (!this.hasClass(elements[i], classNames[j]))
                {
                    elements[i].className += ' ' + classNames[j];
                }
            }
        }
    };



    base.removeClass = function(element, className)
    {
        var elements = ('length' in element && !('className' in element))
            ? element
            : [element];
        var classNames = (typeof className === 'string')
            ? className.split(' ')
            : className;

        for (var i = 0; i < elements.length; i++)
        {
            for (var j = 0; j < classNames.length; j++)
            {
                if (this.hasClass(elements[i], classNames[j]))
                {
                    elements[i].className = elements[i].className.replace(new RegExp('(?:^|\\s+)' + classNames[j] + '(?:$|\\s+)'), ' ');
                }
            }
        }
    };



    base.hasClass = function(element, className)
    {
        return !!element.className.match('(?:^|\\s)' + className + '(?:$|\\s)');
    };



    base.elementMatches = function(element, selector)
    {
        var methods = ['matches', 'matchesSelector', 'msMatchesSelector', 'mozMatchesSelector', 'webkitMatchesSelector'];

        for (var i = 0; i < methods.length; i++)
        {
            if (methods[i] in element)
            {
                return element[methods[i]](selector);
            }
        }

        return false;
    };



    base.isInViewport = function (elem)
    {
        var bounding = elem.getBoundingClientRect();

        return (
            bounding.top >= 0 &&
            bounding.left >= 0 &&
            bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    };

})(window, jQuery, IIDO.Base);
