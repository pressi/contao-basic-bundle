window.addEvent("domready", function ()
{
    var hasGridElements = false;

    $$(".tl_content div.layout-element").each(function (el)
    {
        const parent    = el.getParent("li");
        const classes   = el.classList;
        const gridClass = [];

        for(var i in classes)
        {
            if (classes[ i ].indexOf && (classes[ i ].indexOf("col-") === 0 || classes[ i ].indexOf("layout-") === 0))
            {
                gridClass.push(classes[ i ]);
            }
        }

        const strGridClasses = gridClass.join(" ");

        el.removeClass( strGridClasses );
        parent.addClass( strGridClasses );

        hasGridElements = true;
    });

    if( hasGridElements )
    {
        $$(".tl_listing_container.parent_view > ul").addClass("row");
        $$("html").addClass("grid-page");
    }

    // $$('.subpal').each( function( subEl )
    // {
    //     if( subEl.getStyle('display') === 'block' )
    //     {
    //         subEl.getPrevious('.widget').addClass('subfields-open');
    //     }
    // });

    // $$('.widget.subfields').each( function( wiSfEl )
    // {
    //     wiSfEl.getElement('input.tl_checkbox').addEvent('change', function( event )
    //     {
    //         let widgetEl = event.target.getParent().getParent('.widget');
    //
    //         if( widgetEl.getNext('.subpal').getStyle('display') === 'block' )
    //         {
    //             widgetEl.addClass('subfields-open');
    //         }
    //         else
    //         {
    //             widgetEl.removeClass('subfields-open');
    //         }
    //     });
    // });
});