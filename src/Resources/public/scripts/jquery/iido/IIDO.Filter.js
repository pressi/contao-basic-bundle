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
    var $grid, $buttonGroup;

    filter.init = function()
    {
        let filterList = $(".ce_rsce_project_filter");

        if( !filterList.length )
        {
            filterList = $('.filter-list');
        }

        let filterContainer = filterList.next('.row-container')

        if( filterList.length && filterList.hasClass("load-items-form-next-article") )
        {
            filterContainer = filterContainer.parent(".mod_article").next(".mod_article").find(".article-inside");
        }

        if( !filterContainer )
        {
            filterContainer     = filterList.parent(".article-inside"); //$(".project-container-inside");
        }

        if( filterContainer.hasClass('row-container') )
        {
            filterContainer = filterContainer.find('.filter-list-container');
        }

        if( filterList.length && filterContainer )
        {
            // init Isotope
            $grid = filterContainer.isotope(
            {
                itemSelector: '.filter-item',
                // stamp: '.project-filter',
                // layoutMode: 'masonry',
                layoutMode: 'fitRows'
                // percentPosition: true,
                // masonry: {
                // columnWidth: (filterContainer.width() / 6)
                // }
            });

            // store filter for each group
            var filters = {};

            filterList.on( 'click', '.filter-btn', function(e)
            {
                e.preventDefault();

                var $this       = $(this),
                    filterName  = $this.attr("data-filter");

                if( $this.hasClass("is-disabled") )
                {
                    return false;
                }

                if( $this.hasClass('is-checked') )
                {
                    if( !$this.hasClass('all') )
                    {
                        $this.removeClass('is-checked');
                        $this.siblings('.all').addClass('is-checked');
                    }
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

                // set filter for Isotope
                $grid.isotope({ filter: filterValue });
            });
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
            filterList.find(".filter .label").click( function()
            {
                if( $(this).parent().hasClass("open") )
                {
                    $(this).parent().removeClass("open");
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