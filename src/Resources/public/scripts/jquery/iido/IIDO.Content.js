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
    var $scrollFadeEl;



    content.init = function()
    {
        // this.initStyleGuide();

        this.initFormFields();
        this.initScrollFadeElements();
        this.initBorderAnimations();
        this.initAnimations();
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



    content.initFormFields = function()
    {
        IIDO.Form.initForm();
    };



    content.initScrollFadeElements = function()
    {
        var scrollFadeEl = document.querySelector( ".content-element.scroll-fade-element" );

        if( scrollFadeEl )
        {
            $scrollFadeEl = scrollFadeEl.querySelector(".element-inside");

            IIDO.Content.resetScrollFadeStyles();

            IIDO.Base.addEvent(window, "resize load", IIDO.Content.resetScrollFadeStyles());
            IIDO.Base.addEvent(window, "scroll resize load", IIDO.Content.scrollScrollFade);
            IIDO.Content.scrollScrollFade();
        }
    };



    content.scrollScrollFade = function()
    {
        let header = document.getElementById('header');
        let headerPos = IIDO.Base.getStyle(header, 'postion');
        let headerHeight = (headerPos !== 'absolute' && headerPos !== 'fixed') ? header.offsetHeight : 0;

        var offset = window.pageYOffset || document.documentElement.scrollTop || 0

        headerHeight = (headerHeight - 15);

        if( headerHeight > 0 && headerHeight >= offset )
        {
            return;
        }
        else
        {
            offset = (offset - headerHeight);
        }

        if( IIDO.Base.getZoomLevel() > 1.01 || (window.matchMedia && window.matchMedia("(max-width: 900px)").matches) )
        {
            IIDO.Content.resetScrollFadeStyles();
            return;
        }

        var scrollFadeHeight    = $scrollFadeEl && $scrollFadeEl.offsetHeight;

        // console.log( offset );
        // console.log( scrollFadeHeight );

        if( offset > 0 && offset < scrollFadeHeight )
        {
            $scrollFadeEl.parentNode.style.overflow = 'hidden';
            $scrollFadeEl.style.transform = $scrollFadeEl.style.WebkitTransform = 'translate3d(0,' + Math.round(Math.min(offset, scrollFadeHeight) / 2) + 'px,0)';
            $scrollFadeEl.style.opacity = (scrollFadeHeight - (offset / 1.5)) / scrollFadeHeight;
        }
        else
        {
            IIDO.Content.resetScrollFadeStyles();
        }
    };



    content.resetScrollFadeStyles = function()
    {
        $scrollFadeEl.parentNode.style.overflow = '';
        $scrollFadeEl.style.transform = $scrollFadeEl.style.WebkitTransform = '';
        $scrollFadeEl.style.opacity = '';
    };



    content.initBorderAnimations = function()
    {
        var controller  = new ScrollMagic.Controller(),
            winH        = window.innerHeight;

        var boxImages = document.querySelectorAll('.scroll-border-animation:not(.ani-css)');

        if( boxImages.length )
        {
            for( var biNum=0; biNum<boxImages.length; biNum++ )
            {
                var boxImage        = boxImages[ biNum ],
                    articleTagCont  = boxImage.parentNode;

                if( articleTagCont.classList.contains('article-inside') )
                {
                    articleTagCont = articleTagCont.parentNode;
                }

                var articleTag      = $( articleTagCont ),
                    bgColor         = false;

                if( articleTagCont.classList.contains('has-bg-color') )
                {
                    bgColor = IIDO.Base.getStyle(articleTagCont.querySelector('.article-bg-container'), 'background-color');
                }
                else
                {
                    bgColor = IIDO.Base.getStyle(document.body, 'background-color');
                }

                if( bgColor && bgColor !== 'transparent' && bgColor !== 'rgba(0, 0, 0, 0)' )
                {
                    boxImage.querySelector('.slide-box.slide-box-left').style.backgroundColor = bgColor;
                    boxImage.querySelector('.slide-box.slide-box-right').style.backgroundColor = bgColor;
                }

                var wipeAnimation = new TimelineMax()
                    .fromTo(
                        [
                            $(boxImage.querySelector('.slide-box.slide-box-left')),
                            $(boxImage.querySelector('.slide-box.slide-box-right'))
                        ],
                        0.2,
                        {
                            "width": 0
                        },
                        {
                            "width": 25,
                            ease: Linear.easeNone,
                        });

                // var boundingArticle = articleTagCont.getBoundingClientRect();
                var boundingArticle = boxImage.getBoundingClientRect();
                var bodyRect        = document.body.getBoundingClientRect(),
                    elemOffset      = (boundingArticle.top - bodyRect.top),
                    elemHeight      = boxImage.offsetHeight;

                //TODO: set offset to 0 when first article!
                elemOffset = 0;
                // var insidePadding   = 0,
                //     insideTag       = articleTagCont.querySelector('.article-inside');

                // if( insideTag )
                // {
                //     insidePadding = parseInt( IIDO.Base.getStyle(insideTag, 'padding-top') );
                // }

                new ScrollMagic.Scene({
                    triggerElement: articleTag,
                    triggerHook: "onLeave",
                    // duration: (winH / 3) + (articleTagCont.offsetHeight / 2) + parseInt( IIDO.Base.getStyle(articleTagCont, 'padding-top') ) + insidePadding,
                    // offset: (elemOffset - ((winH / 3)))
                    offset: elemOffset,
                    duration: elemOffset + (elemHeight / 2)
                })
                    .setPin( articleTag ).setTween( wipeAnimation ).addTo( controller );
            }
        }
    };

})(window, jQuery, IIDO.Content);

document.addEventListener("DOMContentLoaded", function(event)
{
    IIDO.Content.init();
});