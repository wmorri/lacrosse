(function(jQuery) {
    var webikaSlider = function(element, options)
    { 
        // Defaults are below
        var settings = jQuery.extend({}, jQuery.fn.WebikaSlider.defaults, options);

        // Useful variables. Play carefully.
        var vars = {
            currentSlide: 0,
            currentImage: '',
            totalSlides: 0,
            running: false,
            paused: false,
            stop: false,
            controlNavEl: false
        };

        // Get this slider
        var slider = jQuery(element);
        slider.data('webikaslider:vars', vars).addClass('webikaSlider');

        // Find our slider children
        var kids = slider.children();
        kids.each(function() {
            var child = jQuery(this);
            var link = '';
            if(!child.is('img')){
                if(child.is('a')){
                    child.addClass('webikaslider-imageLink');
                    link = child;
                }
                child = child.find('img:first');
            }
            // Get img width & height
            var childWidth = (childWidth === 0) ? child.attr('width') : child.width(),
                childHeight = (childHeight === 0) ? child.attr('height') : child.height();

            if(link !== ''){
                link.css('display','none');
            }
            child.css('display','none');
            vars.totalSlides++;
        });
         
        // If randomStart
        if(settings.randomStart){
            settings.startSlide = Math.floor(Math.random() * vars.totalSlides);
        }
        
        // Set startSlide
        if(settings.startSlide > 0){
            if(settings.startSlide >= vars.totalSlides) { settings.startSlide = vars.totalSlides - 1; }
            vars.currentSlide = settings.startSlide;
        }
        
        // Get initial image
        if(jQuery(kids[vars.currentSlide]).is('img')){
            vars.currentImage = jQuery(kids[vars.currentSlide]);
        } else {
            vars.currentImage = jQuery(kids[vars.currentSlide]).find('img:first');
        }
        
        // Show initial link
        if(jQuery(kids[vars.currentSlide]).is('a')){
            jQuery(kids[vars.currentSlide]).css('display','block');
        }
        
        // Set first background
        var sliderImg = jQuery('<img class="webikaslider-main-image" src="#" />');
        sliderImg.attr('src', vars.currentImage.attr('src')).show();
        slider.append(sliderImg);

        
        
        // Detect Window Resize
        var winWidth = jQuery(window).width(),
        winHeight = jQuery(window).height();
        
        jQuery(window).resize(function() {
            onResize  = function(){
                slider.children('img').width(slider.width());
                sliderImg.attr('src', vars.currentImage.attr('src'));
                sliderImg.stop().height('auto');
                jQuery('.webikaslider-slice').remove();
                jQuery('.webikaslider-box').remove();
            };
            
        
            var winNewWidth = jQuery(window).width(),
            winNewHeight = jQuery(window).height();
            
            if(winWidth!=winNewWidth || winHeight!=winNewHeight)
            {
                window.clearTimeout(resizeTimeout);
                resizeTimeout = window.setTimeout(onResize, 10);
            }
            winWidth = winNewWidth;
            winHeight = winNewHeight;
        
        });

        

        //Create caption
        slider.append(jQuery('<div class="webikaslider-caption"></div>'));
        
        // Process caption function
        var processCaption = function(settings){
            var webikasliderCaption = jQuery('.webikaslider-caption', slider);
            if(settings.showTitle && vars.currentImage.attr('title') != '' && vars.currentImage.attr('title') != undefined){
                var title = vars.currentImage.attr('title');
                if(title.substr(0,1) == '#') title = jQuery(title).html();   

                if(webikasliderCaption.css('display') == 'block'){
                    setTimeout(function(){
                        webikasliderCaption.html(title);
                    }, settings.animSpeed);
                } else {
                    webikasliderCaption.html(title);
                    webikasliderCaption.stop().fadeIn(settings.animSpeed);
                }
            } else {
                webikasliderCaption.stop().fadeOut(settings.animSpeed);
            }
        }
        
        //Process initial  caption
        processCaption(settings);
        
        // In the words of Super Mario "let's a go!"
        var timer = 0;
        if(!settings.manualAdvance && kids.length > 1){
            timer = setInterval(function(){ webikasliderRun(slider, kids, settings, false); }, settings.pauseTime);
        }
        
        // Add Direction nav
        if(settings.directionNav){
            if(settings.directionNavMode == 'text')
            {
                slider.append('<div class="webikaslider-directionNav"><a class="webikaslider-prevNav">'+ settings.prevText +'</a><a class="webikaslider-nextNav">'+ settings.nextText +'</a></div>');
            }
            else if(settings.leftArrowImage && settings.rightArrowImage && settings.directionNavMode == 'images')
            {
                var directionNav = jQuery('<div class="webikaslider-directionNav" />');
                var prevArrow = jQuery('<a class="webikaslider-prevNav webikaslider-arrows" />');
                var nextArrow = jQuery('<a class="webikaslider-nextNav webikaslider-arrows" />');
                
                prevArrow.text(settings.prevText).css({background: 'url('+settings.leftArrowImage+')'});
                nextArrow.text(settings.nextText).css({background: 'url('+settings.rightArrowImage+')'});
                
                // Prepare Content
                directionNav.append(prevArrow);
                directionNav.append(nextArrow);
                slider      .append(directionNav);
                
                // Set Image Size
                var i = new Image();
                    i.src = settings.leftArrowImage;
                    i.onload = function(){
                        prevArrow.css({width: this.width+'px', height: this.height+'px'});
                    }
                
                var i = new Image();
                    i.src = settings.rightArrowImage;
                    i.onload = function(){
                        nextArrow.css({width: this.width+'px', height: this.height+'px'});
                    }
            }
            
            jQuery('a.webikaslider-prevNav', slider).live('click', function(){
                if(vars.running) { return false; }
                clearInterval(timer);
                timer = '';
                vars.currentSlide -= 2;
                webikasliderRun(slider, kids, settings, 'prev');
            });
            
            jQuery('a.webikaslider-nextNav', slider).live('click', function(){
                if(vars.running) { return false; }
                clearInterval(timer);
                timer = '';
                webikasliderRun(slider, kids, settings, 'next');
            });
        }
        
        // Add Control nav
        if(settings.controlNav){
            vars.controlNavEl = jQuery('<div class="webikaslider-controlNav"></div>').css('text-align' , settings.nav_position);
            slider.after(vars.controlNavEl);
            for(var i = 0; i < kids.length; i++)
            {
                
                if(settings.controlNavMode == 'thumbnails'){
                    vars.controlNavEl.addClass('webikaslider-thumbs-enabled');
                    var child = kids.eq(i);
                    if(!child.is('img')){
                        child = child.find('img:first');
                    }
                    if(child.attr('data-thumb')) vars.controlNavEl.append('<a class="webikaslider-control webikaslider-thumbs" rel="'+ i +'"><img src="'+ child.attr('data-thumb') +'" alt="" /></a>');
                } 
                else if (settings.controlNavMode == 'bullets')
                {
                    vars.controlNavEl.append('<a class="webikaslider-control webikaslider-bullet" rel="'+ i +'"></a>');
                }
                else if (settings.controlNavMode == 'numbers')
                {
                      vars.controlNavEl.append('<a class="webikaslider-control" rel="'+ i +'">'+ (i + 1) +'</a>');
                }
                if(settings.controlNavMode == 'bullets' && settings.bulletImage)
                {
                    var img = new Image();
                    img.src = settings.bulletImage;
                    img.onload = function(){
                        jQuery('.webikaslider-bullet').css({ width: this.width+'px', height: (this.height/2)+'px', backgroundImage: 'url(' + settings.bulletImage + ')' , backgroundRepeat: 'no-repeat' });
                    }
                }
            }

            //Set initial active link
            jQuery('a:eq('+ vars.currentSlide +')', vars.controlNavEl).addClass('active');
            
            jQuery('a', vars.controlNavEl).bind('click', function(){
                if(vars.running) return false;
                if(jQuery(this).hasClass('active')) return false;
                clearInterval(timer);
                timer = '';
                sliderImg.attr('src', vars.currentImage.attr('src'));
                vars.currentSlide = jQuery(this).attr('rel') - 1;
                webikasliderRun(slider, kids, settings, 'control');
            });
        }
        
        //For pauseOnHover setting
        if(settings.pauseOnHover){
            slider.hover(function(){
                vars.paused = true;
                clearInterval(timer);
                timer = '';
            }, function(){
                vars.paused = false;
                // Restart the timer
                if(timer === '' && !settings.manualAdvance){
                    timer = setInterval(function(){ webikasliderRun(slider, kids, settings, false); }, settings.pauseTime);
                }
            });
        }
        
        // Event when Animation finishes
        slider.bind('webikaslider:animFinished', function(){
            sliderImg.attr('src', vars.currentImage.attr('src'));
            vars.running = false; 
            // Hide child links
            jQuery(kids).each(function(){
                if(jQuery(this).is('a')){
                   jQuery(this).css('display','none');
                }
            });
            // Show current link
            if(jQuery(kids[vars.currentSlide]).is('a')){
                jQuery(kids[vars.currentSlide]).css('display','block');
            }
            // Restart the timer
            if(timer === '' && !vars.paused && !settings.manualAdvance){
                timer = setInterval(function(){ webikasliderRun(slider, kids, settings, false); }, settings.pauseTime);
            }
            // Trigger the afterChange callback
            settings.afterChange.call(this);
        }); 
        
        
        
        // Add slices for slice animations
        var createSlices = function(slider, settings, vars) {
        	if(jQuery(vars.currentImage).parent().is('a')) jQuery(vars.currentImage).parent().css('display','block');
            jQuery('img[src="'+ vars.currentImage.attr('src') +'"]', slider).not('.webikaslider-main-image,.webikaslider-control img').width(slider.width()).css('visibility', 'hidden').show();
            var sliceHeight = (jQuery('img[src="'+ vars.currentImage.attr('src') +'"]', slider).not('.webikaslider-main-image,.webikaslider-control img').parent().is('a')) ? jQuery('img[src="'+ vars.currentImage.attr('src') +'"]', slider).not('.webikaslider-main-image,.webikaslider-control img').parent().height() : jQuery('img[src="'+ vars.currentImage.attr('src') +'"]', slider).not('.webikaslider-main-image,.webikaslider-control img').height();

            for(var i = 0; i < settings.slices; i++){
                var sliceWidth = Math.round(slider.width()/settings.slices);
                
                if(i === settings.slices-1){
                    slider.append(
                        jQuery('<div class="webikaslider-slice" name="'+i+'"><img src="'+ vars.currentImage.attr('src') +'" style="position:absolute; width:'+ slider.width() +'px; height:auto; display:block !important; top:0; left:-'+ ((sliceWidth + (i * sliceWidth)) - sliceWidth) +'px;" /></div>').css({ 
                            left:(sliceWidth*i)+'px', 
                            width:(slider.width()-(sliceWidth*i))+'px',
                            height:sliceHeight+'px', 
                            opacity:'0',
                            overflow:'hidden'
                        })
                    );
                } else {
                    slider.append(
                        jQuery('<div class="webikaslider-slice" name="'+i+'"><img src="'+ vars.currentImage.attr('src') +'" style="position:absolute; width:'+ slider.width() +'px; height:auto; display:block !important; top:0; left:-'+ ((sliceWidth + (i * sliceWidth)) - sliceWidth) +'px;" /></div>').css({ 
                            left:(sliceWidth*i)+'px', 
                            width:sliceWidth+'px',
                            height:sliceHeight+'px',
                            opacity:'0',
                            overflow:'hidden'
                        })
                    );
                }
            }
            
            jQuery('.webikaslider-slice', slider).height(sliceHeight);
            sliderImg.stop().animate({
                height: jQuery(vars.currentImage).height()
            }, settings.animSpeed);
        };
        
        // Add boxes for box animations
        var createBoxes = function(slider, settings, vars){
        	if(jQuery(vars.currentImage).parent().is('a')) jQuery(vars.currentImage).parent().css('display','block');
            jQuery('img[src="'+ vars.currentImage.attr('src') +'"]', slider).not('.webikaslider-main-image,.webikaslider-control img').width(slider.width()).css('visibility', 'hidden').show();
            var boxWidth = Math.round(slider.width()/settings.boxCols),
                boxHeight = Math.round(jQuery('img[src="'+ vars.currentImage.attr('src') +'"]', slider).not('.webikaslider-main-image,.webikaslider-control img').height() / settings.boxRows);
            
                        
            for(var rows = 0; rows < settings.boxRows; rows++){
                for(var cols = 0; cols < settings.boxCols; cols++){
                    if(cols === settings.boxCols-1){
                        slider.append(
                            jQuery('<div class="webikaslider-box" name="'+ cols +'" rel="'+ rows +'"><img src="'+ vars.currentImage.attr('src') +'" style="position:absolute; width:'+ slider.width() +'px; height:auto; display:block; top:-'+ (boxHeight*rows) +'px; left:-'+ (boxWidth*cols) +'px;" /></div>').css({ 
                                opacity:0,
                                left:(boxWidth*cols)+'px', 
                                top:(boxHeight*rows)+'px',
                                width:(slider.width()-(boxWidth*cols))+'px'
                                
                            })
                        );
                        jQuery('.webikaslider-box[name="'+ cols +'"]', slider).height(jQuery('.webikaslider-box[name="'+ cols +'"] img', slider).height()+'px');
                    } else {
                        slider.append(
                            jQuery('<div class="webikaslider-box" name="'+ cols +'" rel="'+ rows +'"><img src="'+ vars.currentImage.attr('src') +'" style="position:absolute; width:'+ slider.width() +'px; height:auto; display:block; top:-'+ (boxHeight*rows) +'px; left:-'+ (boxWidth*cols) +'px;" /></div>').css({ 
                                opacity:0,
                                left:(boxWidth*cols)+'px', 
                                top:(boxHeight*rows)+'px',
                                width:boxWidth+'px'
                            })
                        );
                        jQuery('.webikaslider-box[name="'+ cols +'"]', slider).height(jQuery('.webikaslider-box[name="'+ cols +'"] img', slider).height()+'px');
                    }
                }
            }
            
            sliderImg.stop().animate({
                height: jQuery(vars.currentImage).height()
            }, settings.animSpeed);
        };
        
        /**************************************************************/
        
        
        // Private run method
        var webikasliderRun = function(slider, kids, settings, nudge){          
            // Get our vars
            var vars = slider.data('webikaslider:vars');
            
            // Trigger the lastSlide callback
            if(vars && (vars.currentSlide === vars.totalSlides - 1)){ 
                settings.lastSlide.call(this);
            }
            
            // Stop
            if((!vars || vars.stop) && !nudge) { return false; }
            
            // Trigger the beforeChange callback
            settings.beforeChange.call(this);

            // Set current background before change
            if(!nudge){
                sliderImg.attr('src', vars.currentImage.attr('src'));
            } else {
                if(nudge === 'prev'){
                    sliderImg.attr('src', vars.currentImage.attr('src'));
                }
                if(nudge === 'next'){
                    sliderImg.attr('src', vars.currentImage.attr('src'));
                }
            }
            
            vars.currentSlide++;
            // Trigger the slideshowEnd callback
            if(vars.currentSlide === vars.totalSlides){ 
                vars.currentSlide = 0;
                settings.slideshowEnd.call(this);
            }
            if(vars.currentSlide < 0) { vars.currentSlide = (vars.totalSlides - 1); }
            // Set vars.currentImage
            if(jQuery(kids[vars.currentSlide]).is('img')){
                vars.currentImage = jQuery(kids[vars.currentSlide]);
            } else {
                vars.currentImage = jQuery(kids[vars.currentSlide]).find('img:first');
            }
            
            // Set active links
            if(settings.controlNav){
                jQuery('a', vars.controlNavEl).removeClass('active');
                jQuery('a:eq('+ vars.currentSlide +')', vars.controlNavEl).addClass('active');
            }
            
            // Process caption
            processCaption(settings);            
            
            // Remove any slices from last transition
            jQuery('.webikaslider-slice', slider).remove();
            
            // Remove any boxes from last transition
            jQuery('.webikaslider-box', slider).remove();
            
            var currentEffect = settings.effect,
                anims = '';
                
            // Generate random effect
            if(settings.effect === 'random'){
                anims = new Array('sliceDownRight','sliceDownLeft','sliceUpRight','sliceUpLeft','sliceUpDown','sliceUpDownLeft','fold','fade',
                'boxRandom','boxRain','boxRainReverse','boxRainGrow','boxRainGrowReverse');
                currentEffect = anims[Math.floor(Math.random()*(anims.length + 1))];
                if(currentEffect === undefined) { currentEffect = 'fade'; }
            }
            
            // Run random effect from specified set (eg: effect:'fold,fade')
            if(settings.effect.indexOf(',') !== -1){
                anims = settings.effect.split(',');
                currentEffect = anims[Math.floor(Math.random()*(anims.length))];
                if(currentEffect === undefined) { currentEffect = 'fade'; }
            }
            
            // Custom transition as defined by "data-transition" attribute
            if(vars.currentImage.attr('data-transition')){
                currentEffect = vars.currentImage.attr('data-transition');
            }
        
            // Run effects
            vars.running = true;
            var timeBuff = 0,
                i = 0,
                slices = '',
                firstSlice = '',
                totalBoxes = '',
                boxes = '';
            
            
            
            
            
            if(currentEffect === 'zipper')
            {  
                createSlices(slider, settings, vars);
                timeBuff = 0;
                var i = 0;
                slices = jQuery('.webikaslider-slice', slider);
                
                slices.each(function(){
                    var slice = jQuery(this);
                    var height = slice.height()
                    if(i === settings.slices-1)
                    {
                        if(i%2 === 1)
                        {   
                            slice.css({top:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height , opacity: 1 }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                            }, 100 + timeBuff);
                        }
                        else
                        {
                            slice.css({bottom:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height , opacity: 1 }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                            }, 100 + timeBuff);
                        } 
                    }   
                    else 
                    {
                        if(i%2 === 1)
                        {   
                            slice.css({top:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height, opacity: 1 }, settings.animSpeed); 
                            }, 100 + timeBuff);
                        }
                        else
                        {
                            slice.css({bottom:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height , opacity: 1 }, settings.animSpeed); 
                            }, 100 + timeBuff);
                        } 
                    }
                    timeBuff += 50;
                    i++;
                });
            } 
            else if(currentEffect === 'hatch')
            {  
                createSlices(slider, settings, vars);
                timeBuff = 0;
                var i = 0;
                slices = jQuery('.webikaslider-slice', slider);
                
                slices.each(function(){
                    var slice = jQuery(this);
                    var height = slice.height()
                    if(i === settings.slices-1)
                    {
                        if(i%2 === 1)
                        {   
                            slice.css({top:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height , opacity: 1 }, random(500, 2000), '', function(){ slider.trigger('webikaslider:animFinished'); });
                            }, 100 + timeBuff);
                        }
                        else
                        {
                            slice.css({bottom:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height , opacity: 1 }, random(500, 2000), '', function(){ slider.trigger('webikaslider:animFinished'); });
                            }, 100 + timeBuff);
                        } 
                    }   
                    else 
                    {
                        if(i%2 === 1)
                        {   
                            slice.css({top:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height, opacity: 1 }, random(500, 2000)); 
                            }, 100 + timeBuff);
                        }
                        else
                        {
                            slice.css({bottom:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height , opacity: 1 }, random(500, 2000)); 
                            }, 100 + timeBuff);
                        } 
                    }
                    timeBuff += 50;
                    i++;
                });
            } 
            else if(currentEffect === 'wave')
            {   
                createSlices(slider, settings, vars);
                timeBuff = 0;
                var i = 0;
                slices = jQuery('.webikaslider-slice', slider);
                
                slices.each(function(){
                    var slice = jQuery(this);
                    var height = slice.height()
                    if(i === settings.slices-1)
                    {
                        slice.css({top:0, height: 0, opacity: 0});
                        setTimeout(function()
                        {
                            slice.animate({ height: height , opacity: 1 }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                        }, 100 + timeBuff);
                        
                    }   
                    else 
                    {
                            slice.css({top:0, height: 0, opacity: 0});
                            setTimeout(function()
                            {
                                slice.animate({ height: height, opacity: 1 }, settings.animSpeed); 
                            }, 100 + timeBuff);
                       
                    }
                    timeBuff += 50;
                    i++;
                });
            } 
            else if(currentEffect === 'sliceDown' || currentEffect === 'sliceDownRight' || currentEffect === 'sliceDownLeft'){
                createSlices(slider, settings, vars);
                timeBuff = 0;
                i = 0;
                slices = jQuery('.webikaslider-slice', slider);
                if(currentEffect === 'sliceDownLeft') 
                { 
                    slices = jQuery('.webikaslider-slice', slider);
                    arr = jQuery.makeArray(slices);
                    slices = arr.reverse();
                }
                
                jQuery(slices).each(function(){
                    var slice = jQuery(this);
                    slice.css({ 'top': '0px' });
                    if(i === settings.slices-1){
                        setTimeout(function(){
                            slice.animate({opacity:'1.0' }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                        }, (100 + timeBuff));
                    } else {
                        setTimeout(function(){
                            slice.animate({opacity:'1.0' }, settings.animSpeed);
                        }, (100 + timeBuff));
                    }
                    timeBuff += 50;
                    i++;
                });
            } else if(currentEffect === 'sliceUp' || currentEffect === 'sliceUpRight' || currentEffect === 'sliceUpLeft'){
                createSlices(slider, settings, vars);
                timeBuff = 0;
                i = 0;
                slices = jQuery('.webikaslider-slice', slider);
                if(currentEffect === 'sliceUpLeft') 
                { 
                    slices = jQuery('.webikaslider-slice', slider);
                    arr = jQuery.makeArray(slices);
                    slices = arr.reverse();
                }
                
                jQuery(slices).each(function(){
                    var slice = jQuery(this);
                    slice.css({ 'bottom': '0px' });
                    if(i === settings.slices-1){
                        setTimeout(function(){
                            slice.animate({opacity:'1.0' }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                        }, (100 + timeBuff));
                    } else {
                        setTimeout(function(){
                            slice.animate({opacity:'1.0' }, settings.animSpeed);
                        }, (100 + timeBuff));
                    }
                    timeBuff += 50;
                    i++;
                });
            } else if(currentEffect === 'sliceUpDown' || currentEffect === 'sliceUpDownRight' || currentEffect === 'sliceUpDownLeft'){
                createSlices(slider, settings, vars);
                timeBuff = 0;
                i = 0;
                var v = 0;
                slices = jQuery('.webikaslider-slice', slider);
                if(currentEffect === 'sliceUpDownLeft') 
                { 
                    slices = jQuery('.webikaslider-slice', slider);
                    arr = jQuery.makeArray(slices);
                    slices = arr.reverse();
                }
                
                jQuery(slices).each(function(){
                    var slice = jQuery(this);
                    if(i === 0){
                        slice.css('top','0px');
                        i++;
                    } else {
                        slice.css('bottom','0px');
                        i = 0;
                    }
                    
                    if(v === settings.slices-1){
                        setTimeout(function(){
                            slice.animate({opacity:'1.0' }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                        }, (100 + timeBuff));
                    } else {
                        setTimeout(function(){
                            slice.animate({opacity:'1.0' }, settings.animSpeed);
                        }, (100 + timeBuff));
                    }
                    timeBuff += 50;
                    v++;
                });
            } else if(currentEffect === 'fold'){
                createSlices(slider, settings, vars);
                timeBuff = 0;
                i = 0;
                
                jQuery('.webikaslider-slice', slider).each(function(){
                    var slice = jQuery(this);
                    var origWidth = slice.width();
                    slice.css({ top:'0px', width:'0px' });
                    if(i === settings.slices-1){
                        setTimeout(function(){
                            slice.animate({ width:origWidth, opacity:'1.0' }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                        }, (100 + timeBuff));
                    } else {
                        setTimeout(function(){
                            slice.animate({ width:origWidth, opacity:'1.0' }, settings.animSpeed);
                        }, (100 + timeBuff));
                    }
                    timeBuff += 50;
                    i++;
                });
            } else if(currentEffect === 'fade'){
                createSlices(slider, settings, vars);
                firstSlice = jQuery('.webikaslider-slice:first', slider);
                firstSlice.css({
                    'width': slider.width() + 'px',
                    'top'  : 0
                });
    
                firstSlice.animate({ opacity:'1.0' }, (settings.animSpeed*2), '', function(){ slider.trigger('webikaslider:animFinished'); });
            
            
            
            } else if(currentEffect === 'slideInRight'){
                createSlices(slider, settings, vars);
                
                firstSlice = jQuery('.webikaslider-slice:first', slider);
                firstSlice.css({
                    'width': '0px',
                    'opacity': '1'
                });

                firstSlice.animate({ width: slider.width() + 'px' }, (settings.animSpeed*2), '', function(){ slider.trigger('webikaslider:animFinished'); });
            } else if(currentEffect === 'slideInLeft'){
                createSlices(slider, settings, vars);
                
                firstSlice = jQuery('.webikaslider-slice:first', slider);
                firstSlice.css({
                    'width': '0px',
                    'opacity': '1',
                    'left': '',
                    'right': '0px'
                });

                firstSlice.animate({ width: slider.width() + 'px' }, (settings.animSpeed*2), '', function(){ 
                    // Reset positioning
                    firstSlice.css({
                        'left': '0px',
                        'right': ''
                    });
                    slider.trigger('webikaslider:animFinished'); 
                });
            } else if(currentEffect === 'boxRandom'){
                createBoxes(slider, settings, vars);
                
                totalBoxes = settings.boxCols * settings.boxRows;
                i = 0;
                timeBuff = 0;

                boxes = shuffle(jQuery('.webikaslider-box', slider));
                boxes.each(function(){
                    var box = jQuery(this);
                    if(i === totalBoxes-1){
                        setTimeout(function(){
                            box.animate({ opacity:'1' }, settings.animSpeed, '', function(){ slider.trigger('webikaslider:animFinished'); });
                        }, (100 + timeBuff));
                    } else {
                        setTimeout(function(){
                            box.animate({ opacity:'1' }, settings.animSpeed);
                        }, (100 + timeBuff));
                    }
                    timeBuff += 20;
                    i++;
                });
            } else if(currentEffect === 'boxRain' || currentEffect === 'boxRainReverse' || currentEffect === 'boxRainGrow' || currentEffect === 'boxRainGrowReverse'){
                createBoxes(slider, settings, vars);
                
                totalBoxes = settings.boxCols * settings.boxRows;
                i = 0;
                timeBuff = 0;
                
                // Split boxes into 2D array
                var rowIndex = 0;
                var colIndex = 0;
                var box2Darr = jQuery.makeArray(box2Darr);
                box2Darr[rowIndex] = [];
                
                
                boxes = jQuery('.webikaslider-box', slider);
                if(currentEffect === 'boxRainReverse' || currentEffect === 'boxRainGrowReverse'){
                    boxes = jQuery('.webikaslider-box', slider); 
                    arr = jQuery.makeArray(boxes);
                    boxes = arr.reverse();
                }
                jQuery(boxes).each(function(){
                    box2Darr[rowIndex][colIndex] = jQuery(this);
                    colIndex++;
                    if(colIndex === settings.boxCols){
                        rowIndex++;
                        colIndex = 0;
                        box2Darr[rowIndex] = jQuery.makeArray(box2Darr[rowIndex]);
                    }
                });
                
                // Run animation
                for(var cols = 0; cols < (settings.boxCols * 2); cols++){
                    var prevCol = cols;
                    for(var rows = 0; rows < settings.boxRows; rows++){
                        if(prevCol >= 0 && prevCol < settings.boxCols){
                            /* Due to some weird JS bug with loop vars 
                            being used in setTimeout, this is wrapped
                            with an anonymous function call */
                            (function(row, col, time, i, totalBoxes) {
                                var box = jQuery(box2Darr[row][col]);
                                var w = box.width();
                                var h = box.height();
                                if(currentEffect === 'boxRainGrow' || currentEffect === 'boxRainGrowReverse'){
                                    box.width(0).height(0);
                                }
                                if(i === totalBoxes-1){
                                    setTimeout(function(){
                                        box.animate({ opacity:'1', width:w, height:h }, settings.animSpeed/1.3, '', function(){ slider.trigger('webikaslider:animFinished'); });
                                    }, (100 + time));
                                } else {
                                    setTimeout(function(){
                                        box.animate({ opacity:'1', width:w, height:h }, settings.animSpeed/1.3);
                                    }, (100 + time));
                                }
                            })(rows, prevCol, timeBuff, i, totalBoxes);
                            i++;
                        }
                        prevCol--;
                    }
                    timeBuff += 100;
                }
            }           
        };
        
        var random = function getRandomInt(min, max)
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        
        // Shuffle an array
        var shuffle = function(arr){
            for(var j, x, i = arr.length; i; j = parseInt(Math.random() * i, 10), x = arr[--i], arr[i] = arr[j], arr[j] = x);
            return arr;
        };
        
        // For debugging
        var trace = function(msg){
            if(this.console && typeof console.log !== 'undefined') { console.log(msg); }
        };
        
        // Start / Stop
        this.stop = function(){
            if(!jQuery(element).data('webikaslider:vars').stop){
                jQuery(element).data('webikaslider:vars').stop = true;
                trace('Stop Slider');
            }
        };
        
        this.start = function(){
            if(jQuery(element).data('webikaslider:vars').stop){
                jQuery(element).data('webikaslider:vars').stop = false;
                trace('Start Slider');
            }
        };
        
        // Trigger the afterLoad callback
        settings.afterLoad.call(this);
        
        return this;
    };
        
    jQuery.fn.WebikaSlider = function(options) {
        return this.each(function(key, value){
            var element = jQuery(this);
            // Return early if this element already has a plugin instance
            if (element.data('webikaslider')) { return element.data('webikaslider'); }
            // Pass options to plugin constructor
            var WebikaSlider = new webikaSlider(this, options);
            // Store plugin object in this element's data
            element.data('webikaslider', WebikaSlider);
        });
    };
    
    
    //Default settings
    jQuery.fn.WebikaSlider.defaults = {
        effect: 'random',
        slices: 15,
        boxCols: 8,
        boxRows: 4,
        animSpeed: 500,
        pauseTime: 3000,
        startSlide: 0,
        directionNav: true,
        controlNav: true,
        controlNavThumbs: false,
        pauseOnHover: true,
        manualAdvance: false,
        prevText: 'Prev',
        nextText: 'Next',
        randomStart: false,
        beforeChange: function(){},
        afterChange: function(){},
        slideshowEnd: function(){},
        lastSlide: function(){},
        afterLoad: function(){},
        showTitle: true,
        nav_position: 'center',
        controlNavMode: 'thumbnails',
        directionNavMode: 'images',
        leftArrowImage : false,
        rightArrowImage : false,
        bullitImage : false
    };

    jQuery.fn._reverse = [].reverse;
    
})(jQuery);
jQuery.noConflict;
