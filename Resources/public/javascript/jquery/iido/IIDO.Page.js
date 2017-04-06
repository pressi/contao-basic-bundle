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
        $lbOpen = false, $openLinkTag = false, $headOptions = {};



    page.init = function()
    {
        $wrapper    = $("#wrapper");

        // this.initHeader();
        // this.initSearch();
        // this.initMobileNavigation();
        this.initOnePage();
        this.initFullPage();
        // this.initFooter();

        $("a.open-in-lightbox").click( function(e) { e.preventDefault(); IIDO.Page.openPageInLightbox(e); } );
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

})(window, jQuery, IIDO.Page);

// Document Ready
$(function ()
{
    IIDO.Page.init();
});