/**********************************************************/
/*                                                        */
/*  (c) 2017 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Page = IIDO.Page || {};

(function (window, $, page)
{

    var $header, $wrapper, $searchForm, $mobileNav, $mobileIsOpened, $tagline,
        $lbOpen = false, $openLinkTag = false, $headOptions = {},

        $navOffset = 0;



    page.init = function()
    {
        $wrapper    = $("#wrapper");
        $navOffset  = $("header").height();

        // this.initHeader();
        // this.initSearch();
        // this.initMobileNavigation();
        this.initNavigation();
        this.initOnePage();
        this.initFullPage();
        // this.initFooter();

        if( $(document.body).hasClass("url-change") )
        {
            this.initArticles();
            this.initScroll();
        }

        $("a.open-in-lightbox").click( function(e) { e.preventDefault(); IIDO.Page.openPageInLightbox(e); } );
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

                    if( (el.hasClass("first") && dataArticles.length) || el.attr("data-menu") == "1" )
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

                            IIDO.Page.changeUrl( linkTag, false, true );
                        }
                        else if( $(window).scrollTop() < elPosTop)
                        {
                            // if( !el.hasClass("first") )
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
                            // }
                        }
                    }
                })
            }
        });
    };



    page.initArticles = function()
    {
        var menuArticles    = $("main").find('.mod_article[data-menu="1"]');

        if( menuArticles.length )
        {
            var urlPath         = location.pathname,
                urlParthParts   = urlPath.split("/"),
                useScroll       = false,
                articleAlias    = "",
                scrollTop       = $(window).scrollTop(),
                urlParam        = "aritkel";

            if( $(document.body).hasClass("lang-en") )
            {
                urlParam = "article";
            }

            // var rgxp    = new RegExp(urlParam, 'g'),
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
            // }

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
                    // IIDO.Page.scrollTo( window.event, el );
                    $(window).scrollTop( el.position().top );
                    return true;
                }
            });
        }
    };



    page.initNavigation = function()
    {
        var articleMenu     = $("ul.article-menu");

        if( articleMenu.length )
        {
            articleMenu.each( function(index, element)
            {
                var el          = $(element),
                    linkMain    = el.prev("a"),
                    elChilds    = el.children("li");

                if( !linkMain.length )
                {
                    linkMain = el.prev("strong");
                }

                linkMain.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( linkMain, true, true ); });

                if( elChilds.length )
                {
                    elChilds.each( function(i, elem)
                    {
                        var link = $(elem).find("> a");

                        link.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( link, true, true ); });
                    });
                }
            });
        }

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

                        childLink.click( function(e) { e.preventDefault(); IIDO.Page.changeUrl( childLink, true, true ); });
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

        // if(newUrl.match(rgxp))
        // {
        //     newUrl  = newUrl.replace(rgxpUrl, urlParam + "/" + $(element).attr("data-alias")) + '.html';
        // }
        // else
        // {
        //     newUrl  = newUrl.replace(".html", "") + '/' + urlParam + '/' + $(element).attr("data-alias") + '.html';
        // }
        if(url !== newUrl)
        {
            // newTitle = newTitle.replace(/(.*)::/,   '$1 - ' + linkTitle + ' ::');

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
            //TODO: make options changeable in backend
            $('#main').fullpage(
                {
                    verticalCentered                    : true,
                    resize                              : false,
                    // slidesColor                         : ['#ccc', '#fff'],
                    // anchors                             : ['startseite', 'handwerk', 'projekte', 'holzformer','kontakt'],// TODO: make flexible
                    scrollingSpeed                      : 700,
                    easing                              : 'easeInQuart',
                    menu                                : ".nav-main",
                    navigation                          : false,
                    // navigationPosition                  : 'right',
                    // navigationTooltips                  : ['firstSlide', 'secondSlide'],
                    slidesNavigation                    : false,
                    // slidesNavPosition                   : 'bottom',
                    loopBottom                          : false,
                    loopTop                             : false,
                    loopHorizontal                      : false,
                    autoScrolling                       : true,
                    scrollOverflow                      : false,
                    css3                                : true,
                    // paddingTop                          : '3em',
                    // paddingBottom                       : '70px',
                    // fixedElements                       : '#element1, .element2',
                    // normalScrollElements                : '#element1, .element2',
                    normalScrollElementTouchThreshold   : 5,
                    keyboardScrolling                   : true,
                    touchSensitivity                    : 15,
                    continuousVertical                  : false,
                    animateAnchor                       : true

                    // onLeave: function(index, nextIndex, direction)
                    // {
                    // },

                    // afterLoad: function(anchorLink, index)
                    // {
                    // }
                }
            );
        }
    };



    page.scrollTo = function( event, aTag, aOffset )
    {
        event.preventDefault();

        var target  = "#" + (($(aTag).attr("data-anker") === undefined || $(aTag).attr("data-anker") === "undefined"||$(aTag).attr("data-anker") === null)?$(aTag).attr("id"):$(aTag).attr("data-anker")),
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
            $.smoothScroll({
                offset          : offset,
                scrollTarget    : target
            });
        }

        $(".image-point.open").removeClass("open");

        return false;
    };



    page.openPageInLightbox = function(e)
    {
        e.preventDefault();

        var el              = $( e.currentTarget),
            openType        = 'ajax',
            canBeOpen       = true,
            siblingLinks    = el.parent("li" ),
            page            = el.attr("href"), //$( e.currentTarget ).href, //.split("/").pop().replace('.html', ''),
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
                //minWidth : "100%",
                // height    : 600,
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
                    // $( "html" ).addClass( "fancybox-lock" );
                },

                afterShow : function()
                {
                },

                beforeClose : function()
                {
                },

                afterClose : function()
                {
                    // $( "html" ).removeClass( "fancybox-lock" );
                    //
                    // if( activeTag.length )
                    // {
                    //     activeTag.removeClass( "active" );
                    // }
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
            options = $.extend({}, options, {opts:{fitToView:true,width:'100%',height:'100%',margin:0,selector:'#main .inside',slideClass:slideClassName}});
        }

        if( el.hasClass("fit") || el.hasClass("fit-to-view") || el.hasClass("ftv") )
        {
            // options = $.extend({}, options, {opts:{fitToView:true,width:'100%',height:'100%',margin:0,wrapCSS:"news-lightbox"}});


            options.opts.afterLoad = function(current, previous) {
                setTimeout( function() {

                    var $container = $('#loadingList');

                    // $container.isotope({
                    //     itemSelector: '.layout_latest',
                    //     layoutMode: 'masonry',
                    //     columnWidth: '100%',
                    //     gutter: 0
                    // });

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
                            // $container.isotope('layout');
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
            // options = $.extend({}, options, {opts:{fitToView:true,width:'100%',height:'100%',margin:0,wrapCSS:"page-lightbox"}});
        }

        // if( el.hasClass("icon-search") )
        // {
        //     options = $.extend({}, options, {closeBtn:false,modal:false,wrapCSS:"search-modal"});
        // }

        if($lbOpen)
        {
            if( el.hasClass("active") )
            {
                siblingLinks.each(function(index, element) { $(element).find("a").removeClass("active") });
                $.fancybox.close();

                canBeOpen   = false;
                $lbOpen     = false;
            }
            else
            {
                $.fancybox.close();
                $lbOpen = false;

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

})(window, jQuery, IIDO.Page);

// Document Ready
$(function ()
{
    IIDO.Page.init();
});