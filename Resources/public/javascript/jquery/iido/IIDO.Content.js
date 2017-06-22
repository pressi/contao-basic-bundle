/**********************************************************/
/*                                                        */
/*  (c) 2017 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Content = IIDO.Content || {};

(function (window, $, content)
{
    content.init = function()
    {
        this.initAnimations();
    };



    content.initAnimations = function()
    {
        setTimeout(function()
        {
            var articles = document.querySelectorAll('.mod_article');
            Array.prototype.forEach.call(articles, function(article, index)
            {
                IIDO.Content.startAnimate( article );
            });
        }, 500);
    };



    content.startAnimate = function( element )
    {
        var animateBoxes    = element.querySelectorAll('.animate-box'),
            animateBGs      = element.querySelectorAll('.bg-animate');

        Array.prototype.forEach.call(animateBoxes, function(item, index)
        {
            var animation   = item.getAttribute("data-animate"),
                triggerOnce = item.getAttribute("data-aniamte-trigger-once"),
                offset      = item.getAttribute("data-animate-offset");

            if( offset === undefined || offset === "undefined" || offset === null || offset.length === 0 )
            {
                offset = "95%";
            }

            if( triggerOnce === undefined || triggerOnce === "undefined" || triggerOnce === null || triggerOnce.length === 0 )
            {
                triggerOnce = true;
            }
            else
            {
                triggerOnce = (triggerOnce === 'true');
            }

            if ($(document.body).hasClass('ios') || $(document.body).hasClass('android') )
            {
                item.removeClass('animate-box');
                return true;
            }
            else
            {
                $(item).waypoint(function(direction)
                    {
                        if( $(item).hasClass("animate-wait") )
                        {
                            var waitIt      = item.getAttribute("data-wait"),
                                waitTime    = (waitIt * 250);

                            setTimeout(function() { IIDO.Content.animateClasses(item, animation) }, waitTime );
                        }
                        else
                        {
                            IIDO.Content.animateClasses(item, animation)
                        }
                        this.destroy();
                    },
                    {
                        offset: offset,
                        triggerOnce: triggerOnce
                    }
                );
            }
        });


        Array.prototype.forEach.call(animateBGs, function(item, index)
        {
            item.onmousemove = function(e)
            {
                var e1 = -(e.pageX + this.offsetLeft) / 50,
                    t1 = -(e.pageY + this.offsetTop) / 10;

                this.style.backgroundPosition = e1 + "px " + t1 + "px";
            };
        });
    };



    content.animateClasses = function(item, animation)
    {
        $(item).removeClass('animate-box').addClass('animated ' + animation).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function()
        {
            $(item).removeClass('animated ' + animation);
        });
    };

})(window, jQuery, IIDO.Content);

document.addEventListener("DOMContentLoaded", function(event)
{
    IIDO.Content.init();
});