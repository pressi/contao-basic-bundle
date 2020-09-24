/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Page = IIDO.Page || {};

(function ($, page)
{
    var $mobileNav, $openButton, $searchForm, $mobileIsOpened = false,
        $navOffset = 135,

        $lbOpen = false, $openLinkTag;



    page.init = function()
    {
        this.initSearch();
        this.initPageScroll();
        this.initMobileNavigation();
        this.initScrollLinks();
        this.initNavigtion();
        this.initFullPage();

        $("a.open-page-in-lightbox,a.open-in-lightbox,.ce_hyperlink.open-in-lightbox a.hyperlink_txt").click( function(e) { e.preventDefault(); IIDO.Page.openPageInLightbox(e); } );
    };



    page.initNavigtion = function()
    {
        let offsetNavigation    = document.querySelector('.offset-navigation-container'),
            offsetNavToggler    = document.querySelectorAll('.offset-navigation-toggler');

        if( offsetNavigation && offsetNavToggler.length )
        {
            let offsetClose = offsetNavigation.querySelector('.close');

            if( offsetClose )
            {
                offsetClose.addEventListener('click', function(e)
                {
                    offsetNavigation.classList.remove('open');
                    document.body.classList.remove('open-offset-navigation');
                });
            }

            if( offsetNavToggler.length )
            {
                offsetNavToggler.forEach( toggler => {
                    toggler.addEventListener('click', function( e )
                    {
                        if( offsetNavigation.classList.contains('open') )
                        {
                            offsetNavigation.classList.remove('open');
                            document.body.classList.remove('open-offset-navigation');
                        }
                        else
                        {
                            offsetNavigation.classList.add('open');
                            document.body.classList.add('open-offset-navigation');
                        }
                    });
                });
            }

            document.addEventListener('click', function(e)
            {
                let currentItem             = $(e.target);

                let isOffsetNav             = false;
                let offsetNavContParent     = currentItem.parents('.offset-navigation-container');
                let offsetNav               = currentItem.hasClass('offset-navigation-container');

                let isToggler               = false;
                let offsetNavContToggler    = currentItem.parents('.offset-navigation-toggler');
                let offsetToggler           = currentItem.hasClass('offset-navigation-toggler');

                if( offsetToggler || offsetNavContToggler.length )
                {
                    isToggler = true;
                }

                if( offsetNav || offsetNavContParent.length )
                {
                    isOffsetNav = true;
                }

                if( !isOffsetNav && !isToggler )
                {
                    offsetNavigation.classList.remove('open');
                    document.body.classList.remove('open-offset-navigation');
                }
            })
        }
    };



    page.initPageScroll = function()
    {
        this.checkScrollState();

        window.addEventListener("scroll", function( e )
        {
            IIDO.Page.checkScrollState();
        });
    };



    page.checkScrollState = function()
    {
        let scrollY         = parseInt( IIDO.Base.getBodyScrollTop() );
        let headerHeight    = parseInt( document.getElementById('header').offsetHeight );

        if( scrollY >= 50 )
        {
            document.body.classList.add("scrolled");

            // if( navMainRight )
            // {
            //     if( document.body.classList.contains("open-search-container") )
            //     {
            //         IIDO.Page.closeFullscreenSearch();
            //     }
            //
            //     navMainRight.classList.remove("open");
            //     document.body.classList.remove("open-right-navigation");
            //     document.body.classList.remove("open-hidden-page-navigation");
            //     document.body.classList.remove("open-search-container");
            //     document.body.classList.remove("open-filter-search");
            //
            //     var fpc = document.getElementById('filterPosCont');
            //
            //     if( fpc )
            //     {
            //         fpc.classList.remove('shown');
            //     }
            // }
        }
        else
        {
            document.body.classList.remove("scrolled");
        }

        if( scrollY >= (headerHeight + 25) )
        {
            document.body.classList.add('header-scrolled-out');
        }
        else
        {
            document.body.classList.remove('header-scrolled-out');
        }
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



    page.initScrollLinks = function()
    {
        $('a.scroll-to').click(function(e)
        {
            e.preventDefault();

            IIDO.Page.scrollTo(e, e.currentTarget);

            return false;
        });
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

        // $("html").removeClass("noscroll");
        // $(document.body).removeClass("mobile-menu-open");

        document.documentElement.classList.remove('noscroll');
        document.body.classList.remove('mobile-menu-open');
    };



    page.openMobileNavigation = function()
    {
        $mobileIsOpened = true;

        $mobileNav.addClass('is-active');
        $openButton.addClass('is-active');

        /* IIDO.Page.updateMobileNavigationHeight();*/

        // $("html").addClass("noscroll");
        // $(document.body).addClass("mobile-menu-open");

        document.documentElement.classList.add('noscroll');
        document.body.classList.add('mobile-menu-open');
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



    page.initSearch = function()
    {
        $searchForm  = $("div.fullscreen-search-form");

        if( $searchForm.length )
        {
            var searchLinks = $("a.open-fullscreen-search,.ce_hyperlink.open-fullscreen-search a,div.search.open-fullscreen-search");

            if( searchLinks.length )
            {
                searchLinks.each( function(index, element) {
                    var el      = $(element);

                    // IIDO.Base.addEvent(element, "click", IIDO.Page.openFullscreenSearch);
                });
            }

            IIDO.Base.addEvent($searchForm.find("a.fullscreen-search-form-close")[0], "click", IIDO.Page.closeFullscreenSearch);
        }
    };



    page.openFullscreenSearch = function( event )
    {
        if( document.body.classList.contains("open-fullscreen-search") )
        {
            page.closeFullscreenSearch( event );
        }
        else
        {
            IIDO.Base.eventPreventDefault(event);
            $searchForm.addClass("is-pre-active");

            var trash = $searchForm.offsetWidth;

            $searchForm.addClass("is-active");
            $searchForm.removeClass("is-pre-active");

            setTimeout( function() { $searchForm.find("input.text").focus(); }, 500 );

            document.body.classList.add("open-fullscreen-search");

            // $("html").addClass("noscroll");
        }
    };



    page.closeFullscreenSearch = function( event )
    {
        IIDO.Base.eventPreventDefault(event);
        $searchForm.removeClass("is-active");

        document.body.classList.remove("open-fullscreen-search");

        // $("html").removeClass("noscroll");
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

        var isString = false;

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
            target = $(aTag).attr("href");
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
            // url             = '/SimpleAjaxFrontend.php?c=IIDO_BasicBundle_Ajax_Page&f=renderPageContent&v=' + page;
            url             = page;

        // if( el.hasClass("open-type-iframe") || el.hasClass("open-iframe") || el.attr("type") === "iframe"  || el.attr("open-type") === "iframe" )
        // {
        //     openType    = "iframe";
        //     url         = page;
        // }

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
            }
        };

        var slideClassName = "page-lightbox";

        if( el.hasClass("event-link") )
        {
            slideClassName = "event-page page-lightbox";
        }

        if( openType === "ajax" )
        {
            options = $.extend({}, options, {opts:{ajax:{settings:{data:{fancybox:true}}},fitToView:true,width:'100%',height:'100%',margin:0,selector:'#main > .inside',filter:'#main > .inside',slideClass:slideClassName}});
        }

        options.opts.beforeShow = function(e)
        {
            /* $( "html" ).addClass( "fancybox-lock" );*/

            if( document.documentElement.classList.contains('fp-enabled') )
            {
                $.fn.fullpage.setAllowScrolling(false);
            }
        };

        // options.opts.afterShow = function(){};

        options.opts.beforeClose = function()
        {
            if( document.documentElement.classList.contains('fp-enabled') )
            {
                $.fn.fullpage.setAllowScrolling(true);
            }
        };

        options.opts.afterClose = function()
        {
            /* $( "html" ).removeClass( "fancybox-lock" );
            //
            // if( activeTag.length )
            // {
            //     activeTag.removeClass( "active" );
            // }*/
        };

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



    page.initFullPage = function()
    {
        let nav = document.querySelector('.fullpage-navigation');

        if( nav )
        {
            let prev = nav.querySelector('.nav-prev');
            let next = nav.querySelector('.nav-next');

            prev.addEventListener('click', function()
            {
                $.fn.fullpage.moveSectionUp();
            });

            next.addEventListener('click', function()
            {
                $.fn.fullpage.moveSectionDown();
            });
        }

        let cont = document.getElementById('main').querySelector('.inside');
        let sections = cont.querySelectorAll('section.section');

        if( sections.length )
        {
            sections.forEach( function(element, index)
            {
                element.querySelector('.section-index').innerHTML = (index + 1);
            });
        }
    }

})(jQuery, IIDO.Page);

document.addEventListener("DOMContentLoaded", function(event)
{
    IIDO.Page.init();
});