/**********************************************************/
/*                                                        */
/*  (c) 2017 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.FullPage = IIDO.FullPage || {};

(function (window, $, fullpage) {

    var $viewFuncs = [], $leaveFuncs = [];


    fullpage.addToView = function(index, func)
    {
        if (index in $viewFuncs)
        {
            $viewFuncs[ index ].push( func );
        }
        else
        {
            $viewFuncs[ index ] = [];
            $viewFuncs[ index ].push( func );
        }
    };



    fullpage.runLoadSection = function(index, anchorLink)
    {
        if( index in $viewFuncs)
        {
            for(var i=0; i < $viewFuncs[ index ].length; i++)
            {
                $viewFuncs[ index ][ i ]();
            }
        }
    };



    fullpage.runLeaveSection = function(index, nextIndex, direction)
    {
        if( index in $leaveFuncs)
        {
            for(var i=0; i < $leaveFuncs[ index ].length; i++)
            {
                $leaveFuncs[ index ][ i ](nextIndex, direction);
            }
        }
    };

})(window, jQuery, IIDO.FullPage); var huhu;