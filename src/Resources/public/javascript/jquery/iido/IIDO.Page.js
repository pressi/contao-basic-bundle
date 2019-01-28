/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Page = IIDO.Page || {};

(function (window, $, page)
{

    var $header, $wrapper, $searchForm, $mobileNav, $openButton, $mobileIsOpened, $tagline,
        $lbOpen = false, $openLinkTag = false, $headOptions = {}, $menuLink,

        $navOffset = 0;



    page.init = function()
    {
        $wrapper    = $('#wrapper');

        $tagline    = $('.tagline-inside');
        $header     = $("header");
        $navOffset  = $header.height();

        this.initHeader();
        this.initSearch();
        this.initMobileNavigation();
        this.initNavigation();
        this.initOnePage();
        this.initFullPage();
        this.initPageFade();
        this.initFooter();
        this.initMobile();
        this.initLinks();
        this.initShowArticles();
        this.initSticky();
        // this.initArticles();
        this.initArticleContainers();
        this.initPageScroll();

        if( $(document.body).hasClass("url-change") )
        {
            this.initArticles();
            this.initScroll();
        }

        $(window).resize( function()
        {
            IIDO.Page.initMobile();
        });

        $("a.open-in-lightbox,a.open-page-in-lightbox").click( function(e) { e.preventDefault(); IIDO.Page.openPageInLightbox(e); } );

        $(document).keyup(function(e)
        {
            if (e.keyCode === 27)
            {
                var articles = $(".mod_article:not(.first).shown");

                if( articles.length )
                {
                    var articleID = parseInt( $( articles[0] ).attr("id").replace("article-", "") );

                    if( articleID > 0 )
                    {
                        IIDO.Page.hideArticle( $('a[data-id="' + articleID + '"]') );
                    }
                }
            }
        });

        var imageArrows = $(".scroll-to-image-end");

        if( imageArrows.length )
        {
            for(var i=0; i<imageArrows.length; i++)
            {
                var imageArrow  = imageArrows[ i ];

                imageArrow.addEventListener("click", function(e)
                {
                    var ankorID = this.getAttribute("data-id");

                    IIDO.Page.scrollTo(e, 'image_arrow_ankor_' + ankorID, 0);
                })
            }
        }

        var topLink = document.querySelector(".ce_toplink");

        if( topLink )
        {
            topLink.addEventListener("click", function(e)
            {
                e.preventDefault();

               IIDO.Page.scrollTo(e, 'top');

               return false;
            });
        }
    };



    page.initFooter = function()
    {
        var footer          = document.getElementById('footer'),
            wrapper         = document.getElementById('wrapper'),
            winHeight       = window.innerHeight,
            footerHeight    = footer.offsetHeight;

        if( (winHeight - footerHeight) <= parseInt(wrapper.offsetHeight) )
        {
            wrapper.classList.add("has-shadow");
            footer.classList.remove("has-shadow");
        }
        else
        {
            wrapper.classList.remove("has-shadow");
            footer.classList.add("has-shadow");
        }
    };



    page.initHeader = function()
    {
        if( !$header.length )
        {
            return;
        }

        this.initHeaderLogin();

        if( $header.find(".header-top-bar").length )
        {
            $headOptions.headerTopBar        = $header.find(".header-top-bar");
            $headOptions.headerBar           = $header.find(".header-bar-inside");

            $headOptions.originHeight        = 0;
            $headOptions.originTopBarHeight  = 0;
            $headOptions.originBarHeight     = 0;

            $headOptions.minTopBarHeight     = 0;
            $headOptions.minBarHeight        = 0;

            this.headerUpdateValues();

            IIDO.Base.addEvent(window, 'resize load', IIDO.Page.headerUpdateValues);
            IIDO.Base.addEvent(window, 'scroll resize load', IIDO.Page.headerScroll);
            this.headerScroll();
        }
    };



    page.headerResetStyles = function()
    {
        $header.removeClass("is-fixed");
        $wrapper.css("padding-top", "");

        $headOptions.headerBar.css("height", "");

        $headOptions.headerTopBar.css({overflow:'', height:''});

        if( $tagline && $tagline.length )
        {
            $tagline.parent().css("overflow", "");
            $tagline.css(
                {
                    "transform"         : "",
                    "WebkitTransform"   : "",
                    "opacity"           : ""
                }
            );
        }
    };



    page.headerUpdateValues = function()
    {
        IIDO.Page.headerResetStyles();

        $headOptions.originHeight       = $header.outerHeight();
        $headOptions.originBarHeight    = $headOptions.headerBar.outerHeight();
        $headOptions.originTopBarHeight = $headOptions.headerTopBar.outerHeight();

        $headOptions.headerBar.css("height", 0);
        $headOptions.headerTopBar.css("height", 0);

        $headOptions.minBarHeight       = $headOptions.headerBar.outerHeight();
        $headOptions.minTopBarHeight    = $headOptions.headerTopBar.outerHeight();

        $headOptions.headerBar.css("height", "");
        $headOptions.headerTopBar.css("height", "");
    };



    page.headerScroll = function()
    {
        if (IIDO.Base.getZoomLevel() > 1.01 || (window.matchMedia && window.matchMedia("(max-width: 900px)").matches))
        {
            IIDO.Page.headerResetStyles();
            return;
        }

        var taglineHeight   = $tagline && $tagline.outerHeight(),
            documentHeight  = window.innerHeight || document.documentElement.clientHeight,
            offset          = window.pageYOffset || document.documentElement.scrollTop || 0,

            nav             = $headOptions.headerBar,
            bar             = $headOptions.headerTopBar;

        // Only allow scroll positions inside the possible range
        offset = Math.min(
            document.documentElement.scrollHeight - documentHeight,
            offset
        );
        offset = Math.max(0, offset);

        $header.addClass('is-fixed');
        $wrapper.css("padding-top", $headOptions.originHeight);

        nav.css("height", Math.max($headOptions.minBarHeight, $headOptions.originBarHeight - offset) );

        offset -= $headOptions.originBarHeight - Math.max($headOptions.minBarHeight, $headOptions.originBarHeight - offset);

        if (offset > 0)
        {
            bar.css("overflow", "hidden");
        }
        else
        {
            bar.css("overflow", "");
        }

        bar.css("height", Math.max($headOptions.minTopBarHeight, $headOptions.originTopBarHeight - offset) );

        offset -= $headOptions.originTopBarHeight - Math.max($headOptions.minTopBarHeight, $headOptions.originTopBarHeight - offset);

        if ($tagline && $tagline.length )
        {
            if (
                offset > 0
                && offset < taglineHeight
                && taglineHeight + $headOptions.originHeight < documentHeight
            ) {
                $tagline.parent().css("overflow", "hidden");

                var transformStyle = "translate3d(0," + Math.round(Math.min(offset, taglineHeight) / 2) + "px,0)";

                $tagline.css(
                    {
                        "transform"         : transformStyle,
                        "WebkitTransform"   : transformStyle,
                        "opacity"           : (taglineHeight - (offset / 1.5)) / taglineHeight
                    }
                );
            }
            else
            {
                $tagline.parent().css("overflow", "");

                $tagline.css(
                    {
                        "transform"         : "",
                        "WebkitTransform"   : "",
                        "opacity"           : ""
                    }
                );
            }
        }
    };



    page.initHeaderLogin = function()
    {
        var headerLoginElements = $header.find(".header-login");

        if( headerLoginElements.length && !headerLoginElements.hasClass("logout"))
        {
            var headline = headerLoginElements.find(".headline");

            if( headline && headline.length )
            {
                IIDO.Base.addEvent(headline[0], 'click', IIDO.Page.toggleHeaderLogin);

                if( headerLoginElements.find(".error").length )
                {
                    headerLoginElements.addClass("is-open");
                }
            }
        }
    };



    page.toggleHeaderLogin = function(e)
    {
        IIDO.Base.eventPreventDefault(e);
        IIDO.Base.toggleElementClass($(this).parent().parent(), 'is-open');

        var firstInput = $(this).parent().find("input[type=text]");

        if( firstInput && $(this).parent().parent().hasClass('is-open') )
        {
            firstInput.focus();
        }
        else
        {
            firstInput.blur();
        }
    };



    page.initScroll = function()
    {
        $(window).scroll( function()
        {
            var main            = $("main"),
                menuArticles    = main.find('.mod_article'),
                dataArticles    = main.find('.mod_article[data-menu="1"]'),
                urlParam        = "aritkel";

            if( $(document.body).hasClass("lang-en") )
            {
                urlParam = "article";
            }

            if( menuArticles.length && menuArticles.length > 1 )
            {
                menuArticles.each( function(index, element)
                {
                    var el = $(element);

                    if( (el.hasClass("first") && dataArticles.length) || el.attr("data-menu") === "1" )
                    {
                        var urlPath         = location.pathname,
                            elPosTop        = (el.position().top - 50),
                            elPosTopHeight  = elPosTop + el.height();

                        if( $(window).scrollTop() >= elPosTop && $(window).scrollTop() <= elPosTopHeight)
                        {
                            var artAlias    = el.attr("data-alias"),
                                linkTag     = $('.nav-main ul.level_2.article-menu > li a[data-alias="' + artAlias + '"]');

                            if( el.hasClass("first") )
                            {
                                linkTag = $('.nav-main ul.level_2.article-menu').prev();
                            }

                            if( linkTag.length )
                            {
                                IIDO.Page.changeUrl( linkTag, false, true );
                            }
                        }
                        else if( $(window).scrollTop() < elPosTop)
                        {
                            /* if( !el.hasClass("first") )
                            // {
                            //     var prevArticle     = el.prevAll('.mod_article[data-menu="1"]'),
                            //         firstArticle    = false;
                            //
                            //     if( !prevArticle.length )
                            //     {
                            //         firstArticle = true;
                            //         prevArticle = el.prevAll(".first");
                            //     }
                            //
                            //     var articleAlias    = prevArticle.attr("data-alias"),
                            //         linkTagPart     = $('.nav-main ul.level_2.article-menu > li a[data-alias="' + articleAlias + '"]');
                            //
                            //     if( firstArticle )
                            //     {
                            //         linkTagPart = $('.nav-main ul.level_2.article-menu').prev();
                            //     }
                            //
                            //     if( linkTagPart.length )
                            //     {
                            //         IIDO.Page.changeUrl( linkTagPart, false, true );
                            //     }
                            // } */
                        }
                    }
                })
            }
        });
    };



    page.initPageScroll = function()
    {
        window.addEventListener("scroll", function()
        {
            if( IIDO.Base.getBodyScrollTop() >= 50 )
            {
                document.body.classList.add("scrolled");
            }
            else
            {
                document.body.classList.remove("scrolled");
            }
        });
    };



    page.initArticles = function()
    {
        var menuArticles    = $("#main").find('.mod_article[data-menu="1"]');

        if( menuArticles.length )
        {
            var urlPath         = location.pathname,
                urlParthParts   = urlPath.split("/"),
                useScroll       = false,
                articleAlias    = "",
                scrollTop       = $(window).scrollTop(),
                urlParam        = "artikel";

            if( $(document.body).hasClass("lang-en") )
            {
                urlParam = "article";
            }

            /* var rgxp    = new RegExp(urlParam, 'g'),
            //     rgxpUrl = new RegExp(urlParam + '\/(.*).html', 'g');
            //
            // if( urlPath.match(rgxp) )
            // {
            //     var pathFirst   = urlPath.replace(rgxpUrl, ""),
            //         alias       = urlPath.replace(pathFirst + "/" + urlParam + "/", "");
            //
            //     alias = alias.replace(/.html$/, "");
            //
            //     var goTo = $("#" + alias);
            //
            //     if(goTo.length)
            //     {
            //         $(window).scrollTop( goTo.position().top );
            //     }
            // } */

            $.each( urlParthParts, function(i, urlPart)
            {
                if( useScroll )
                {
                    articleAlias = urlPart.replace(/.html$/, '');
                    return true;
                }
                else
                {
                    if( urlPart === "article" || urlPart === "artikel" )
                    {
                        useScroll = true;
                    }
                }
            });

            menuArticles.each( function(index, element)
            {
                var el      = $(element);

                if( useScroll && articleAlias === el.attr("data-alias") )
                {
                    IIDO.Page.scrollTo( window.event, el );
                    /* $(window).scrollTop( el.position().top );*/
                    return true;
                }
            });
        }
    };



    page.initArticleContainers = function()
    {
        var artContPosBottom = document.querySelector(".mod_article.ce-pos-bottom ");

        if( artContPosBottom )
        {
            var pitLane     = document.getElementById("pitLane"),
                footerCont  = document.getElementById("footer"),

                padBottom   = 0;

            if( pitLane )
            {
                padBottom = (padBottom + parseInt( pitLane.offsetHeight ));
            }

            if( footerCont )
            {
                padBottom = (padBottom + parseInt( footerCont.offsetHeight ));
            }

            if( padBottom > 0 )
            {
                var mainCont = document.getElementById("main");

                if( mainCont )
                {
                    var mainPadBottom = parseInt( IIDO.Base.getStyle(mainCont, "padding-bottom") );

                    if( mainPadBottom > 0 )
                    {
                        padBottom = (padBottom - mainPadBottom);
                    }
                }

                if( padBottom > 0 )
                {
                    artContPosBottom.style.paddingBottom = padBottom + 'px';
                }
            }
        }
    };



    page.initNavigation = function()
    {
        if( document.body.classList.contains("page-is-onepage") )
        {
            var onepageNav = document.querySelector(".nav-onepage");

            if( onepageNav )
            {
                $(onepageNav).find("ul.level_1").onePageNav({
                    currentClass: 'active'
                });
            }
        }

        var navContLeftOutsideOpen  = document.querySelector(".open-left-side-navigation"),
            navContLeftOutside      = document.querySelector("header.nav-cont-left-outside");

        if( navContLeftOutsideOpen && navContLeftOutside )
        {
            navContLeftOutsideOpen.addEventListener("click", function()
            {
                // if( document.body.classList.contains("open-navigation") )
                // {
                //     document.body.classList.remove("open-navigation");
                // }
                // else
                // {
                //     document.body.classList.add("open-navigation");
                // }

                if( document.body.classList.contains("hide-navigation") )
                {
                    document.body.classList.remove("hide-navigation");
                }
                else
                {
                    document.body.classList.add("hide-navigation");
                }
            });
        }

        // var articleMenu     = $("ul.article-menu");
        //
        // if( articleMenu.length )
        // {
        //     articleMenu.each( function(index, element)
        //     {
        //         var el          = $(element),
        //             linkMain    = el.prev("a"),
        //             elChilds    = el.children("li");
        //
        //         if( !linkMain.length )
        //         {
        //             linkMain = el.prev("strong");
        //         }
        //
        //         /* if( linkMain.hasClass("article-link") )
        //         // {
        //         //     linkMain.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( linkMain, true, true ); });
        //         // } */
        //         linkMain.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( linkMain, true, true ); });
        //
        //         if( elChilds.length )
        //         {
        //             elChilds.each( function(i, elem)
        //             {
        //                 var link = $(elem).find("> a");
        //
        //                 /* if( link.hasClass("article-link") )
        //                 // {
        //                 //     link.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( link, true, true ); });
        //                 // } */
        //                 link.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( link, true, true ); });
        //             });
        //         }
        //     });
        // }

        var articleSubMenu     = $("ul.article-submenu");

        if( articleSubMenu.length )
        {
            articleSubMenu.each( function(index, element)
            {
                var el          = $( element ),
                    elChildren  = el.children("li");

                if( elChildren.length )
                {
                    elChildren.each( function(number, children)
                    {
                        var child       = $(children),
                            childLink   = child.find("> a");

                        if( !childLink.length )
                        {
                            childLink = child.find("> strong");
                        }

                        if( child.hasClass("external-link") )
                        {
                            if( childLink.attr("target") === "_blank" )
                            {
                                childLink.click( function(e) { e.preventDefault(); window.open( childLink.attr("href") ); });
                            }
                            else
                            {
                                childLink.click( function(e) { e.preventDefault(); location.href = childLink.attr("href"); });
                            }
                        }
                        else
                        {
                            if( childLink.hasClass("article-link") )
                            {
                                childLink.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( childLink, true, true ); });
                            }
                        }
                    });
                }
            });
        }
    };



    page.changeUrl = function( linkTag, scrollTo, changeMenu )
    {
        var url             = (window.location.pathname).replace(/^\//, ''),
            newUrl          = (($(linkTag)[0].tagName === "STRONG") ? $(linkTag).attr("data-href") : $(linkTag).attr("href") ),
            site            = {},

            newTitle        = document.title,
            linkTitle       = $(linkTag).html(),
            urlParam        = "artikel",
            urlParts        = newUrl.split("/"),
            articleAlias    = "";

        if( scrollTo === undefined || scrollTo === "undefined" || scrollTo === null)
        {
            scrollTo = false;
        }

        if( changeMenu === undefined || changeMenu === "undefined" || changeMenu === null)
        {
            changeMenu = false;
        }

        if( $(document.body).hasClass("lang-en") )
        {
            urlParam = 'article';
        }

        $.each(urlParts, function(index, urlPart)
        {
            if( urlPart === urlParam )
            {
                articleAlias = (urlParts[ index + 1 ]).replace(/.html$/, '');
                return true;
            }
        });

        var rgxp    = new RegExp(urlParam, 'g'),
            rgxpUrl = new RegExp(urlParam + '\/(.*).html', 'g');

        /* if(newUrl.match(rgxp))
        // {
        //     newUrl  = newUrl.replace(rgxpUrl, urlParam + "/" + $(element).attr("data-alias")) + '.html';
        // }
        // else
        // {
        //     newUrl  = newUrl.replace(".html", "") + '/' + urlParam + '/' + $(element).attr("data-alias") + '.html';
        // } */
        if(url !== newUrl)
        {
            /* newTitle = newTitle.replace(/(.*)::/,   '$1 - ' + linkTitle + ' ::'); */

            history.pushState(site, newTitle, newUrl);
            document.title = newTitle;

            if( changeMenu )
            {
                $(linkTag).parent("li.active").find("a").removeClass("active");
                $(linkTag).parent("li").siblings().removeClass("active");

                $(linkTag).addClass("active");
                $(linkTag).parent("li").addClass("active");
            }

            if( scrollTo )
            {
                IIDO.Page.scrollTo( window.event, $('.mod_article[data-alias="' + articleAlias + '"]') );
            }
        }
    };



    page.initOnePage = function()
    {
        var winHeight           = $(window).height(),
            headerTag           = $("header"),
            footerTag           = $("footer"),
            footerLine          = $(".footer-bottom-line"),

            headerFullHeight    = $headOptions.originHeight,
            headerHeight        = 0,
            footerHeight        = footerTag.height();

        $("#main").find(".mod_article").each(function(index, element)
        {
            var el = $(element);

            if( (el.hasClass("first") && $(document.body).hasClass("onepage")) || el.hasClass("fullheight") || el.hasClass("full-height") || el.attr("data-height") === "full" )
            {
                if( el.hasClass( "first" ) )
                {
                    winHeight = (winHeight - headerFullHeight);
                }
                else
                {
                    winHeight = (winHeight - headerHeight);
                }

                if( el.hasClass( "last" ) )
                {
                    winHeight = (winHeight - footerHeight );
                }

                if( !el.hasClass("no-fullheight") )
                {
                    el.height( winHeight );
                }
            }

            if( el.hasClass("text-valign-middle-optical") )
            {
                var elPaddingBottom = headerHeight;

                if( el.hasClass("first") )
                {
                    elPaddingBottom = headerFullHeight;
                }

                el.find(".article-inside").css("padding-bottom", elPaddingBottom)
            }
        });
    };



    page.initFullPage = function()
    {
        if( $(document.body).hasClass("enable-fullpage") )
        {
            var main        = $("#main"),
                arrAnchors  = [];

            main.find(".mod_article").each( function(index, articleTag)
            {
                var article = $(articleTag);

                if( article.css("display") !== "none")
                {
                    arrAnchors.push( article.attr("data-anchor") );
                }
                else
                {
                    article.remove();
                }

                /*/ if( !article.hasClass("hidden-area") )
                // {*/
                // arrAnchors.push( article.attr("data-anchor") );
                /*/ }*/
            });

            /*/TODO: make options changeable in backend*/
            main.fullpage(
                {
                    /*Navigation*/
                    menu                                : '.nav-main',
                    //lockAnchors                         : false,
                    anchors                             : arrAnchors,
                    //navigation                          : false,
                    /* navigationPosition                  : 'right',
                    // navigationTooltips                  : ['firstSlide', 'secondSlide'],*/
                    //showActiveTooltip                   : false,
                    //slidesNavigation                    : true,
                    //slidesNavPosition                   : 'top',

                    /*Scrolling*/
                    css3                                : true,
                    scrollingSpeed                      : 800,
                    /*scrollDelay                         : 600,*/
                    //autoScrolling                       : true,
                    //fitToSection                        : true,
                    //fitToSectionDelay                   : 600,
                    //scrollBar                           : false,
                    // easing                              : 'linear', //'easeInQuart',
                    // easingcss3                          : 'linear', //'ease',
                    //loopBottom                          : false,
                    //loopTop                             : false,
                    loopHorizontal                      : false,
                    //continuousVertical: false,
                    continuousHorizontal                : false,
                    scrollHorizontally                  : false, // extension
                    //interlockedSlides: false,
                    //dragAndMove: false,
                    //offsetSections: false,
                    //resetSliders: false,
                    //fadingEffect: false,
                    /*/ normalScrollElements: '#element1, .element2',*/
                    scrollOverflow: false,
                    //scrollOverflowReset: false,
                    scrollOverflowOptions:
                    {
                        preventDefault: false
                    },
                    /*touchSensitivity: 500,*/
                    /*normalScrollElementTouchThreshold: 5,*/
                    //bigSectionsDestination: null,

                    /*/Accessibility*/
                    //keyboardScrolling: true,
                    //animateAnchor: true,
                    //recordHistory: true,

                    /*/Design*/
                    //controlArrows: true,
                    //verticalCentered: true,
                    /*/ sectionsColor : ['#ccc', '#fff'],
                    // slidesColor                         : ['#ccc', '#fff'],
                    // paddingTop: '3em',
                    // paddingBottom: '10px',*/
                    //fixedElements: '#header', /*/'#header, .footer'*/
                    responsiveWidth: 500,
                    // responsiveHeight: 0,
                    responsiveSlides: false,
                    //parallax: false,
                    /*/ parallaxOptions: {type: 'reveal', percentage: 62, property: 'translate'},*/

                    /*/Custom selectors*/
                    /*/ sectionSelector: '.section',
                    // slideSelector: '.slide',*/

                    //lazyLoading: true,

                    resize                              : false,


                    onLeave: function(index, nextIndex, direction)
                    {
                        IIDO.FullPage.runLeaveSection(index, nextIndex, direction);
                        IIDO.FullPage.runLeaveSectionAll(index, nextIndex, direction);
                    },

                    afterLoad: function(anchorLink, index)
                    {
                        IIDO.FullPage.runLoadSection(index, anchorLink);
                        IIDO.FullPage.runLoadSectionAll(index, anchorLink);

                        var articleTag = document.querySelector('.mod_article[data-alias="' + anchorLink + '"]');

                        if( articleTag )
                        {
                            if( !articleTag.classList.contains("loaded") )
                            {
                                articleTag.classList.add( "loaded" );

                                var animateBoxes = articleTag.querySelectorAll( '.animate-box' );

                                if( animateBoxes.length )
                                {
                                    for(var i= 0; i < animateBoxes.length; i++)
                                    {
                                        var item        = animateBoxes[ i ],
                                            animation   = item.getAttribute( "data-animate" );

                                        if( item.classList.contains("animate-wait") )
                                        {
                                            var waitIt      = item.getAttribute("data-wait"),
                                                waitTime    = (waitIt * 250);

                                            setTimeout(IIDO.Content.animateClasses.bind(this, item, animation), waitTime );
                                        }
                                        else
                                        {
                                            IIDO.Content.animateClasses(item, animation)
                                        }
                                    }
                                }
                            }
                        }
                    },

                    onSlideLeave: function(anchorLink, index, slideIndex, direction, nextSlideIndex)
                    {
                        IIDO.FullPage.runLeaveSlide(index, slideIndex, nextSlideIndex, direction, anchorLink);
                        IIDO.FullPage.runLeaveSlideAll(index, slideIndex, nextSlideIndex, direction, anchorLink);
                    }
                }
            );

            var logoLink = document.querySelector("header .logo a");

            if( !logoLink )
            {
                logoLink = document.querySelector("header .logo img");
            }

            if( logoLink )
            {
                logoLink.addEventListener("click", function(e) { e.preventDefault(); IIDO.Page.goToSection(1);  });
            }

            var nextLinks           = document.querySelectorAll(".scroll-to-next-page"),
                nextSectionLinks    = document.querySelectorAll(".scroll-to-next-section"),
                pageLinks           = document.querySelectorAll(".scroll-to-section-page"),
                goToSection         = document.querySelectorAll("a.go-to-section");

            if( nextLinks.length )
            {
                for(var i=0; i<nextLinks.length; i++)
                {
                    var nextLink = nextLinks[ i ];

                    nextLink.addEventListener("click", function(e)
                    {
                        e.preventDefault();

                        $.fn.fullpage.moveSectionDown();

                        return false;
                    });
                }
            }

            if( nextSectionLinks.length )
            {
                for( var ni=0; ni<nextSectionLinks.length; ni++)
                {
                    var nextSectionLink = nextSectionLinks[ ni ];

                    nextSectionLink.addEventListener("click", function(e)
                    {
                        e.preventDefault();

                        $.fn.fullpage.moveSlideRight();
                    });
                }
            }

            if( pageLinks.length )
            {
                for(var num=0; num<pageLinks.length; num++)
                {
                    var pageLink = pageLinks[ num ];

                    pageLink.addEventListener("click", function(e)
                    {
                        e.preventDefault();

                        var sectionLink     = e.target.parentNode.getAttribute("href"),
                            sectionAlias    = sectionLink.split("/").pop().replace(/.html$/, ''),
                            sectionIndex    = document.querySelector('.mod_article[data-anchor="' + sectionAlias + '"]').getAttribute("data-index");

                        $.fn.fullpage.moveTo( (parseInt(sectionIndex) + 1) );

                        return false;
                    });
                }
            }

            if( goToSection.length )
            {
                for(var iNum=0; iNum<goToSection.length; iNum++)
                {
                    var sectionLink = goToSection[ iNum ];

                    sectionLink.addEventListener("click", function(e)
                    {
                       e.preventDefault();

                       var articleTag       = document.querySelector('.mod_article[data-anchor="' + e.target.parentNode.getAttribute("data-article") + '"]'),
                           articleIndex     = articleTag.getAttribute("data-index");

                        $.fn.fullpage.moveTo( (parseInt(articleIndex) + 1) );
                    });
                }
            }
        }
        else
        {
            var nextPageLinks = document.querySelectorAll(".scroll-to-next-page");

            if( nextPageLinks.length )
            {
                for(var i=0; i<nextPageLinks.length; i++)
                {
                    var nextPageLink = nextPageLinks[ i ];

                    nextPageLink.addEventListener("click", function(e)
                    {
                        e.preventDefault();

                        var parenArticle    = this.parentNode,
                            nextArticle     = parenArticle.nextElementSibling;

                        if( nextArticle )
                        {
                            var intOffset = 0;

                            if( document.querySelector("header.is-fixed") )
                            {
                                intOffset = -(document.querySelector("header.is-fixed").clientHeight);
                            }

                            IIDO.Page.scrollTo(e, nextArticle.getAttribute("id"), intOffset );
                        }

                        return false;
                    });
                }
            }
        }
    };



    page.goToSection = function( sectionIndex )
    {
        $.fn.fullpage.moveTo( sectionIndex, 0 );

        return false;
    };



    page.sectionBack = function(navTag)
    {
        var articleTag  = navTag.parentNode.parentNode,
            index       = (parseInt(articleTag.getAttribute("data-index")) + 1);

        if( index > 1 )
        {
            $.fn.fullpage.moveTo( (index - 1) );
        }
    };



    page.sectionForward = function(navTag)
    {
        var articleTag  = navTag.parentNode.parentNode,
            index       = (parseInt(articleTag.getAttribute("data-index")) + 1),
            maxIndex    = document.querySelectorAll("#main .mod_article").length;

        if( index < maxIndex)
        {
            $.fn.fullpage.moveTo( (index + 1) );
        }
    };



    page.scrollTo = function( event, aTag, aOffset, animated )
    {
        if( event !== undefined && event !== "undefined" && event !== null )
        {
            event.preventDefault();
        }

        if( animated === undefined || animated === "undefined" || animated === null )
        {
            animated = true;
        }

        var isString = true;

        if( typeof aTag === "string" )
        {
            isString = true;
        }

        var target  = "#" + (isString ? aTag : (($(aTag).attr("data-anker") === undefined || $(aTag).attr("data-anker") === "undefined"||$(aTag).attr("data-anker") === null)?$(aTag).attr("id"):$(aTag).attr("data-anker"))),
            offset  = -$navOffset;

        if( aTag === "top" )
        {
            target = "#top";
            offset = 0;
        }

        if( target === '#' || target === '#undefined' )
        {
            target = aTag.attr("href");
        }

        if( aOffset !== undefined && aOffset !== "undefined" && aOffset !== null)
        {
            offset = aOffset;
        }

        var targetObj = document.getElementById( target.replace(/^#/, '') );

        if( !targetObj )
        {
            targetObj = document.querySelector('*[data-anchor="' + target.replace(/^#/, '') + '"]');

            if( targetObj )
            {
                target = '#' + targetObj.getAttribute("id");
            }
        }

        var waitIt      = false;

        if( waitIt )
        {
            setTimeout(function()
            {
                $.smoothScroll({
                    offset          : offset,
                    scrollTarget    : target
                });
            }, 400);
        }
        else
        {
            var config = {
                offset          : offset,
                scrollTarget    : target
            };

            if( !animated )
            {
                config.speed = 0;
            }
            
            $.smoothScroll( config );
        }

        /* $(".image-point.open").removeClass("open");*/

        return false;
    };



    page.openPageInLightbox = function(e)
    {
        e.preventDefault();

        var el              = $( e.currentTarget),
            openType        = 'ajax',
            canBeOpen       = true,
            siblingLinks    = el.parent("li" ),
            page            = el.attr("href"), /*$( e.currentTarget ).href, //.split("/").pop().replace('.html', ''),*/
            url             = '/SimpleAjaxFrontend.php?c=IIDO_BasicBundle_Ajax_Page&f=renderPageContent&v=' + page;

        if( el.hasClass("open-type-iframe") || el.hasClass("open-iframe") || el.attr("type") === "iframe"  || el.attr("open-type") === "iframe" )
        {
            openType    = "iframe";
            url         = page;
        }

        var options = {
            src: page,
            type: openType,

            opts: {
                margin    : [ 110, 0, 110, 0 ],
                padding   : 0,
                /*minWidth : "100%",
                // height    : 600,*/
                minHeight : 600,

                type : openType,

                infobar : false,
                buttons : false,

                slideShow  : false,
                fullScreen : false,
                thumbs     : false,
                closeBtn   : true,

                focus : false,

                beforeShow : function(e)
                {
                    /* $( "html" ).addClass( "fancybox-lock" );*/
                },

                afterShow : function()
                {
                },

                beforeClose : function()
                {
                },

                afterClose : function()
                {
                    /* $( "html" ).removeClass( "fancybox-lock" );
                    //
                    // if( activeTag.length )
                    // {
                    //     activeTag.removeClass( "active" );
                    // }*/

                    if( $menuLink )
                    {
                        $menuLink.removeClass("active");
                        $menuLink = '';
                    }

                    if( $openLinkTag )
                    {
                        $openLinkTag.removeClass("active");
                        $openLinkTag = '';
                    }

                    $lbOpen = false;
                }
            }
        };

        var slideClassName = "page-lightbox";

        if( el.hasClass("event-link") )
        {
            slideClassName = "event-page page-lightbox";
        }

        if( openType === "ajax" )
        {
            // options = $.extend({}, options, {opts:{fitToView:true,width:'100%',height:'100%',margin:0,filter:'#main > .inside',selector:'#main > .inside',slideClass:slideClassName}});

            // options.opts.fitToView = true;
            // options.opts.width = '100%';
            // options.opts.height = '100%';
            // options.opts.margin = 0;
            options.opts.filter = '#main > .inside';
            options.opts.selector = '#main > .inside';
            options.opts.slideClass = slideClassName;
        }

        if( el.hasClass("fit") || el.hasClass("fit-to-view") || el.hasClass("ftv") )
        {
            /* options = $.extend({}, options,
             {opts:{fitToView:true,width:'100%',height:'100%',margin:0,wrapCSS:"news-lightbox"}});
              */


            options.opts.afterLoad = function(current, previous) {
                setTimeout( function() {

                    var $container = $('#loadingList');

                    /* $container.isotope({
                    //     itemSelector: '.layout_latest',
                    //     layoutMode: 'masonry',
                    //     columnWidth: '100%',
                    //     gutter: 0
                    // }); */

                    $container.infinitescroll({
                            loading:
                                {
                                    finishedMsg: "Alle News wurden geladen!",
                                    msgText: 'mehr News werden geladen ...</div><div class="double-bounce1"></div><div class="double-bounce2">'
                                },
                            nextSelector: "#loadingListParent .pagination li.next a",
                            navSelector: "#loadingListParent .pagination",
                            itemSelector: "#loadingList .layout_latest"
                        },
                        function(newElements, data)
                        {
                            /* $container.isotope('layout');*/
                        }
                    );

                    $(window).unbind('.infscr');

                    $('#loadingListParent').find('.pagination li.next a').click(function(){
                        $container.infinitescroll('retrieve');
                        return false;
                    });

                }, 500);
            };
        }
        else
        {
            /* options = $.extend({}, options,
             {opts:{fitToView:true,width:'100%',height:'100%',margin:0,wrapCSS:"page-lightbox"}});
              */
        }

        /* if( el.hasClass("icon-search") )
        // {
        //     options = $.extend({}, options, {closeBtn:false,modal:false,wrapCSS:"search-modal"});
        // } */

        if($lbOpen)
        {
            if( el.hasClass("active") )
            {
                if( $menuLink )
                {
                    $menuLink.removeClass("active");
                    $menuLink = '';
                }

                siblingLinks.each(function(index, element) { $(element).find("a").removeClass("active") });
                $.fancybox.close();

                canBeOpen   = false;
                $lbOpen     = false;
            }
            else
            {
                $.fancybox.close();
                $lbOpen = false;

                if( $menuLink )
                {
                    $menuLink.removeClass("active");
                    $menuLink = '';
                }
                siblingLinks.each(function(index, element) { $(element).find("a").removeClass("active") });
            }
        }

        if( canBeOpen )
        {
            var activeTag   = "",
                activeLink  = $.trim( el.html() );

            siblingLinks.each(function(index, element)
            {
                var linkTag     = $(element).find("a"),
                    menuLink    = $.trim( linkTag.html() );

                if(menuLink === activeLink)
                {
                    linkTag.addClass("active");
                    activeTag = linkTag;

                    $menuLink = linkTag;
                }
            });

            $openLinkTag    = el;

            $.fancybox.open(options);

            $lbOpen         = true;
        }
    };



    page.openGallery = function( galleryImages )
    {
        $.fancybox.open( galleryImages );
    };



    page.initPageFade = function()
    {
        if( $(document.body).hasClass("page-fade-animation") )
        {
            var lastElementClicked;
            /* var PrevLink = document.querySelector('.mod_booknav .previous > a');
            // var NextLink = document.querySelector('.mod_booknav .next > a');*/

            Barba.Pjax.init();
            /* Barba.Pjax.start(); */
            Barba.Prefetch.init();

            Barba.Pjax.originalPreventCheck = Barba.Pjax.preventCheck;

            Barba.Pjax.preventCheck = function(evt, element)
            {
                if( $(element).hasClass("no-barba") )
                {
                    return false;
                }

                if (!Barba.Pjax.originalPreventCheck(evt, element))
                {
                    return false;
                }

                var parent = element.parentNode.parentNode;

                if( parent.classList.contains("image_container") )
                {
                    parent = parent.parentNode;
                }

                if( parent.classList.contains("logo") )
                {
                    return true;
                }

                if( !parent.classList.contains("logo") && location.href.replace(/\/$/, '') !== location.origin )
                {
                    return false;
                }

                return true;
            };

            Barba.Dispatcher.on('linkClicked', function(HTMLElement, MouseEvent)
            {
                $(document.body).addClass("start-fade-animation");

                lastElementClicked = HTMLElement;
            });

            Barba.Dispatcher.on('newPageReady', function(currentStatus, oldStatus, container, rawContainer)
            {
                var headTag     = document.getElementsByTagName('head')[0],

                    rgxp        = /<body([A-Za-z0-9\s\-_=",;.:]{0,})class="([A-Za-z0-9\s\-_]{0,})"/,
                    rgxpSlider  = /class="mod_rocksolid_slider/,

                    match       = rgxp.exec( rawContainer ),
                    matchSlider = rgxpSlider.exec( rawContainer ),

                    rgxpMaps    = /<script src="http(s|):\/\/maps.google.com\/maps\/api\/js\?key=([A-Za-z0-9,;.:\-_#$]{0,})&(amp;|)language=([a-z]{2})"><\/script>/,

                    matchMaps   = rgxpMaps.exec( rawContainer ),
                    matchCurr   = rgxpMaps.exec( headTag.innerHTML );

                    /* footer      = $("footer");*/

                if( match.length === 3 )
                {
                    /* setTimeout(function()
                    // {
                    //     $(document.body).attr("class", match[2]);
                    // }, 200); */

                    $(document.body).attr("class", match[2]);
                }

                /* if( matchSlider !== null && matchSlider.length )
                // {
                //     var sliderEl = [].slice.call( document.getElementsByClassName( 'mod_rocksolid_slider' ), -1 )[ 0 ];
                //
                //     initSlider( sliderEl );
                // }*/

                var useMaps = false;

                if( rawContainer.indexOf("maps.google.com") !== -1 )
                {
                    if( matchCurr === null && headTag.innerHTML.indexOf("maps.google.com") === -1 )
                    {
                        /* var rgxpMapsInit    = /<script>function
                         gmap1_initialize\(\){([A-Za-z0-9\s\-_=\{\},;.:\(\)!\'\"\[\]]{0,})<\/script>/,
                          */
                        /*     matchMapsInit   = rgxpMapsInit.exec( rawContainer ); */

                        var s = document.createElement("script");
                        s.type = "text/javascript";
                        s.src = "http" + matchMaps[1] + "://maps.google.com/maps/api/js?key=" + matchMaps[2] + "&language=" + matchMaps[4];
                        $(headTag).append(s);

                        /* var s1 = document.createElement("script");
                        // s1.innerHTML = 'function gmap1_initialize(){' + matchMapsInit[1];
                        // setTimeout(function() { $(document.body).append(s1); }, 1000);*/
                        useMaps = true;
                    }
                }

                var js = container.querySelectorAll("script");
                if(js !== null)
                {
                    if( useMaps )
                    {
                        setTimeout(function()
                        {
                            IIDO.Page.runScript( js );
                            /* eval(js.innerHTML);
                            // $.each(js, function(index, scriptTag)
                            // {
                            //     IIDO.Page.runScript( scriptTag, js );
                            // });*/
                        }, 500);
                    }
                    else
                    {
                        IIDO.Page.runScript( js );
                        /* eval(js.innerHTML);*/
                    }
                }

                if( typeof IIDO.Functions === "object" && typeof IIDO.Functions.init === "function")
                {
                    IIDO.Functions.init();
                }

                setTimeout(function()
                {
                    IIDO.Filter.init();
                }, 500);

                /* if( footer.length )
                // {
                //     if( footer.hasClass("home") )
                //     {
                //         // footer.animate({"opacity": 0}, 350, function()
                //         footer.fadeOut(350, function()
                //         {
                //             // footer.removeClass("home").animate({"opacity": 1});
                //             footer.removeClass("home");
                //         });
                //     }
                //     else
                //     {
                //         if( $(document.body).hasClass("homepage") || match[2].match(/homepage/) !== null )
                //         {
                //             // footer.animate({"opacity": 0}, 350, function()
                //             footer.fadeIn(350, function()
                //             // footer.animate({"opacity": 1}, 350, function()
                //             {
                //                 // footer.addClass("home").animate({"opacity": 1});
                //                 footer.addClass("home");
                //             });
                //         }
                //     }
                // }*/
            });



            /* var FadeTransition = Barba.BaseTransition.extend(
            //     {
            //     start: function()
            //     {
            //         /**
            //          * This function is automatically called as soon the Transition starts
            //          * this.newContainerLoading is a Promise for the loading of the new container
            //          * (Barba.js also comes with an handy Promise polyfill!)
            //          * /
            //
            //         // As soon the loading is finished and the old page is faded out, let's fade the new page
            //         Promise
            //             .all([this.newContainerLoading, this.fadeOut()])
            //             .then(this.fadeIn.bind(this));
            //     },
            //
            //     fadeOut: function()
            //     {
            //         /**
            //          * this.oldContainer is the HTMLElement of the old Container
            //          * /
            //
            //         return $(this.oldContainer).animate({ "margin-left": "-100%" }, 600).promise();
            //     },
            //
            //     fadeIn: function()
            //     {
            //         /**
            //          * this.newContainer is the HTMLElement of the new Container
            //          * At this stage newContainer is on the DOM (inside our #barba-container and with visibility: hidden)
            //          * Please note, newContainer is available just after newContainerLoading is resolved!
            //          * /
            //
            //         var _this = this;
            //         var $el = $(this.newContainer);
            //
            //         // $(this.oldContainer).hide();
            //
            //         $el.css({
            //             // visibility : 'visible',
            //             // opacity : 0
            //             "margin-left": '100%'
            //         });
            //
            //         $el.animate({ "margin-left": 0 }, 600, function() {
            //             /**
            //              * Do not forget to call .done() as soon your transition is finished!
            //              * .done() will automatically remove from the DOM the old Container
            //              * /
            //
            //             _this.done();
            //             // IIDO.Functions.init();
            //
            //             // if( !$(document.body).hasClass("homepage") )
            //             // {
            //             //     setTimeout("gmap1_initialize()", 500);
            //             // }
            //         });
            //     }
            // });
            //
            // /**
            //  * Next step, you have to tell Barba to use the new Transition
            //  * /
            //
            // Barba.Pjax.getTransition = function()
            // {
            //     /**
            //      * Here you can use your own logic!
            //      * For example you can use different Transition based on the current page or link...
            //      * /
            //
            //     return FadeTransition;
            // };
            */

            var MovePage = Barba.BaseTransition.extend({
                start: function()
                {
                    this.originalThumb = lastElementClicked;

                    Promise
                        .all([this.newContainerLoading, this.scrollTop()])
                        .then(this.movePages.bind(this));
                },

                scrollTop: function()
                {
                    var deferred = Barba.Utils.deferred();
                    var obj = { y: window.pageYOffset };

                    TweenLite.to(obj, 0.4, {
                        y: 0,
                        onUpdate: function()
                        {
                            if (obj.y === 0)
                            {
                                deferred.resolve();
                            }

                            window.scroll(0, obj.y);
                        },
                        onComplete: function()
                        {
                            deferred.resolve();
                        }
                    });

                    return deferred.promise;
                },

                movePages: function()
                {
                    var _this = this;
                    var goingForward = true;
                    /* this.updateLinks();*/

                    var oldLink             = Barba.HistoryManager.prevStatus().url.split('/').pop(),
                        clickedLinkParent   = $( this.originalThumb ).parent();

                    /* console.log( oldLink );
                    // console.log( clickedLinkParent );*/

                    if( clickedLinkParent.hasClass("image_container") )
                    {
                        clickedLinkParent = clickedLinkParent.parent();
                    }

                    if ( clickedLinkParent.hasClass("logo") )
                    {
                        goingForward = false;
                    }

                    if( (goingForward && oldLink === "") || !goingForward)
                    {
                        var headerTag = $("#header"),
                            winH = $(window).height();

                        TweenLite.set(this.newContainer, {
                            visibility: 'visible',
                            /* yPercent: goingForward ? 100 : -100,*/

                            position: 'fixed',
                            left: 0,
                            top: goingForward ? (winH - 120) : -(winH - 120),
                            right: 0,

                            width: '100vw',
                            height: '100vh'
                        });
                        TweenLite.set(this.oldContainer, {
                            visibility: 'visible',

                            position: 'fixed',
                            left: 0,
                            top: 0,
                            right: 0,

                            width: '100vw',
                            height: '100vh'
                        });
                        TweenLite.set(headerTag, {
                            top: goingForward ? (winH - 120) : 0
                        });

                        /* TweenLite.to(this.oldContainer, 0.6, { yPercent: goingForward ? -100 : 100 });*/
                        TweenLite.to(this.oldContainer, 0.6, { top: goingForward ? -(winH - 120) : (winH - 120) });

                        TweenLite.to(headerTag, 0.6, { top: goingForward ? 0 : (winH - 120)});

                        /* TweenLite.to(this.newContainer, 0.6, { top: winH });
                        // TweenLite.to(this.newContainer, 0.6, { yPercent: 0, onComplete: function() {*/
                        TweenLite.to(this.newContainer, 0.6, { top: 0, onComplete: function() {
                            TweenLite.set(_this.newContainer, { clearProps: 'all' });
                            _this.done();

                            $(document.body).removeClass("start-fade-animation");

                            IIDO.Page.initNavigationAfterFade( _this );

                            if( typeof IIDO.Functions === "object" && typeof IIDO.Functions.initAfterLoading === "function")
                            {
                                IIDO.Functions.initAfterLoading();
                            }
                        }});
                    }
                    else
                    {
                        var $el = $(this.newContainer);

                        $el.css({
                            position: 'fixed',
                            top: 0,
                            right: 0,
                            bottom: 0,
                            left: 0,

                            width: '100vw',
                            height: '100vh',

                            visibility : 'visible',
                            opacity : 0
                        });

                        $(this.oldContainer).animate({"opacity": 0}, 1000);

                        $el.animate({ "opacity": 1 }, 1000, function() {
                            _this.done();
                            TweenLite.set(_this.newContainer, { clearProps: 'all' });

                            $(document.body).removeClass("start-fade-animation");

                            IIDO.Page.initNavigationAfterFade( _this );

                            if( typeof IIDO.Functions === "object" && typeof IIDO.Functions.initAfterLoading === "function")
                            {
                                IIDO.Functions.initAfterLoading();
                            }
                        });
                    }
                }

                /* updateLinks: function()
                // {
                //     PrevLink.href = this.newContainer.dataset.prev;
                //     NextLink.href = this.newContainer.dataset.next;
                // },

                // getNewPageFile: function()
                // {
                //     return Barba.HistoryManager.currentStatus().url.split('/').pop();
                // }*/
            });

            Barba.Pjax.getTransition = function() {
                return MovePage;
            };



            /* var ContentPage = Barba.BaseView.extend({
            //     namespace: 'content-page',
            //     onEnter: function() {
            //         // The new Container is ready and attached to the DOM.
            //     },
            //     onEnterCompleted: function() {
            //         // The Transition has just finished.
            //     },
            //     onLeave: function() {
            //         // A new Transition toward a new page has just started.
            //     },
            //     onLeaveCompleted: function() {
            //         // The Container has just been removed from the DOM.
            //     }
            // });
            //
            // // Don't forget to init the view!
            // ContentPage.init();*/
        }
    };



    page.initNavigationAfterFade = function( fadeObject )
    {
        var parent = $(fadeObject.originalThumb).parent();

        if( parent.hasClass("image_container") )
        {
            parent = parent.parent();
        }

        if( parent.hasClass("logo") )
        {
            $(".nav-main ul.level_1 > li").removeClass("active").removeClass("trail").addClass("sibling");
        }
        else
        {
            parent.addClass("active").removeClass("sibling");
            parent.siblings().removeClass("active").addClass("sibling");
        }

        var activeMetaNav   = document.querySelector(".nav-meta ul > li.active"),
            activeMainNav   = document.querySelector(".nav-main ul > li.active");

        if( activeMetaNav )
        {
            var activeMetaNavLink       = activeMetaNav.querySelector('a'),
                activeMetaNavLinkHref   = activeMetaNavLink.href;


            if( activeMetaNavLinkHref !== location.href )
            {
                activeMetaNav.classList.remove("active");
                activeMetaNav.classList.add("sibling");

                activeMetaNavLink.classList.remove("active");
            }
        }

        if( activeMainNav )
        {
            var activeMainNavLink       = activeMainNav.querySelector('a'),
                activeMainNavLinkHref   = activeMainNavLink.href;

            if( activeMainNavLinkHref !== location.href )
            {
                activeMainNav.classList.remove("active");
                activeMainNav.classList.add("sibling");

                activeMainNavLink.classList.remove("active");
            }
        }
    };



    page.initShowArticles = function()
    {
        var arrPath         = location.pathname.split('/'),
            articleElement  = false,
            articleAlias    = "";

        $.each(arrPath, function(index, element)
        {
            if( articleElement )
            {
                articleAlias = element.replace(/.html$/, '');
                return false;
            }

            if( element === "artikel" || element === "article" )
            {
                articleElement = true;
            }
        });

        if( articleAlias.length )
        {
            var modArticle = $('.mod_article[data-alias="' + articleAlias + '"]');

            if( modArticle.length )
            {
                page.showArticle( modArticle.attr("id") );
            }
        }

        $('.nav-mobile-main li.article-link').each( function(index, element)
        {
            var el          = $(element),
                elLink      = el.find("a"),
                elHref      = elLink.attr("href"),
                linkParts   = elHref.split("/"),
                linkElement = false,
                linkAlias   = "";

            elLink.click( function()
            {
                $.each(linkParts, function(linkIndex, linkEl)
                {
                    if( linkElement )
                    {
                        linkAlias = linkEl.replace(/.html$/, '');
                        return false;
                    }

                    if( linkEl === "artikel" || linkEl === "article" )
                    {
                        linkElement = true;
                    }
                });

                if( linkAlias.length )
                {
                    var modArticle = $('.mod_article[data-alias="' + linkAlias + '"]');

                    if( modArticle.length )
                    {
                        /* var urlPath         = location.pathname.split('/'),
                        //     urlElement      = false,
                        //     urlAlias        = "";
                        //
                        // $.each(urlPath, function(urlIndex, urlEl)
                        // {
                        //     if( urlElement )
                        //     {
                        //         urlAlias = urlEl.replace(/.html$/, '');
                        //         return false;
                        //     }
                        //
                        //     if( urlEl === "artikel" || urlEl === "article" )
                        //     {
                        //         urlElement = true;
                        //     }
                        // });
                        //
                        // if( urlAlias.length )
                        // {
                        //     var modCurrentArticle = $('.mod_article[data-alias="' + urlAlias + '"]');
                        //
                        //     if( modCurrentArticle.length )
                        //     {
                        //         page.hideArticle( modCurrentArticle.attr("id") );
                        //     }
                        // }*/

                        $(".mod_article.shown").removeClass("shown").addClass("hide-area");

                        page.showArticle( modArticle.attr("id") );
                    }
                }
            });

        });
    };



    page.showArticle = function( aTag )
    {
        var article = $("#article-" + $(aTag).attr("data-id") );

        if( typeof aTag === "string" )
        {
            article = $('#' + aTag);
        }

        if( !article )
        {
            article = $(".mod_article#" + $(aTag).attr("data-alias") );
        }

        if( article.length )
        {
            article.addClass("shown").removeClass("hide-area");
            $(document.body).addClass("show-hidden-area");
        }
    };



    page.hideArticle = function( aTag )
    {
        var article = $("#article-" + $(aTag).attr("data-id") );

        if( typeof aTag === "string" )
        {
            article = $('#' + aTag);
        }

        if( !article )
        {
            article = $(".mod_article#" + $(aTag).attr("data-alias") );
        }

        if( article.length )
        {
            article.removeClass("shown").addClass("hide-area");
            $(document.body).removeClass("show-hidden-area");
        }
    };


    page.runScript = function( js )
    {
        $.each(js, function(index, scriptTag)
        {
            var scriptHTML = scriptTag.innerHTML;

            if( js.length > 1 && scriptHTML.indexOf("mod_rocksolid_slider") !== -1 )
            {
                var sliderElements = document.getElementsByClassName('mod_rocksolid_slider');

                if( sliderElements.length )
                {
                    $.each(sliderElements, function(seIndex, seElement )
                    {
                        if( seIndex === 0 )
                        {
                            eval( scriptHTML );
                        }

                        if( seIndex > 0 && seIndex < (sliderElements.length - 1) )
                        {
                            scriptHTML = scriptHTML.replace("{initSlider(sliderEl)}", "{var sliderElements =" +
                                " document.getElementsByClassName('mod_rocksolid_slider');if( sliderElements.length" +
                                " ){$.each(sliderElements, function(seIndex, seElement ){initSlider(seElement);}); }}");


                            /* initSlider( seElement );
                            // eval( scriptHTML );*/
                            setTimeout(function() {
                                eval( scriptHTML );
                            }, 300);
                        }
                    });
                }

                return true;
            }
            else
            {
                /* if( scriptHTML.indexOf('data-rsts-type="image"') === -1 && scriptHTML.indexOf('var arrImages_')
                 === -1) */
                if( scriptHTML.indexOf('data-rsts-type="image"') === -1 && scriptHTML.indexOf('data-rsts-type="video"') === -1 )
                {
                    /* console.log( scriptHTML );
                    // eval( scriptHTML );*/
                    setTimeout(function() {
                        eval( scriptHTML );
                    }, 300);
                }
            }
        });
    };



    page.initMobileNavigation = function()
    {
        $mobileNav  = $('.main-navigation-mobile');

        if( $mobileNav.length )
        {
            IIDO.Base.addEvent(window, 'resize', IIDO.Page.updateMobileNavigationHeight);
            $mobileNav.addClass('is-enabled');

            $openButton = $("a.main-navigation-mobile-open");

            IIDO.Base.addEvent($openButton, 'click', function(event)
            {
                IIDO.Base.eventPreventDefault(event);

                if ($mobileNav.hasClass('is-active'))
                {
                    IIDO.Page.closeMobileNavigation();
                }
                else
                {
                    IIDO.Page.openMobileNavigation();
                }
            });

            var closeButton = $mobileNav.find("button.main-navigation-mobile-close");

            IIDO.Base.addEvent(closeButton, 'click', function(event)
            {
                if( $mobileIsOpened )
                {
                    IIDO.Page.closeMobileNavigation();
                }
                IIDO.Base.eventPreventDefault(event);
            });

            var listContainers = $mobileNav.find('li.submenu'), button, listItem;

            $mobileNav.find("ul.level_1 > li > a,ul.level_2 > li > a").click( function() { if(!this.classList.contains("no-content") && !this.classList.contains("link-forward") && !this.classList.contains("link-toggler") ) { IIDO.Page.closeMobileNavigation(); } } );

            if( listContainers.length )
            {
                for (var i = 0; i < listContainers.length; i++)
                {
                    listItem    = $(listContainers[ i ]);
                    button      = listItem.find(" > button.main-navigation-mobile-expand");

                    IIDO.Base.addEvent(button, 'click', IIDO.Page.clickOnMobileNavigationButton);

                    if( listItem.hasClass('active') || listItem.hasClass('trail') )
                    {
                        listItem.addClass('is-expanded');
                    }
                    else
                    {
                        listItem.addClass('is-collapsed');
                    }
                }
            }
        }
    };



    page.updateMobileNavigationHeight = function()
    {
        if( $mobileIsOpened )
        {
            if( !$mobileNav.offsetHeight )
            {
                this.closeMobileNavigation();
            }
            else
            {
                $mobileNav.css("min-height", '');
            }
        }
    };



    page.closeMobileNavigation = function()
    {
        $mobileIsOpened = false;
        $mobileNav.removeClass('is-active');

        $openButton.removeClass('is-active');

        $("html").removeClass("noscroll");
        $(document.body).removeClass("mobile-menu-open");
    };



    page.openMobileNavigation = function()
    {
        $mobileIsOpened = true;
        $mobileNav.addClass('is-active');

        $openButton.addClass('is-active');

        /* IIDO.Page.updateMobileNavigationHeight();*/

        $("html").addClass("noscroll");
        $(document.body).addClass("mobile-menu-open");
    };



    page.clickOnMobileNavigationButton = function( event )
    {
        var element = this.parentNode;

        if( element === undefined || element === "undefined" || element === null)
        {
            element = $(event.currentTarget).parent("li")
        }

        IIDO.Base.toggleElementClass(element, 'is-expanded');
        IIDO.Base.toggleElementClass(element, 'is-collapsed');

        /* IIDO.Page.updateMobileNavigationHeight();*/
    };



    page.initMobile = function()
    {
        var eventMenu   = $(".mod_eventmenu"),
            eventFilter = $(".mod_mae_event_filter a, .btn-reset a, .mod_eventmenu li a");

        if( $(window).width() <= 420 )
        {
            if( eventMenu.length )
            {
                eventMenu.find("h3").click(function() { eventMenu.toggleClass("open"); });
            }

            if( eventFilter.length )
            {
                eventFilter.click( function(e) { IIDO.Page.scrollTo(e, $(".mod_article#article-31") ); });
            }
        }
        else
        {
            if( eventMenu.length )
            {
                eventMenu.find("h3").unbind("click");
            }
        }
    };



    page.initLinks = function()
    {
        var e = window.event;

        var scrollLinks = $("a.scroll-to");

        if( scrollLinks.length )
        {
            scrollLinks.each( function(index, linkElement)
            {
                var link        = $(linkElement);

                link.click( function(e) { IIDO.Page.scrollLinkClicked(e, link); } );
            });
        }
    };



    page.scrollLinkClicked = function(e, link)
    {
        var linkHref    = link.attr("href");

        if( linkHref === '#next-article' || linkHref === '#article-next' )
        {
            var linkParent = link.parent(".content-element").parent().parent(".mod_article");

            if( linkParent.length )
            {
                var linkParentNext = linkParent.next(".mod_article");

                if( linkParentNext.length )
                {
                    IIDO.Page.scrollTo(e, linkParentNext);
                }
            }
        }
        else
        {
            IIDO.Page.scrollTo(e, link);
        }
    };



    page.initSticky = function()
    {
        var stickyElements = document.querySelectorAll(".is-sticky-element");

        if( stickyElements.length )
        {
            Array.prototype.forEach.call(stickyElements, function(element, index)
            {
                var stickyConfig = {
                    element: $(element),
                    handler: function( direction )
                    {
                        /*/ var footer = pageTitle.parent().next(".footer");
                        // $(document.body).toggleClass("fixed-page-title");
                        // pageContainer.toggleClass("is-fixed");*/
                    },
                    offset: function()
                    {
                        var stickyOffset    = 0,
                            stickyElement   = element; //this.element.children[0]

                        Array.prototype.forEach.call(stickyElement.classList, function(classTitle)
                        {
                            if( classTitle.indexOf("sticky-offset") !== -1 )
                            {
                                stickyOffset = parseInt( classTitle.replace('sticky-offset-', '') );
                            }
                        });

                        if( stickyOffset === 0 )
                        {
                            var dataOffset = parseInt( stickyElement.getAttribute("data-offset") );

                            if( dataOffset > 0 )
                            {
                                stickyOffset = dataOffset;
                            }
                        }

                        if( stickyOffset === 0 )
                        {
                            var style = stickyElement.currentStyle || window.getComputedStyle(stickyElement);
                            stickyOffset = parseInt( style.marginTop );
                        }

                        return -(stickyOffset);
                    }
                };

                var addWrapper = $(element).attr("data-wrapper");

                if( addWrapper && addWrapper === "no" )
                {
                    stickyConfig.wrapper = null;
                }

                var stickyElement = new Waypoint.Sticky(stickyConfig);

            });
        }
    };



    page.initSearch = function()
    {
        $searchForm  = $("div.fullscreen-search-form");

        if( $searchForm.length )
        {
            var searchLinks = $("a.open-fullscreen-search,.ce_hyperlink.open-fullscreen-search a");

            if( searchLinks.length )
            {
                searchLinks.each( function(index, element) {
                    var el      = $(element);

                    IIDO.Base.addEvent(element, "click", IIDO.Page.openFullscreenSearch);
                });
            }

            IIDO.Base.addEvent($searchForm.find("a.fullscreen-search-form-close")[0], "click", IIDO.Page.closeFullscreenSearch);
        }
    };



    page.openFullscreenSearch = function( event )
    {
        IIDO.Base.eventPreventDefault(event);
        $searchForm.addClass("is-pre-active");

        var trash = $searchForm.offsetWidth;

        $searchForm.addClass("is-active");
        $searchForm.removeClass("is-pre-active");

        setTimeout( function() { $searchForm.find("input.text").focus(); }, 500 );

        document.body.classList.add("open-fullscreen-search");

        $("html").addClass("noscroll");
    };



    page.closeFullscreenSearch = function( event )
    {
        IIDO.Base.eventPreventDefault(event);
        $searchForm.removeClass("is-active");

        document.body.classList.remove("open-fullscreen-search");

        $("html").removeClass("noscroll");
    };

})(window, jQuery, IIDO.Page);

/* Document Ready */
/* $(function () */
document.addEventListener("DOMContentLoaded", function()
{
    IIDO.Page.init();
});