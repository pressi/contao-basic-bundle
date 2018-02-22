/**********************************************************/
/*                                                        */
/*  (c) 2018 IIDO                <development@iido.at>    */
/*                                                        */
/*  author: Stephan Preßl        <development@iido.at>    */
/*                                                        */
/**********************************************************/
var IIDO  = IIDO || {};
IIDO.FullPage = IIDO.FullPage || {};

(function (window, $, fullpage) {

    var $viewFuncs = [], $leaveFuncs = [],
        $viewAllFuncs = [], $leaveAllFuncs = [];


    fullpage.addToLoadView = function(index, func)
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



    fullpage.addLoadToAllViews = function(func)
    {
        $viewAllFuncs.push( func );
    };



    fullpage.addToLeaveView = function(index, func)
    {
        if (index in $leaveFuncs)
        {
            $leaveFuncs[ index ].push( func );
        }
        else
        {
            $leaveFuncs[ index ] = [];
            $leaveFuncs[ index ].push( func );
        }
    };



    fullpage.addLeaveToAllViews = function(func)
    {
        $leaveAllFuncs.push( func );
    };



    fullpage.runLoadSection = function(index, anchorLink)
    {
        if( index in $viewFuncs)
        {
            for(var i=0; i < $viewFuncs[ index ].length; i++)
            {
                $viewFuncs[ index ][ i ](anchorLink);
            }
        }
    };



    fullpage.runLoadSectionAll = function(anchorLink)
    {
        for(var i=0; i < $viewAllFuncs.length; i++)
        {
            $viewAllFuncs[ i ](anchorLink);
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



    fullpage.runLeaveSectionAll = function(index, nextIndex, direction)
    {
        for(var i=0; i < $leaveAllFuncs.length; i++)
        {
            $leaveAllFuncs[ i ](index, nextIndex, direction);
        }
    };

})(window, jQuery, IIDO.FullPage); var huhu;