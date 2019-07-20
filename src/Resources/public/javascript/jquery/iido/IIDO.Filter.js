/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.Filter = IIDO.Filter || {};

(function (window, $, filter)
{
    var $grid, $buttonGroup, $hasPagination = false, $activePage = 0;



    filter.init = function()
    {
        var perRow = 6;

        if( window.innerWidth <= 800 )
        {
            $hasPagination = false;
            perRow = 4;
        }

        var filterList          = $(".ce_rsce_project_filter"),
            filterContainer     = filterList.parent(".article-inside"); //$(".project-container-inside");

        if( !filterList.length )
        {
            filterList = $('.filter-list');
        }

        if( filterList.length && filterList.hasClass("load-items-from-next-article") )
        {
            filterContainer = filterContainer.parent(".mod_article").next(".mod_article").find(".article-inside");
        }

        if( document.querySelector(".project-outside-container") )
        {
            filterContainer = $( document.querySelector(".project-inside-container") );
        }

        if( filterList.length && filterContainer.length === 0 )
        {
            filterContainer = filterList.parent(".article-inside");
        }

        if( filterList.length && filterContainer )
        {
            var isoConfig = {
                itemSelector: '.project-item',
                stamp: filterList,
                layoutMode: 'masonry',
                percentPosition: true,
                masonry:
                {
                    columnWidth: (filterContainer.width() / perRow)
                }
            };

            if( $hasPagination )
            {
                isoConfig.filter = '.page-0';
            }

            // init Isotope
            $grid = filterContainer.isotope( isoConfig );

            // store filter for each group
            var filters = {};

            filterList.on( 'click', '.filter-btn', function(e)
            {
                e.preventDefault();

                var $this       = $(this),
                    filterName  = $this.attr("data-filter"),
                    activePage  = $grid.attr("data-page");

                if( $this.hasClass("is-disabled") )
                {
                    return false;
                }

                if( activePage === "undefined" || activePage === undefined || activePage === null )
                {
                    activePage = 0;
                }

                if( $this.hasClass('is-checked') )
                {
                    $this.removeClass('is-checked');
                    $this.siblings(".all").addClass("is-checked");
                }
                else
                {
                    $this.addClass('is-checked');
                    $this.siblings().removeClass('is-checked');
                }

                // get group key
                var $buttonGroup    = $this.parents('.filter-group'),
                    filterGroup     = $buttonGroup.attr('data-filter-group');

                var filterUrl = filterList.attr("data-url");

                if( filterUrl && filterUrl.length )
                {
                    location.href = filterUrl + '?' + filterGroup + '=' + filterName.replace(/^.mainfilter\-/, '');
                }

                if( filterGroup === "mainfilter" )
                {
                    var subFilterGroup  = $buttonGroup.parent(".filter").next(".sub-filter"),
                        checkedFilter   = $buttonGroup.find(".is-checked");

                    if( !checkedFilter.hasClass("all") )
                    {
                        subFilterGroup.addClass("main-is-active");

                        subFilterGroup.find("a:not(.all)").removeClass("is-disabled").each( function(index, element)
                        {
                            var el              = $(element),
                                mainFilter      = el.attr("data-mainfilter"),
                                strMainFilter   = filterName.replace(/^.mainfilter\-/, ''),
                                arrFilters      = mainFilter.split(",");

                            if( $.inArray(strMainFilter, arrFilters) )
                            {
                                el.addClass("is-disabled");
                            }
                        })
                    }
                    else
                    {
                        subFilterGroup.removeClass("main-is-active");
                        subFilterGroup.find("a.all").trigger("click");
                        subFilterGroup.find("a:not(.all)").removeClass("is-disabled");
                    }
                }

                // set filter for group
                if( filters[ filterGroup ] === filterName )
                {
                    filters[ filterGroup ] = '';
                }
                else
                {
                    filters[ filterGroup ] = filterName;
                }

                // combine filters
                var filterValue = IIDO.Filter.concatValues( filters );

                if( $hasPagination )
                {
                    filterValue = filterValue + '.page-0';
                }

                // set filter for Isotope
                $grid.isotope({ filter: filterValue });
            });

            if( $hasPagination )
            {
                this.initPagination();
            }
        }

        // // change is-checked class on buttons
        // $('.filter-group').each( function( i, buttonGroup )
        // {
        //     var $buttonGroup = $( buttonGroup );
        //
        //     $buttonGroup.on( 'click', 'a', function()
        //     {
        //         if( $(this).hasClass("is-disabled") )
        //         {
        //             return false;
        //         }
        //
        //         if( $( this ).hasClass('is-checked') )
        //         {
        //             $( this ).removeClass('is-checked');
        //         }
        //         else
        //         {
        //             $buttonGroup.find('.is-checked').removeClass('is-checked');
        //             $( this ).addClass('is-checked');
        //         }
        //     });
        // });

        if( filterList.length && filterList.hasClass("open-on-click") )
        {
            filterList.find(".filter .label").on('click', function()
            {
                if( $(this).parent().hasClass("open") )
                {
                    $(this).parent().removeClass("open");
                    $(this).parent().find(".filter-btn.all").trigger("click");
                }
                else
                {
                    $(this).parent().addClass("open");
                }
            });
        }
    };



    // flatten object by concatting values
    filter.concatValues = function( obj )
    {
        var value = '';
        for ( var prop in obj )
        {
            value += obj[ prop ];
        }
        return value;
    };



    filter.addPagination = function()
    {
        if( window.innerWidth > 800 )
        {
            $hasPagination = true;
        }
    };



    filter.initPagination = function()
    {
        var items           = $grid.find(".project-item");

        if( items.length && items.length > 18 )
        {
            var contPagination  = document.createElement("div"),

                pageNext        = document.createElement("div"),
                pagePrev        = document.createElement("div");

            contPagination.classList.add("project-pagination-container");

            pageNext.classList.add("project-next-page");
            pagePrev.classList.add("project-prev-page");

            contPagination.appendChild(pagePrev);
            contPagination.appendChild(pageNext);

            pagePrev.addEventListener("click", function() { IIDO.Filter.toPrevPage(); });
            pageNext.addEventListener("click", function() { IIDO.Filter.toNextPage(); });

            $(contPagination).insertBefore($grid.find(".project-outside-container"));

            var piHeight    = items[0].offsetHeight,
                contHeight  = (piHeight * 3);

            // cont.classList.add("initialized");

            var posTop = (contHeight / 2);

            pagePrev.style.top = posTop + 'px';
            pageNext.style.top = posTop + 'px';
        }
    };



    filter.toPrevPage = function()
    {
        var activePage = this.getActivePage();

        activePage = (activePage - 1);

        if( activePage < 0 )
        {
            activePage = 0;
        }

        this.goToPage( activePage );
    };



    filter.toNextPage = function()
    {
        var activePage  = this.getActivePage(),
            maxPages    = this.getMaxPages();

        activePage = (activePage + 1);

        if( activePage > maxPages )
        {
            activePage = maxPages;
        }
        this.goToPage( activePage );
    };



    filter.goToPage = function( toPage )
    {
        var activePage      = this.getActivePage(),
            activeFilter    = $grid.data("isotope").options.filter;

        if( activeFilter === "undefined" || activeFilter === undefined || activeFilter === null )
        {
            activeFilter = '';
        }

        // if( activePage !== toPage )
        // {
            activeFilter = activeFilter.replace(/.page-([0-9]{1,})/, '') + '.page-' + toPage;
        // }

        $grid.isotope({ filter: activeFilter });

        $grid.attr("data-page", toPage);
    };



    filter.getActivePage = function()
    {
        var activePage = $grid.attr("data-page");

        if( activePage === "undefined" || activePage === undefined || activePage === null )
        {
            activePage = 0;
        }

        return parseInt( activePage );
    };



    filter.getMaxPages = function()
    {
        var items           = $grid.find(".project-item"),
            activeFilter    = $grid.data("isotope").options.filter;

        if( activeFilter.match(/filter/) !== null )
        {
            items = $grid.data("isotope").filteredItems;
        }

        return (Math.ceil(items.length / 18) - 1);
    };



})(window, jQuery, IIDO.Filter);

// document.addEventListener("DOMContentLoaded", function()
// {
//     IIDO.Filter.init();
// });
window.addEventListener("load", function load(event)
{
    window.removeEventListener("load", load, false); // Listener entfernen, da nicht mehr benötigt
    IIDO.Filter.init();
}, false);