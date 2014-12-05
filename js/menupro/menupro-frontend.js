var mst = jQuery.noConflict();
mst(document).ready(function($) {
	Menupro = {
		addFirstLastClass : function() {
			$("li:first-child").addClass("first");
			$("li:last-child").addClass("last");
		},
		addActiveClass : function(selector) {
			/* Add active class to parent when a child active */
			var current_url = window.location.href;
			var link = null;
			var li_class = null;
			$(selector + ' li a').each(function() {
				link = $(this).attr('href');
				if(link == current_url && (link!='http://devrack.lacrosseunlimited.com/')){
					$(this).addClass('active');
					$(this).parents('li').addClass('active');
				}
			});
			//Just want active class visiable in li level0, and remove in all another level
			$(selector + ' li').each(function() {
				try{
					li_class = $(this).attr('class');
					if (li_class != "" && li_class != undefined) {
						if(li_class.indexOf('level0') == -1) {
							$(this).removeClass('active');
						}
					}
					
				}catch(error){
					//Do nothing in here
				}
			});
		},
		addDepthToDropline : function () {
			$('div.dropline-menu div.dropdown-menu ul').each(function() {
				var depth = $(this).parents('ul').length;
				$(this).addClass('dropline-level-' + depth);
			});
		},
		changeClassName : function (selector) {
			var className = "";
			var parentClass;
			$(selector).each(function() {
				className = $(this).attr('class');
				parent = $(this).parent('div').attr('class');
				if(typeof className != "undefined" && className != ""){
					if (className.match('dropdown-menu') ) {
						//If parent of this ul is div (class="sub-one", sw-1)
						if (typeof parent != "undefined" && parent != "") {
							$(this).removeClass('dropdown-menu');
						}
					}
				}
			});
		}
	}
	Menupro.addFirstLastClass();
	Menupro.addActiveClass('.navbar .navbar-inner .nav');//Active dropdown and dropline menu
	Menupro.addActiveClass('.navsidebar .navbar-inner .nav');//Active sidebar
	Menupro.addActiveClass('.nav-accordion .navbar-inner .nav');//Active accordion
	//Menupro.addDepthToDropline();
	//Menupro.changeClassName('.dropline-level-2');
});