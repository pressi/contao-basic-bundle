/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.ProductGallery = IIDO.ProductGallery || {};

(function (window, $, productGallery)
{
    var $container, $products = [], $activeIndex = 1, $maxIndex = 1, $prev, $next, $counter;

    productGallery.init = function( containerID )
    {
        $container = document.getElementById( "productGallery_" + containerID );

        $prev = document.getElementById( "proGalPrev_" + containerID );
        $next = document.getElementById( "proGalNext_" + containerID );

        $prev.addEventListener("click", function()
        {
            IIDO.ProductGallery.prev();
        });

        $next.addEventListener("click", function()
        {
            IIDO.ProductGallery.next();
        });

        $counter = document.getElementById("productCounter_" + containerID);
    };



    productGallery.addProducts = function( products )
    {
        $products = products;

        $maxIndex = $products.length;
    };



    productGallery.next = function()
    {
        $activeIndex++;

        if( $activeIndex >= $maxIndex )
        {
            $activeIndex = $maxIndex;
        }

        this.updateCounter();
        this.loadGalleryItem();
    };



    productGallery.prev = function()
    {
        $activeIndex--;

        if( $activeIndex <= 1 )
        {
            $activeIndex = 1;
        }

        this.updateCounter();
        this.loadGalleryItem();
    };



    productGallery.updateCounter = function()
    {
        $counter.querySelector(".current").innerHTML = $activeIndex;
    };



    productGallery.loadGalleryItem = function()
    {
        var currentItem = $container.querySelector(".image_container"),
            newItem     = document.createElement("div");

        newItem.classList.add("image_container");
        newItem.classList.add("hidden");

        // newItem.innerHTML = '<div class="ctable"><div class="ctable-cell"></div></div>';

        currentItem.parentNode.append( newItem );

        var product     = $products[ ($activeIndex - 1) ],
            strContent  = '';

        if( product.slider )
        {
            strContent = product.sliderHTML;
        }
        else
        {
            strContent = '<div class="ctable"><div class="ctable-cell"><img src="' + product.images[0] + '" alt="' + product.title + '"></div></div>';
        }

        // newItem.querySelector(".ctable-cell").innerHTML = strContent;
        newItem.innerHTML = strContent;

        eval( product.sliderScript );

        setTimeout(function()
        {
            currentItem.classList.add("hidden");
            newItem.classList.remove("hidden");

            setTimeout(function()
            {
                currentItem.parentNode.removeChild( currentItem );
            }, 400);
        }, 500);
    };

})(window, jQuery, IIDO.ProductGallery);