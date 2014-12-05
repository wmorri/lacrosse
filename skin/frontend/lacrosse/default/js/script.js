jQuery(document).ready(function () {

// form input

jQuery('input[type="text"], textarea').each(function(){    
	var default_value = jQuery(this).val();        
	jQuery(this).focus(function() {
		if(jQuery(this).val() == default_value)
		{
			 jQuery(this).val("");
		}
	}).blur(function(){
		if(jQuery(this).val().length == 0) /*Small update*/
		{
			jQuery(this).val(default_value);
		}
	});
});


//tabs
jQuery('#menu3').tabify();
jQuery('#menu2').tabify();
	

//responsine nav

var pull 		= jQuery('#pull');
				menu 		= jQuery('nav ul');
				menuHeight	= menu.height();

			jQuery(pull).on('click', function(e) {
				e.preventDefault();
				menu.slideToggle();
			});

			jQuery(window).resize(function(){
        		var w = jQuery(window).width();
        		if(w > 320 && menu.is(':hidden')) {
        			menu.removeAttr('style');
        		}
});


//slide show
//accordian
jQuery('.cycle-slideshow').cycle();
jQuery('.cycle-slideshow4').cycle();

	jQuery("#firstpane ul li span.menu_head").click(function()
    {
		jQuery(this).toggleClass("active").next("ul.menu_body").slideToggle(300).siblings("ul.menu_body").slideUp("slow");
       	jQuery(this).siblings();
	});

// border radius
jQuery(function() {
    if (window.PIE) {
        jQuery('.green-light-box, .lft-aside').each(function() {
            PIE.attach(this);
        });
    }
});	
  
  });
  
  
  

//Placehoder Script
(function(jQuery) {
  jQuery.extend(jQuery,{ placeholder: {
      browser_supported: function() {
        return this._supported !== undefined ?
          this._supported :
          ( this._supported = !!('placeholder' in jQuery('<input>')[0]) );
      },
      shim: function(opts) {
        var config = {
          color: '#333333',
          cls: 'placeholder',
          selector: 'input[placeholder], textarea[placeholder]'
        };
        jQuery.extend(config,opts);
        return !this.browser_supported() && jQuery(config.selector)._placeholder_shim(config);
      }
  }});

  jQuery.extend(jQuery.fn,{
    _placeholder_shim: function(config) {
      function calcPositionCss(target)
      {
        var op = jQuery(target).offsetParent().offset();
        var ot = jQuery(target).offset();

        return {
          top: ot.top - op.top,
          left: ot.left - op.left,
          width: jQuery(target).width()
        };
      }
      function adjustToResizing(label) {
      	var jQuerytarget = label.data('target');
      	if(typeof $target !== "undefined") {
          label.css(calcPositionCss($target));
          jQuery(window).one("resize", function () { adjustToResizing(label); });
        }
      }
      return this.each(function() {
        var $this = jQuery(this);

        if( $this.is(':visible') ) {

          if( $this.data('placeholder') ) {
            var $ol = $this.data('placeholder');
            $ol.css(calcPositionCss($this));
            return true;
          }

          var possible_line_height = {};
          if( !$this.is('textarea') && $this.css('height') != 'auto') {
            possible_line_height = { lineHeight: $this.css('height'), whiteSpace: 'nowrap' };
          }

          var ol = jQuery('<label />')
            .text($this.attr('placeholder'))
            .addClass(config.cls)
            .css($.extend({
              position:'absolute',
              display: 'inline',
              float:'none',
              overflow:'hidden',
              textAlign: 'left',
              color: config.color,
              cursor: 'text',
              paddingTop: $this.css('padding-top'),
              paddingRight: $this.css('padding-right'),
              paddingBottom: $this.css('padding-bottom'),
              paddingLeft: $this.css('padding-left'),
              fontSize: $this.css('font-size'),
              fontFamily: $this.css('font-family'),
              fontStyle: $this.css('font-style'),
              fontWeight: $this.css('font-weight'),
              textTransform: $this.css('text-transform'),
              backgroundColor: 'transparent',
              zIndex: 99
            }, possible_line_height))
            .css(calcPositionCss(this))
            .attr('for', this.id)
            .data('target',$this)
            .click(function(){
              jQuery(this).data('target').focus();
            })
            .insertBefore(this);
          $this
            .data('placeholder',ol)
            .focus(function(){
              ol.hide();
            }).blur(function() {
              ol[$this.val().length ? 'hide' : 'show']();
            }).triggerHandler('blur');
          jQuery(window).one("resize", function () { adjustToResizing(ol); });
        }
      });
    }
  });
})(jQuery);

jQuery(document).add(window).bind('ready load', function() {
  if (jQuery.placeholder) {
    jQuery.placeholder.shim();
  }
});