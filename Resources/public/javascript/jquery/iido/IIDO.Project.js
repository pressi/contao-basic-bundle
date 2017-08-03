/**********************************************************/
/*                                                        */
/*  (c) 2017 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO = IIDO || {};
IIDO.Project = IIDO.Project || {};

(function(window, $, project)
{

    project.open = function( openType, arrImagesOrUrl )
    {
        if( openType === "images" )
        {
            $.fancybox.open( arrImagesOrUrl );
        }
        else if( openType === "details" )
        {
            // Barba.Pjax.goTo( location.origin + '/' + arrImagesOrUrl );
            location.href = location.origin + '/' + arrImagesOrUrl;
        }
    };



    project.toggleDetails = function( projectID, start )
    {
        var textContainer = $('#projectDetails' + projectID);

        if( start === undefined || start === null || start === "undefined" )
        {
            start = false;
        }

        if( textContainer.length )
        {
            if( start )
            {
                textContainer.animate({
                    'height': 'toggle'
                }, 1);
            }
            else
            {
                textContainer.prev().toggleClass("open");

                textContainer.animate({
                    'height': 'toggle'
                }, 500);
            }
        }
    }

})(window, jQuery, IIDO.Project);
var huhu;