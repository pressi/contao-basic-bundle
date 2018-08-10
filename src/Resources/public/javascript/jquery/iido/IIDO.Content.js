/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Content = IIDO.Content || {};

(function (window, $, content)
{
    var $scrollContainer, $tagline, $countdownInterval;


    content.init = function()
    {
        this.initStyleGuide();
        this.initAnimations();
        this.initCountdown();
        this.initAnimateBackgrounds();
        this.initTagline();
    };



    content.initAnimations = function()
    {
        window.addEventListener("load", function()
        {
            var animatedPositions = document.querySelectorAll(".animate-box.pos-center-top,.animate-box.pos-center-bottom,.animate-box.pos-center-center");

            if( animatedPositions )
            {
                for( var i = 0; i < animatedPositions.length; i++ )
                {
                    var boxPos = animatedPositions[ i ];

                    boxPos.style.marginLeft = '-' + (boxPos.offsetWidth / 2) + 'px';

                    if( boxPos.classList.contains("pos-center-center") )
                    {
                        boxPos.style.marginTop = '-' + (boxPos.offsetHeight / 2) + 'px';
                    }

                    boxPos.classList.add("no-transform");
                }
            }

            setTimeout(function()
            {
                var articles = document.querySelectorAll('.mod_article');

                Array.prototype.forEach.call(articles, function(article, index)
                {
                    IIDO.Content.startAnimate( article );
                });
            }, 500);
        });
    };



    content.startAnimate = function( element )
    {
        var animateBoxes    = element.querySelectorAll('.animate-box'),
            animateBGs      = element.querySelectorAll('.bg-animate');

        Array.prototype.forEach.call(animateBoxes, function(item, index)
        {
            var animation   = item.getAttribute("data-animate"),
                triggerOnce = item.getAttribute("data-animate-trigger-once"),
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
                $(item).removeClass('animate-box');
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



    content.initStyleGuide = function()
    {
        var styleguide = document.querySelectorAll(".ce_rsce_styleguide");

        if( styleguide.length )
        {
            Array.prototype.forEach.call(styleguide, function(sgContainer, index)
            {
                var imageTag    = sgContainer.querySelector("img"),

                    imgWidth    = imageTag.offsetWidth,
                    imgHeight   = imageTag.offsetHeight,

                    imgCont     = sgContainer.querySelector(".image-container"),
                    imgOriginW  = imgCont.getAttribute("data-width"),
                    imgOriginH  = imgCont.getAttribute("data-height"),

                    imagePoints = sgContainer.querySelectorAll(".image-point"),

                    percentW    = ((imgWidth / (imgOriginW / 100)) / 100),
                    percentH    = ((imgHeight / (imgOriginH / 100)) / 100);

                Array.prototype.forEach.call(imagePoints, function( imagePoint, pointIndex)
                {
                    var posX = parseInt(imagePoint.getAttribute("data-x"));

                    imagePoint.style.top    = (parseInt(imagePoint.getAttribute("data-y")) * percentH) + 'px';
                    imagePoint.style.left   = (posX * percentW) + 'px';

                    if( (imgOriginW / 2) > posX )
                    {
                        imagePoint.classList.add("side-right");
                    }
                    else
                    {
                        imagePoint.classList.add("side-left");
                    }
                });
            });

        }
    };



    content.scrollContainer = function( direction, container )
    {
        if( container === "undefined" || container === undefined || container === null)
        {
            container = $scrollContainer;
        }

        if( container !== "undefined" && container !== undefined && container !== null)
        {
            var scrollTopNum        = 0,
                contHeight          = container.children[0].clientHeight,
                mainHeight          = container.clientHeight;

            if( direction === "up" )
            {
                scrollTopNum = (mainHeight - contHeight);
            }

            var animationTime = ((2200 / contHeight) * (mainHeight - contHeight));

            $(container).animate({
                scrollTop: scrollTopNum
            }, animationTime);

            $scrollContainer = container;
        }
    };



    content.stopScrollContainer = function()
    {
        $($scrollContainer).stop();
    };



    content.initCountdown = function()
    {
        var containers = document.querySelectorAll(".countdown-container");

        if( containers.length )
        {
            for(var i=0; i<containers.length; i++)
            {
                var container   = containers[ i ],
                    strDate     = parseInt( container.getAttribute("data-date") );
                    // arrDate     = strDate.split(" "),
                    //
                    // arrDMY      = arrDate[0].split("."),
                    // arrTime     = arrDate[1].split(":");

                // var contDate    = new Date( arrDMY[2], (arrDMY[1] - 1), arrDMY[0], arrTime[0], arrTime[1] ).getTime();

                $countdownInterval = setInterval(IIDO.Content.runCountdown.bind( null, container, strDate ), 1000);
            }
        }
    };


    content.runCountdown = function( container, toDate )
    {
        var t = IIDO.Content.getTimeRemaining( toDate );

        if( t.total <= 0 )
        {
            clearInterval( $countdownInterval );
            container.innerHTML = container.getAttribute("data-text");
        }
        else
        {
            container.querySelector(".box-days .value").innerHTML       = t.days;
            container.querySelector(".box-hours .value").innerHTML      = t.hours; //('0' + t.hours).slice(-2);
            container.querySelector(".box-minutes .value").innerHTML    = t.minutes; //('0' + t.minutes).slice(-2);
            container.querySelector(".box-seconds .value").innerHTML    = t.seconds; //('0' + t.seconds).slice(-2);
        }


        // var now         = new Date().getTime(),
        //
        //     distance    = (toDate - now),
        //
        //     years       = 0,
        //     months      = 0,
        //     days        = Math.floor(distance / (1000 * 60 * 60 * 24)),
        //     hours       = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        //     minutes     = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
        //     seconds     = Math.floor((distance % (1000 * 60)) / 1000);
        //
        // if( distance < 0 )
        // {
        //     clearInterval( $countdownInterval );
        //     container.innerHTML = container.getAttribute("data-text");
        // }
        // else
        // {
        //     if( days > 30 )
        //     {
        //         months   = Math.floor( days / 30 );
        //         days    = (days % 30);
        //     }
        //
        //     if( months > 12 )
        //     {
        //         years   = Math.floor( months / 12 );
        //         days    = (months % 12);
        //     }
        //
        //     if( years > 0 )
        //     {
        //         container.querySelector(".box-years .value").innerHTML  = years;
        //     }
        //     else
        //     {
        //         if( container.querySelector(".box-years") )
        //         {
        //             container.querySelector(".box-years").classList.add("hidden");
        //         }
        //     }
        //
        //     if( months > 0 )
        //     {
        //         container.querySelector(".box-months .value").innerHTML = months;
        //     }
        //     else
        //     {
        //         if( container.querySelector(".box-months") )
        //         {
        //             container.querySelector(".box-months").classList.add("hidden");
        //         }
        //     }
        //
        //     if( days > 0 )
        //     {
        //         container.querySelector(".box-days .value").innerHTML   = days;
        //     }
        //     else
        //     {
        //         if( container.querySelector(".box-days") )
        //         {
        //             container.querySelector(".box-days").classList.add("hidden");
        //         }
        //     }

            // container.querySelector(".box-hours .value").innerHTML      = hours;
            // container.querySelector(".box-minutes .value").innerHTML    = minutes;
            // container.querySelector(".box-seconds .value").innerHTML    = seconds;
        // }
    };



    content.getTimeRemaining = function( endtime )
    {
        var t       = endtime - Date.parse( new Date() ),
            seconds = Math.floor( (t / 1000) % 60 ),
            minutes = Math.floor( (t / 1000 / 60) % 60 ),
            hours   = Math.floor( (t / (1000 * 60 * 60)) % 24 ),
            days    = Math.floor( t / (1000 * 60 * 60 * 24) );

        return {
            'total'     : t,
            'days'      : days,
            'hours'     : hours,
            'minutes'   : minutes,
            'seconds'   : seconds
        }
    };



    content.initAnimateBackgrounds = function()
    {
        setTimeout(function()
        {
            var elements = $(".bg-parallax, .box-image .image_container");

            if( elements.length )
            {
                elements.waypoint(function(direction)
                    {
                        if(!$("body").is(".mobile,.ios,.android"))
                        {
                            $.stellar({
                                horizontalOffset: 0,
                                verticalOffset: 0,
                                horizontalScrolling: false,
                                verticalScrolling: true,
                                parallaxBackgrounds: true,
                                positionProperty: "position",
                                scrollProperty: "scroll",
                                parallaxElements: true,
                                hideDistantElements: false
                            });
                        }
                    },
                    {
                        offset: '100%',
                        triggerOnce: true
                    });
            }
        }, 500);
    };



    content.initTagline = function()
    {
        if( !document.querySelector(".header-top-bar") )
        {
            var tagline = document.querySelector( ".content-element.tagline" );

            if( tagline )
            {
                $tagline = tagline.querySelector(".element-inside");

                IIDO.Content.resetTaglineStyles();

                IIDO.Base.addEvent(window, "resize load", IIDO.Content.resetTaglineStyles);
                IIDO.Base.addEvent(window, "scroll resize load", IIDO.Content.scrollTagline);
                IIDO.Content.scrollTagline();
            }
        }
    };



    content.scrollTagline = function()
    {
        if( IIDO.Base.getZoomLevel() > 1.01 || (window.matchMedia && window.matchMedia("(max-width: 900px)").matches) )
        {
            IIDO.Content.resetTaglineStyles();
            return;
        }

        var taglineHeight   = $tagline && $tagline.offsetHeight,
            offset          = window.pageYOffset || document.documentElement.scrollTop || 0;


        if( offset > 0 && offset < taglineHeight )
        {
            $tagline.parentNode.style.overflow = 'hidden';
            $tagline.style.transform = $tagline.style.WebkitTransform = 'translate3d(0,' + Math.round(Math.min(offset, taglineHeight) / 2) + 'px,0)';
            $tagline.style.opacity = (taglineHeight - (offset / 1.5)) / taglineHeight;
        }
        else
        {
            IIDO.Content.resetTaglineStyles();
        }
    };



    content.resetTaglineStyles = function()
    {
        $tagline.parentNode.style.overflow = '';
        $tagline.style.transform = $tagline.style.WebkitTransform = '';
        $tagline.style.opacity = '';
    };

})(window, jQuery, IIDO.Content);

document.addEventListener("DOMContentLoaded", function(event)
{
    IIDO.Content.init();
});