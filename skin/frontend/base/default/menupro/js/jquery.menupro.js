/*
 * Project: Twitter Bootstrap Hover Dropdown
 * Author: Cameron Spear
 * Contributors: Mattia Larentis
 *
 * Dependencies?: Twitter Bootstrap's Dropdown plugin
 *
 * A simple plugin to enable twitter bootstrap dropdowns to active on hover and provide a nice user experience.
 *
 * No license, do what you want. I'd love credit or a shoutout, though.
 *
 * http://cameronspear.com/blog/twitter-bootstrap-dropdown-on-hover-plugin/
 */
jQuery.noConflict();
 (function($, window, undefined) {
    // outside the scope of the jQuery plugin to
    // keep track of all dropdowns
    var $allDropdowns = $();

    // if instantlyCloseOthers is true, then it will instantly
    // shut other nav items when a new one is hovered over
    $.fn.dropdownHover = function(options) {

        // the element we really care about
        // is the dropdown-toggle's parent
        $allDropdowns = $allDropdowns.add(this.parent());

        return this.each(function() {
            var $this = $(this).parent(),
                defaults = {
                    delay: 100,
                    instantlyCloseOthers: true
                },
                data = {
                    delay: $(this).data('delay'),
                    instantlyCloseOthers: $(this).data('close-others')
                },
                options = $.extend(true, {}, defaults, options, data),
                timeout;
			/* Dropdown Level 0 */
			$this.hover(function() {
				if(options.instantlyCloseOthers === true)
				$allDropdowns.removeClass('open');

				window.clearTimeout(timeout);
				$(this).find('> .dropdown-menu').slideDown();
				$(this).find('> .dropline-menu').slideDown();
				$(this).find('.dropdown-menu').find('.dropdown-menu').hide();
				$(this).find('.dropline-menu').find('.dropdown-menu').hide();
				$(this).addClass('open');
				
			}, function() {
				timeout = window.setTimeout(function() {
				$this.find('>.dropdown-menu').hide();
				$this.find('>.dropline-menu').hide();
				$this.removeClass('open');
				}, options.delay);
			});
        });
    };
    $(document).ready(function() {
        $('[data-hover="dropdown"]').dropdownHover();
		$('.nav [class*="s_w"] ul li').hover(function(){
			$(this).addClass('over');
		},function(){
			$(this).removeClass('over');
		});
    });
})(jQuery, this);
/**
*	@name							Accordion
*	@descripton						This Jquery plugin makes creating accordions pain free
*	@version						1.4
*	@requires						Jquery 1.2.6+
*
*	@author							Jan Jarfalk
*	@author-email					jan.jarfalk@unwrongest.com
*	@author-website					http://www.unwrongest.com
*
*	@licens							MIT License - http://www.opensource.org/licenses/mit-license.php
*/
(function($, window, undefined) {
	$.fn.accordion = function() {
		return this.each(function() {
            	
            	var $ul						= $(this),
					elementDataKey			= 'accordiated',
					activeClassName			= 'response_active',
					activationEffect 		= 'slideToggle',
					panelSelector			= 'ul, div',
					activationEffectSpeed 	= 'fast',
					itemSelector			= 'li';
            	
				if($ul.data(elementDataKey)){ 
					return false;
					}
													
				$.each($ul.find('li>div'), function(){
					$(this).data(elementDataKey, true);
					$(this).hide();
				});
				
				$.each($ul.find('span.expand'), function(){
					$(this).click(function(e){
						activate(this, activationEffect);
						return void(0);
					});
					
					$(this).bind('activate-node', function(){
						$ul.find( panelSelector ).not($(this).parents()).not($(this).siblings()).slideUp( activationEffectSpeed );
						activate(this,'slideDown');
					});
				});
				
				var active = (location.hash)?$ul.find('a[href=' + location.hash + ']')[0]:$ul.find('li.current a')[0];

				if(active){
					activate(active, false);
				}
				
				function activate(el,effect){
					$(el).parent( itemSelector ).siblings().removeClass(activeClassName).children( panelSelector ).slideUp( activationEffectSpeed );
					
					$(el).siblings( panelSelector )[(effect || activationEffect)](((effect == "show")?activationEffectSpeed:false),function(){
						
						if($(el).siblings( panelSelector ).is(':visible')){
							//When parent of expand is a div (nav-header)
							if ($(el).parent().attr('class').match('s_w') != null) {
								//console.log($(el).parent().attr('class').match('s_w'));
								$(el).parents( 'div' ).not($ul.parents()).addClass(activeClassName);
							}
							$(el).parents( itemSelector ).not($ul.parents()).addClass(activeClassName);
						} else {
							$(el).parent( itemSelector ).removeClass(activeClassName);
							//When parent of expand is a div (nav-header)
							if ($(el).parent().attr('class').match('s_w') != null) {
								$(el).parent( 'div' ).removeClass(activeClassName);
							}
						}
						
						if(effect == 'show'){
							$(el).parents( itemSelector ).not($ul.parents()).addClass(activeClassName);
						}
					
						$(el).parents().show();
					
					});
					
				}
				
            });
	};
	$(document).ready(function() {
        $('ul.accordion').accordion();
		/* Collapse function */
		var $this = $('.nav-accordion');
		var $target = '.nav-collapse';
		var $opening = 'show_menu';
		var $closing = 'hide_menu';
		$this.find($target).slideUp();
		$this.find('.'+$opening).live('click',function(){
			$(this).parent().find($target).slideDown();//slideDown('slow');
			$(this).removeClass($opening).addClass($closing);
		}); 
		 $this.find('.'+$closing).live('click',function(){
			$(this).parent().find($target).slideUp('slow');
			$(this).addClass($opening).removeClass($closing);
		}); 
		

    });
})(jQuery, this);
