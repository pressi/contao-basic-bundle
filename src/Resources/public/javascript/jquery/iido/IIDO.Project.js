/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO = IIDO || {};
IIDO.Project = IIDO.Project || {};

(function(window, $, project)
{
    var $projectImages = {}, $projectData = {}, $projectSlider = {},
        $activeImage = 0, $maxImage = 0, $activePage = 0,
        $nextImage, $prevImage, $imagesContainer, $projectDetailsAsPage = false;


    project.init = function()
    {
        // if( window.innerWidth <= 800 )
        // {
        //     $projectDetailsAsPage = true;
        // }
    };


    project.open = function( openType, arrImagesOrUrlOrId )
    {
        if( document.getElementById("projectItem_" + arrImagesOrUrlOrId).classList.contains("full-height") )
        {
            return;
        }

        // if( window.innerWidth <= 800 )
        // {
        //     $projectDetailsAsPage = true;
        // }

        $activeImage = 0;

        if( openType === "images" )
        {
            var options = {
                buttons : ['close'],

                infobar : true,
                arrows : true,

                btnTpl: {
                    arrowLeft   : '<div class="arrow arrow-style1 arrow-left big fancybox-button--arrow_left" data-fancybox-prev>' +
                    '<div class="arrow-inside-container"></div></div>',
                    arrowRight  : '<div class="arrow arrow-style1 arrow-right big fancybox-button--arrow_right" data-fancybox-next>' +
                    '<div class="arrow-inside-container"></div></div>'
                },

                afterLoad : function()
                {
                    var headerLogo = $("header .logo").clone(),
                        clonedLogo = $(".cloned-logo");

                    if( !clonedLogo.length )
                    {
                        headerLogo.addClass("cloned-logo");

                        $( document.body ).append( headerLogo );
                    }
                },

                afterClose : function()
                {
                    var clonedLogo = $(".cloned-logo");

                    if( clonedLogo.length )
                    {
                        clonedLogo.remove();
                    }
                }
            },
            arrImages = IIDO.Project.getProjectImages(arrImagesOrUrlOrId);

            $.fancybox.open( arrImages, options );
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


                var imageGallery    = IIDO.Project.renderImageGallery( arrImagesOrUrlOrId ),
                    textAdded       = false;

                if( imageGallery )
                {
                    projectImageCounter.classList.add("image-counter");
                    projectImageCounter.innerHTML = '<span id="imageCount">1</span>/' + objImages.length;

                    projectImages.classList.add("images");
                    projectImages.append( imageGallery );
                }
                else
                {
                    var projectImagesInside     = document.createElement("div"),
                        projectImagesWrapper    = document.createElement("div");

                    projectImages.classList.add("image-gallery-container");

                    projectImagesInside.classList.add("image-gallery-inside");

                    projectImagesWrapper.classList.add("image-gallery-wrapper");
                    projectImagesWrapper.classList.add("hidden");

                    projectImagesInside.append( projectImagesWrapper );
                    projectImages.append( projectImagesInside );

                    if( document.getElementById("projectItem_" + arrImagesOrUrlOrId).classList.contains("has-slider") )
                    {
                        IIDO.Project.renderSlider( arrImagesOrUrlOrId, projectImagesWrapper );
                    }
                    else
                    {
                        if( document.getElementById("projectItem_" + arrImagesOrUrlOrId).classList.contains("box-w2") )
                        {
                            projectImagesWrapper.innerHTML = objProjectData.text;

                            projectImagesWrapper.classList.remove("hidden");
                            projectImages.classList.add("only-text");

                            textAdded = true;
                        }
                    }

                    document.getElementById("projectItem_" + arrImagesOrUrlOrId).parentNode.parentNode.scrollTop = 0;
                }

                projectText.classList.add("text");


                var projectTextInside   = document.createElement("div"),
                    projectTextWrapper  = document.createElement("div");

                projectTextInside.classList.add("text-inside");
                projectTextWrapper.classList.add("text-wrapper");

                if( !textAdded )
                {
                    projectTextWrapper.innerHTML = objProjectData.text;
                }

                projectTextInside.append( projectTextWrapper );
                projectText.append( projectTextInside );

                projectClose.classList.add("close");
                projectClose.innerHTML = 'zurück';

                projectClose.addEventListener("click", function()
                {
                    projectDetailContainer.classList.remove("open");

                    document.documentElement.classList.remove("locked");
                    document.body.classList.remove("details-open");

                    setTimeout( function() { projectDetailContainer.parentNode.removeChild( projectDetailContainer ); }, 300);
                });

                projectDetailContainer.appendChild( projectTitle );

                projectDetailContainer.appendChild( projectText );
                projectDetailContainer.appendChild( projectClose );

                if( imageGallery )
                {
                    projectDetailContainer.appendChild( projectImageCounter );
                    projectDetailContainer.appendChild( projectImages );
                }
                else
                {
                    projectDetailContainer.appendChild( projectImages );
                }

                // document.body.appendChild( projectDetailContainer );
                var cont = document.getElementById("projectItem_" + arrImagesOrUrlOrId);

                if( cont )
                {
                    cont.parentNode.appendChild( projectDetailContainer );
                }

                document.documentElement.classList.add("locked");
                document.body.classList.add("details-open");

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
        var objImages       = IIDO.Project.getProjectImages( projectID );

        if( objImages.length === 0 || objImages.length === undefined || objImages.length === "undefined" || objImages.length === null)
        {
            return false;
        }
        
        var imageCont       = document.createElement("div"),
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



    project.setProjectSlider = function(sliderID, sliderHTML, sliderScript)
    {
        $projectSlider[ sliderID ] = {
            'html'  : sliderHTML,
            'script' : sliderScript
        };
    };



    project.renderSlider = function( sliderID, sliderContainer )
    {
        sliderContainer.innerHTML = $projectSlider[ sliderID ].html;

        setTimeout(function()
        {
            eval( $projectSlider[ sliderID ].script );
            sliderContainer.classList.remove("hidden");
        }, 500);
    };



    project.initPagination = function( perPage, articleContID )
    {
        var articleCont     = document.getElementById( articleContID ),
            cont            = articleCont.querySelector(".project-inside-container");

        if( cont )
        {
            var items = cont.querySelectorAll(".project-item");

            if( items.length )
            {
                var pageParentCont  = cont.parentNode,
                    pageCont        = pageParentCont.querySelector(".project-pagination-container");

                if( items.length > perPage && !pageCont )
                {
                    var contPagination = document.createElement("div"),

                        pageNext        = document.createElement("div"),
                        pagePrev        = document.createElement("div");

                    contPagination.classList.add("project-pagination-container");

                    pageNext.classList.add("project-next-page");
                    pagePrev.classList.add("project-prev-page");

                    contPagination.appendChild(pagePrev);
                    contPagination.appendChild(pageNext);

                    pagePrev.addEventListener("click", function() { IIDO.Project.toPrevPage(); });
                    pageNext.addEventListener("click", function() { IIDO.Project.toNextPage(); });

                    pageParentCont.parentNode.insertBefore(contPagination, pageParentCont);


                    var projectItem = cont.querySelector(".project-item"),
                        piWidth     = projectItem.offsetWidth,
                        piHeight    = projectItem.offsetHeight,

                        contWidth   = (piWidth * 6),
                        contHeight  = (piHeight * 3),

                        pages       = Math.ceil( items.length % perPage );

                    if( projectItem.classList.contains("box-w2") )
                    {
                        contWidth = (piWidth * 3);
                    }

                    pageParentCont.style.height = contHeight + 'px';

                    cont.classList.add("initialized");
                    cont.style.width = (contWidth * pages) + 'px';

                    var posTop = (contHeight / 2);

                    pagePrev.style.top = posTop + 'px';
                    pageNext.style.top = posTop + 'px';
                }
            }
        }
    };



    project.toNextPage = function()
    {
        // console.log( this );
        // console.log( this.targetNode );
        // console.log( this.targetElm );
    };



    project.toPrevPage = function()
    {
        // console.log( this );
        // console.log( this.targetNode );
        // console.log( this.targetElm );
    }

})(window, jQuery, IIDO.Project);
var huhu;