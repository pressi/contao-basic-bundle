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
    var $projectImages = {}, $projectData = {},
        $activeImage = 0, $maxImage = 0,
        $nextImage, $prevImage, $imagesContainer, $projectDetailsAsPage = false;

    project.open = function( openType, arrImagesOrUrlOrId )
    {
        $activeImage = 0;

        if( openType === "images" )
        {
            $.fancybox.open( IIDO.Project.getProjectImages(arrImagesOrUrlOrId) );
        }
        else if( openType === "details" )
        {
            // Barba.Pjax.goTo( location.origin + '/' + arrImagesOrUrl );

            if( $projectDetailsAsPage )
            {
                location.href = location.origin + '/' + arrImagesOrUrlOrId;
            }
            else
            {
                var objProjectData          = IIDO.Project.getProjectData( arrImagesOrUrlOrId ),
                    objImages               = IIDO.Project.getProjectImages( arrImagesOrUrlOrId ),

                    projectDetailContainer  = document.createElement("div"),

                    projectTitle            = document.createElement("div"),
                    projectImageCounter     = document.createElement("div"),
                    projectClose            = document.createElement("div"),
                    projectText             = document.createElement("div"),
                    projectImages           = document.createElement("div");

                $maxImage = (objImages.length - 1);

                projectDetailContainer.classList.add("project-detail-container");

                projectTitle.classList.add("title");
                projectTitle.innerHTML = objProjectData.title;

                projectImageCounter.classList.add("image-counter");
                projectImageCounter.innerHTML = '<span id="imageCount">1</span>/' + objImages.length;

                projectImages.classList.add("images");
                projectImages.append( IIDO.Project.renderImageGallery( arrImagesOrUrlOrId ) );

                projectText.classList.add("text");
                projectText.innerHTML = objProjectData.text;

                projectClose.classList.add("close");
                projectClose.innerHTML = 'zurück';

                projectClose.addEventListener("click", function()
                {
                    projectDetailContainer.classList.remove("open");

                    document.documentElement.classList.remove("locked");

                    setTimeout( function() { projectDetailContainer.parentNode.removeChild( projectDetailContainer ); }, 300);
                });

                projectDetailContainer.appendChild( projectTitle );
                projectDetailContainer.appendChild( projectImageCounter );
                projectDetailContainer.appendChild( projectImages );
                projectDetailContainer.appendChild( projectText );
                projectDetailContainer.appendChild( projectClose );

                document.body.appendChild( projectDetailContainer );

                document.documentElement.classList.add("locked");

                setTimeout( function() { projectDetailContainer.classList.add("open"); }, 200);
            }
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
    };



    project.setProjectImages = function( projectID, arrImages )
    {
        $projectImages[ projectID ] = arrImages;
    };



    project.setProjectData = function( projectID, objData )
    {
        $projectData[ projectID ] = objData;
    };



    project.getProjectImages = function( projectID )
    {
        return $projectImages[ projectID ] || {};
    };



    project.getProjectData = function( projectID )
    {
        return $projectData[ projectID ] || {};
    };



    project.renderImageGallery = function( projectID )
    {
        var objImages       = IIDO.Project.getProjectImages( projectID ),
            imageCont       = document.createElement("div"),
            imageTagCont    = document.createElement("div"),

            imageContWrapper    = document.createElement("div"),

            prevImage       = document.createElement("div"),
            nextImage       = document.createElement("div");

        imageContWrapper.classList.add("images-container-wrapper");
        imageCont.classList.add("images-container");

        imageTagCont.classList.add("image-tag");
        imageTagCont.classList.add("bg-image");
        imageTagCont.classList.add("bg-cover");

        $imagesContainer = imageCont;

        for(var i=0; i<objImages.length; i++)
        {
            var objImage    = objImages[ i ],
                imageTag    = imageTagCont.cloneNode(true);

            imageTag.style.backgroundImage = 'url(' + objImage.src + ')';

            imageCont.append( imageTag );
        }

        prevImage.classList.add("prev-image");
        prevImage.classList.add("disabled");

        nextImage.classList.add("next-image");

        if( objImages.length === 1 )
        {
            nextImage.classList.add("disabled");
        }

        prevImage.addEventListener("click", function()
        {
            IIDO.Project.prevImage();
        });

        nextImage.addEventListener("click", function()
        {
            IIDO.Project.nextImage();
        });

        $prevImage = prevImage;
        $nextImage = nextImage;

        imageContWrapper.append( imageCont );
        imageContWrapper.append( prevImage );
        imageContWrapper.append( nextImage );

        return imageContWrapper;
    };



    project.prevImage = function()
    {
        $activeImage--;

        $nextImage.classList.remove("disabled");

        if( $activeImage <= 0 )
        {
            $activeImage = 0;

            $prevImage.classList.add("disabled");
        }

        document.getElementById("imageCount").innerHTML = ($activeImage + 1);

        $imagesContainer.style.transform = 'translateY(-' + (100 * $activeImage) + '%)';
    };



    project.nextImage = function()
    {
        $activeImage++;

        $prevImage.classList.remove("disabled");

        if( $activeImage >= $maxImage )
        {
            $activeImage = $maxImage;

            $nextImage.classList.add("disabled");
        }

        document.getElementById("imageCount").innerHTML = ($activeImage + 1);

        $imagesContainer.style.transform = 'translateY(-' + (100 * $activeImage) + '%)';
    };

})(window, jQuery, IIDO.Project);
var huhu;