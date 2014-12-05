
var Zoomer  = Class.create();

Zoomer.prototype = ({
    imageArray: [],
    initialize: function (element, options, preload)
    {
        //------------------------Main optionts------------------------------------------------------------------
        this.zoomEnable = Boolean(parseInt(LightboxOptions.zoomEnable));
        this.LBoxEnable = Boolean(parseInt(LightboxOptions.lBoxEnable));
        this.zoomerActivation = LightboxOptions.zoomerActivation; // Activate zoomer with click or with mouse hover
        this.viewerPosition = LightboxOptions.viewerPosition; // Set viewer's position (modes: right, left)
        this.showProductName = Boolean(parseInt(LightboxOptions.showProductName));
        this.ChangeMainImg = Boolean(parseInt(LightboxOptions.ChangeMainImg));
        //-------------------------------------------------------------------------------------------------------------
        this.scaleX       = 1;
        this.scaleY       = 1;
        this.scaleWH      = 1; // Ratio of width to height
        this.scaleHW      = 1;
        this.small_height = 0;
        this.small_width  = 0;
        this.small_top    = 0;
        this.small_bottom = 0;
        this.small_left   = 0;
        this.small_right  = 0;
        this.paddingH     = 0; // If image doesn't have a square shape Magento creates white spaces around the small image to make it square (size 265*265)
        this.paddingW     = 0;
        this.marginH      = 0; // If image was set with lbox it has a margins 
        this.marginW      = 0;
        this.lightboxTop  = 0;
        this.lightboxLeft = 0;
        this.divNameHeight = 30;
        this.paddingValue  = 1; // padding for container "new div"
        
        this.globalSmallWidth = this.globalSmallHeight = parseInt(LightboxOptions.MainImgSize); 
        //if($('image').up(2).getWidth() > this.globalSmallWidth){
       //     this.globalSmallWidth = this.globalSmallHeight = $('image').up(2).getWidth();
	//    $('loopdiv').style.top = this.globalSmallWidth - 24 + 'px';
	//    $('loopdiv').style.left = this.globalSmallWidth - 25 + 'px';
      //  }
        this.viewerPositionAroundSmallWindow = LightboxOptions.viewerMargin; 
        this.viewerWidth  = LightboxOptions.viewerWidth;
        this.viewerHeight = LightboxOptions.viewerHeight;
        this.workOnlyLBox = false;
        this.p = 0;
       
        if(!this.zoomEnable && this.LBoxEnable)
        {
            this.workOnlyLBox = true;
        }

        if(this.zoomEnable)
        {
            var zoomDiv = null;
            this.element    = $(element);
            this.image      = this.element.down('img');
            this.source     = {
                small: this.image.src,
                large: this.element.href
            }

            this.selected   = this.source.small;

            this.options = Object.extend({
                trigger:        null,
                afterZoomIn:    null,
                afterZoomOut:   null
            }, options || {});

            this.dimensions = {
                small: {
                    width:  this.image.getWidth(),
                    height: this.image.getHeight()
                }
            }

            if(this.viewerWidth < 100) this.viewerWidth = this.globalSmallWidth;
            if(this.viewerHeight < 100) this.viewerHeight = this.globalSmallHeight;
            
            if(this.viewerPositionAroundSmallWindow < 0)
            {
                this.viewerPositionAroundSmallWindow = 30;
            }
            
            var viewerPositionX = 0;
            var viewerPositionY = 0;

            switch(this.viewerPosition)
            {
                case 'left':
                     viewerPositionX = - parseInt(this.viewerPositionAroundSmallWindow) - parseInt(this.viewerWidth) - this.paddingValue * 2;
                     viewerPositionY = 0;
                break;
                case 'right':
                default:
                     viewerPositionX = parseInt(this.viewerPositionAroundSmallWindow) + parseInt($('image').parentNode.offsetWidth);
                     viewerPositionY = 0;
                break;
            }

            $('newdiv').style.left = viewerPositionX + 'px';
            $('newdiv').style.top = viewerPositionY + 'px';

            this.preload        = new Image(); 
            this.preload.src    = this.source.large;
            this.preload.onload = this.loaded.bind(this); 

            $('oImg').setAttribute('src', this.preload.src);

            var f = $$('div .amlabel-div');
            f = (f != '') ? f[0]: this.element;
            f.observe('click', this.click.bindAsEventListener(this));

            f.observe('mousemove', this.move.bindAsEventListener(this));

            if(this.zoomerActivation != 'click')
            {  
                if (f.addEventListener){
                   f.addEventListener('mouseout', function(){viewerClose()}, false); 
                } else if (f.attachEvent){
                   f.attachEvent('onmouseout', function(){viewerClose()});
                }
            }

            $('loopdiv').style.zIndex = 1;
            var ImgLeft = this.image.cumulativeOffset().left;

            if (this.options.trigger) 
            {
                $(this.options.trigger).observe('click', this.click.bindAsEventListener(this));
            }    
        }
        /******************************************************************************************************/
        if(this.LBoxEnable)
        {
            this.updateImageList();
            this.keyboardAction = this.keyboardAction.bindAsEventListener(this);
            if (LightboxOptions.resizeSpeed > 10) LightboxOptions.resizeSpeed = 10;
            if (LightboxOptions.resizeSpeed < 1)  LightboxOptions.resizeSpeed = 1;
            this.resizeDuration = LightboxOptions.animate ? ((11 - LightboxOptions.resizeSpeed) * 0.15) : 0;
            this.overlayDuration = LightboxOptions.animate ? 0.2 : 0;  // shadow fade in/out duration
            
            // When Lightbox starts it will resize itself from 250 by 250 to the current image dimension.
            // If animations are turned off, it will be hidden as to prevent a flicker of a
            // white 250 by 250 box.
            
            var size = (LightboxOptions.animate ? 250 : 1) + 'px';
            var objBody = $$('body')[0];
            
            if(!$('overlay'))
            {
                    objBody.appendChild(Builder.node('div',{id:'overlay'})); // black field with opacity 0,6
                    $('overlay').hide().observe('click', (function() { this.end(); }).bind(this));
                    
                    objBody.appendChild(Builder.node('div',{id:'lightbox'}, [
                        Builder.node('div',{id:'outerImageContainer'}, 
                            Builder.node('div',{id:'imageContainer'}, [
                                Builder.node('img',{id:'lightboxImage'}), 
                                Builder.node('div',{id:'hoverNav'}, [
                                    Builder.node('a',{id:'prevLink', href: '#' }),
                                    Builder.node('a',{id:'nextLink', href: '#' })
                                ]),
                                Builder.node('div',{id:'loading'}, 
                                    Builder.node('a',{id:'loadingLink', href: '#' }, 
                                        Builder.node('img', {src: LightboxOptions.fileLoadingImage})
                                    )
                                )
                            ])
                        ),
                        Builder.node('div', {id:'imageDataContainer'},
                            Builder.node('div',{id:'imageData'}, [
                                Builder.node('div',{id:'imageDetails'}, [
                                    Builder.node('span',{id:'caption'}),      
                                    Builder.node('span',{id:'numberDisplay'}) 
                                ]),
                                Builder.node('div',{id:'bottomNav'},
                                    Builder.node('a',{id:'bottomNavClose', href: '#' },
                                        Builder.node('img', { src: LightboxOptions.fileBottomNavCloseImage })
                                    )
                                )
                            ])
                        )
                    ]));
                    
                    $('lightbox').hide().observe('click', (function(event) { 
                         if (event.element().id == 'lightbox') 
                         this.end(); 
                    }).bind(this)); 
                    
                    $('outerImageContainer').setStyle({ width: size, height: size });
                    $('prevLink').observe('click', (function(event) { event.stop(); this.changeImage(LightboxOptions.activeImage - 1); }).bindAsEventListener(this));
                    $('nextLink').observe('click', (function(event) { event.stop(); this.changeImage(LightboxOptions.activeImage + 1); }).bindAsEventListener(this));
                    $('loadingLink').observe('click', (function(event) { event.stop(); this.end(); }).bind(this));
                    $('bottomNavClose').observe('click', (function(event) { event.stop(); this.end(); }).bind(this));

                    var th = this;
                    (function(){
                        var ids = 
                            'overlay lightbox outerImageContainer imageContainer lightboxImage hoverNav prevLink nextLink loading loadingLink ' + 
                            'imageDataContainer imageData imageDetails caption numberDisplay bottomNav bottomNavClose';   
                        $w(ids).each(function(id){ th[id] = $(id); });
                    }).defer();
            }
        }
        if(!this.zoomEnable && this.LBoxEnable)
        {
           this.workOnlyLBox = true;
        }
    },
    
    click: function (event) 
    {
        event.stop();
        switch(true)
        {  case this.zoomerActivation == 'click':
                if($('zoomimg').style.display =='none' && this.selected == this.source.large)
                {
                    this.selected = this.source.small;
                }
                if(this.selected == this.source.small)
                {
                    if (this.element.loaded) 
                    {   
                        var imgPreloader = new Image();
                        imgPreloader.src = $('image').parentNode.href; 

                        if($('image').width < imgPreloader.width && $('image').height < imgPreloader.height && imgPreloader.width > this.viewerWidth && imgPreloader.height > this.viewerHeight)                                   
                        {
                           this.selected=this.source.large; 

                           this.loaded();
                           if(this.zoomEnable && event.pointerX() > this.small_left && event.pointerX() < this.small_right && event.pointerY() > this.small_top && event.pointerY() < this.small_bottom)
                           {                
                              this.viewerOpen();
                              this.move(event);  
                           }  
                        }
                        if (this.options.afterZoomIn) 
                        {
                            this.options.afterZoomIn();
                        }
                    } 
                    else 
                    {                    
                        this.element.hide();                 
                        //Periodically check if target image has loaded
                        this.click.bind(this).delay(0.5, event);
                    }
                }
                else if(this.selected == this.source.large)
                {
                    if(event.pointerX() > this.small_left && event.pointerX() < this.small_right && event.pointerY() > this.small_top && event.pointerY() < this.small_bottom)
                    {
                       viewerClose();
                    }
                    
                    if (this.options.afterZoomOut) 
                    {
                        this.options.afterZoomOut();
                    }
                }
            break;
        }
    },
    move: function (event)
    {  
        this.loaded();

        var imgPreloader = new Image();
        imgPreloader.src = $('image').parentNode.href; 
        if(this.zoomEnable && $('image').width < imgPreloader.width && $('image').height < imgPreloader.height && imgPreloader.width > this.viewerWidth && imgPreloader.height > this.viewerHeight && Event.pointerX(event) > this.small_left && Event.pointerX(event) < this.small_right && Event.pointerY(event) > this.small_top && Event.pointerY(event) < this.small_bottom)
        {
            if($('divName').style.display == 'none' && this.zoomerActivation != 'click')
            {                       
                this.viewerOpen();
            }

            this.moveZoomImg(event);
            
            var x = Event.pointerX(event) - this.small_left;
            var y = Event.pointerY(event) - this.small_top;

            $('scroller').scrollLeft = x * this.scaleX - this.viewerWidth/2; 
            $('scroller').scrollTop = y * this.scaleY  - this.viewerHeight/2;
        }
        else
        {
            if($('zoomimg').style.display == 'block' && this.zoomerActivation != 'click')
            {
                viewerClose();
            }
        }
    },

    loaded: function () 
    {
        this.element.loaded = true;
        var imgPreloader = new Image();
        imgPreloader.src = $('image').parentNode.href;
        
        this.dimensions.large = 
        {
            width:  imgPreloader.width,
            height: imgPreloader.height
        };
        
        this.small_width = this.small_height = this.globalSmallWidth; 
        
        this.small_top=this.absPosition($('image').parentNode).y;    
        this.small_bottom=this.small_top + this.small_width;
        this.small_left=this.absPosition($('image').parentNode).x;   
        this.small_right=this.small_left + this.small_width;

        this.marginH = 0;
        this.marginW = 0;
        
        if($('oImg').width != $('oImg').height)
        {
            if($("oImg").width > $("oImg").height)
            {
                this.marginH = this.absPosition($('image')).y - this.absPosition($('image').parentNode).y;                 
                if(this.marginH > 0)
                {
                    this.setProperties();
                }
                else
                {
                    this.scaleWH = this.dimensions.large.width / this.dimensions.large.height;
                    this.paddingH = (this.small_height - this.small_width / this.scaleWH)/2;
                    this.small_height = this.small_width / this.scaleWH;
                    
                    this.small_top = this.absPosition($('image').parentNode).y + this.paddingH;  
                    this.small_bottom = this.small_top + this.small_height;
                }
            }
            else
            {
                this.marginW = this.absPosition($('image')).x - this.absPosition($('image').parentNode).x;   

                if(this.marginW > 0)
                {
                    this.setProperties();
                }
                else
                {   
                    this.scaleHW = this.dimensions.large.height / this.dimensions.large.width;
                    this.paddingW =(this.small_width - this.small_height / this.scaleHW)/2;
                    this.small_width = this.small_height / this.scaleHW;
                    
                    this.small_left = this.absPosition($('image').parentNode).x + this.paddingW;
                    this.small_right = this.small_left + this.small_width;
                }
            }
        }
        this.scaleX = $("oImg").getWidth() / this.small_width;
        this.scaleY = $("oImg").getHeight() / this.small_height;

        $('zoomimg').style.width = this.viewerWidth/this.scaleX + 'px';
        $('zoomimg').style.height = this.viewerHeight/this.scaleY + 'px';
         
        this.closeSmallZoomImg();
    },
    absPosition: function(obj)
    { 
        var x = y = 0;
        while(obj) 
        {
            x += obj.offsetLeft; 
            y += obj.offsetTop;  
            obj = obj.offsetParent;
        }
        return {x:x, y:y};
    },
    moveZoomImg: function(event)
    {   
        var zoomImgWidth = $('zoomimg').style.width.slice(0,$('zoomimg').style.width.indexOf('px'));
        var zoomImgHeight = $('zoomimg').style.height.slice(0,$('zoomimg').style.height.indexOf('px'));
              
        if(Event.pointerX(event) - zoomImgWidth/2 < this.small_left)
        {
            $('zoomimg').style.left=this.small_left - this.absPosition($('image')).x + this.marginW + 'px';   
        }
        else if(Event.pointerX(event) + zoomImgWidth/2 > this.small_right)
        {
            $('zoomimg').style.left = this.small_right - zoomImgWidth - this.absPosition($('image')).x + this.marginW + 'px';
        }
        else
        {
            $('zoomimg').style.left = Event.pointerX(event) - this.absPosition($('image')).x + this.marginW - zoomImgWidth/2 + 'px'; 
        }   
        
        if(Event.pointerY(event) - zoomImgHeight/2 < this.small_top)
        {
            $('zoomimg').style.top = this.small_top - this.absPosition($('image')).y + this.marginH + 'px';
        }
        else if(Event.pointerY(event) + zoomImgHeight/2 > this.small_bottom)
        {
            $('zoomimg').style.top = this.small_bottom - zoomImgHeight - this.absPosition($('image')).y + this.marginH + 'px';
        }
        else
        {
           $('zoomimg').style.top = Event.pointerY(event) - this.absPosition($('image')).y + this.marginH - zoomImgHeight/2 + 'px';
        }
    },
    setProperties: function()
    {
        this.small_width = $("image").width;
        this.small_height = $("image").height;
        this.small_top = this.absPosition($('image')).y;
        this.small_bottom = this.small_top + this.small_height;
        this.small_left = this.absPosition($('image')).x;  
        this.small_right=this.small_left + this.small_width;
    },
    closeSmallZoomImg: function()
    {
        var imgPreloader = new Image();
        imgPreloader.src = $('image').parentNode.href; 
       
        if(this.zoomEnable && $('image').width < imgPreloader.width && $('image').height < imgPreloader.height && $('image').width > $('zoomimg').width && $('image').height > $('zoomimg').height && imgPreloader.width > this.viewerWidth && imgPreloader.height > this.viewerHeight)
        {
            $('loopdiv').style.display ='block';
            $('image').parentNode.style.cursor = 'pointer';
        }
        else
        {
            $('loopdiv').style.display ='none';
            $('image').parentNode.style.cursor = 'default';
        }
    },
    
    //
    // updateImageList()
    // Loops through anchor tags looking for 'lightbox' references and applies onclick
    // events to appropriate links. You can rerun after dynamically adding images w/ajax.
    //
    
    updateImageList: function() {  
        
        this.updateImageList = Prototype.emptyFunction;
        if(this.workOnlyLBox)
        {    
            // mode 'Light box only':          
            if(this.ChangeMainImg)
            {
                document.observe('click', (function(event){
                var target = event.findElement('a[rel^=lightbox]') || event.findElement('area[rel^=lightbox]');
                    if (target)
                    {
                        if(LightboxOptions.flag == 0 || this.workOnlyLBox)
                       {   
                           event.stop();
                           this.start(target, false);
                       }
                    }
                }).bind(this));
            }
            
            document.observe('click', (function(event){
               var target = event.findElement('a[rel^=lightbox]') || event.findElement('area[rel^=lightbox]');
               if (target)
               {
                   if(LightboxOptions.flag == 0  || this.workOnlyLBox)
                   {
                      event.stop();
                      LightboxOptions.flag = 1;
                      this.start(target, true); 
                   }
               }
            }).bind(this));
            
            document.observe('click', (function(event){        
                                
               var target = event.findElement('a[rel=zoomer]');
               if (target)
               {
                   if(LightboxOptions.flag == 0)
                   {
                      event.stop();
                      LightboxOptions.flag = 1;
                      this.start(target, true);
                   }
               }
            }).bind(this));
            
            
        }
        else if(this.LBoxEnable && this.zoomEnable)
        {
            //mode 'Both':
            if(this.ChangeMainImg)
            {
                document.observe('mouseover', (function(event){
                    var target = event.findElement('a[rel^=lightbox]') || event.findElement('area[rel^=lightbox]');
                    
                    if (target) 
                    {
                        if(LightboxOptions.flag == 0)
                       {   
                           event.stop();
                           this.start(target, false);
                       }
                    }
                    
                }).bind(this)); 
            }
            
            document.observe('click', (function(event){
               var target = event.findElement('a[rel^=lightbox]') || event.findElement('area[rel^=lightbox]');
               if (target)
               {
                   if(LightboxOptions.flag == 0)
                   {
                      event.stop();
                      LightboxOptions.flag = 1;
                      this.start(target, true);
                   }
               }
            }).bind(this));
            
            if(this.zoomerActivation != 'click')
            { 
                this.element.observe('click', (function(event){                            
                   var target = event.findElement('a[rel=zoomer]');
                   if (target)
                   {
                       if(LightboxOptions.flag == 0)
                       {
                          event.stop();
                          LightboxOptions.flag = 1;
                          this.start(target, true);
                       }
                   }
                }).bind(this));
            }
        } 
    },
    //
    //  start()
    //  Display overlay and lightbox. If image is part of a set, add siblings to imageArray.
    //
    start: function(imageLink, flag) {  
        viewerClose();
        switch(flag)
        {
            case true:
                if(LightboxOptions.flag==1)
                {                    
                    var arrayPageSize = this.getPageSize();
                    this.changeImage(this.start2(imageLink).imageNum);
                    $('overlay').setStyle({ width: arrayPageSize[0] + 'px', height: arrayPageSize[1] + 'px' });
                    
                    $$('select', 'object', 'embed').each(function(node){ node.style.visibility = 'hidden' });
                    new Effect.Appear($('overlay'), { duration: this.overlayDuration, from: 0.0, to: LightboxOptions.overlayOpacity });
                    $('lightbox').setStyle({ top: this.lightboxTop + 'px', left: this.lightboxLeft + 'px' }).show();
                }
            break;
            case false:
                if(LightboxOptions.flag==0 || this.workOnlyLBox)
                {
                    if(this.zoomEnable)
                    {
                       this.loaded();
                    }
                    
                    this.viewImage(this.start2(imageLink).imageNum);
                }
            break;
        }
    },    
    start2: function(imageLink) {   
        this.imageArray = [];
        this.imageArray = LightboxOptions.images;
        
        var imageNum = 0;   
        if ((imageLink.getAttribute("rel") == 'lightbox')){
            // if image is NOT part of a set, add single image to imageArray
            this.imageArray.push([imageLink.href, imageLink.title]);       
        } else {
            // if image is part of a set
            while (this.imageArray[imageNum][0] != imageLink.href) { imageNum++; }
        }
        // calculate top and left offset for the lightbox
        var arrayPageScroll = document.viewport.getScrollOffsets();
        this.lightboxTop = arrayPageScroll[1] + (document.viewport.getHeight() / 10);        
        this.lightboxLeft = arrayPageScroll[0];
        return {imageNum:imageNum};
    },

    //
    //  changeImage()
    //  Hide most elements and preload image in preparation for resizing image container.
    //
    changeImage: function(imageNum) { 
        this.imageArray = [];                     
        this.imageArray = LightboxOptions.images; 

        LightboxOptions.activeImage = imageNum; // update global var    
        if(LightboxOptions.animate) $('loading').show();
        $('lightboxImage').hide();

        $('hoverNav').hide();
        $('prevLink').hide();
        $('nextLink').hide();
        
        // HACK: Opera9 does not currently support scriptaculous opacity and appear fx
        $('imageDataContainer').setStyle({opacity: .0001});
        $('numberDisplay').hide();      

        var imgPreloader = new Image();

        // once image is preloaded, resize image container
        imgPreloader.onload = (function(){
            $('lightboxImage').src = this.imageArray[LightboxOptions.activeImage][0];
            $('lightboxImage').width = imgPreloader.width;
            $('lightboxImage').height = imgPreloader.height;
            this.resizeImageContainer(imgPreloader.width, imgPreloader.height);
        }).bind(this);

        imgPreloader.src = this.imageArray[LightboxOptions.activeImage][0];
    },
    viewImage: function(imageNum)
    {        
        LightboxOptions.activeImage = imageNum;
        
        var imgPreloader = new Image(); 
        imgPreloader.src = this.imageArray[LightboxOptions.activeImage][0]; 

        var WidthImg = HeightImg = 0;
        var globSmall = this.globalSmallWidth;

        imgPreloader.onload = function(){
            WidthImg = this.width;
            HeightImg = this.height;
           
            if(WidthImg != HeightImg)
            {   
                if(WidthImg > HeightImg)
                {
                    var coefficient = WidthImg/globSmall;
                    var HeightPadding = (globSmall - HeightImg/coefficient)/2;
                    
                    $("image").style.width = WidthImg/coefficient + 'px';
                    $("image").style.height = HeightImg/coefficient + 'px';
                    $("image").style.marginTop = HeightPadding + 'px';
                    $("image").style.marginBottom = HeightPadding + 'px';
                    $("image").style.marginLeft = 0 + 'px';
                    $("image").style.marginRight = 0 + 'px';
                }
                else
                {
                    var coefficient = HeightImg/globSmall;  
                    var WidthPadding = (globSmall - WidthImg/coefficient)/2; 
                    
                    $("image").style.height = HeightImg/coefficient + 'px';
                    $("image").style.width = WidthImg/coefficient + 'px';
                    $("image").style.marginTop = 0 + 'px'; 
                    $("image").style.marginBottom = 0 + 'px';
                    $("image").style.marginLeft = WidthPadding + 'px'; 
                    $("image").style.marginRight = WidthPadding + 'px';        
                }
            }
            else
            {   
                var coefficient = WidthImg/globSmall;     
                 
                $("image").style.width = WidthImg/coefficient + 'px';
                $("image").style.height = HeightImg/coefficient + 'px';
                $("image").style.marginLeft = 0 + 'px';            
                $("image").style.marginRight = 0 + 'px';
                $("image").style.marginTop = 0 + 'px'; 
                $("image").style.marginBottom = 0 + 'px';
            }    
            
            $("image").src = imgPreloader.src;
        }
    
        if(this.zoomEnable)
        { 
            $("oImg").src = this.imageArray[LightboxOptions.activeImage][0];
        }
        
        $("image").parentNode.href = this.imageArray[LightboxOptions.activeImage][0];
        $("image").title = this.imageArray[LightboxOptions.activeImage][1];
        
        if($("image").src != this.imageArray[LightboxOptions.activeImage][0])
        {
           new Effect.Opacity($("image").parentNode.parentNode, { from: 0.5, to: 1.0, duration: 0.5 });
        }
    },

    //
    //  resizeImageContainer()
    //
    resizeImageContainer: function(imgWidth, imgHeight) {
        // get current width and height
        var widthCurrent  = $('outerImageContainer').style.width.split('px')[0]; 
        var heightCurrent = $('outerImageContainer').style.height.split('px')[0];
        // get new width and height
        var widthNew  = (imgWidth + LightboxOptions.borderSize * 2);
        var heightNew = (imgHeight + LightboxOptions.borderSize * 2);
        // scalars based on change from old to new
        var xScale = (widthNew  / widthCurrent)  * 100;
        var yScale = (heightNew / heightCurrent) * 100;
        // calculate size difference between new and old image, and resize if necessary
        var wDiff = widthCurrent - widthNew;
        var hDiff = heightCurrent - heightNew;

        if (hDiff != 0) new Effect.Scale($('outerImageContainer'), yScale, {scaleX: false, duration: this.resizeDuration, queue: 'front'}); 
        if (wDiff != 0) new Effect.Scale($('outerImageContainer'), xScale, {scaleY: false, duration: this.resizeDuration, delay: this.resizeDuration}); 
        // if new and old image are same size and no scaling transition is necessary, 
        // do a quick pause to prevent image flicker.
        var timeout = 0;
        if ((hDiff == 0) && (wDiff == 0)){
            timeout = 100;
            if (Prototype.Browser.IE) timeout = 250;   
        }

        (function(){
            $('prevLink').setStyle({ height: imgHeight + 'px' });
            $('nextLink').setStyle({ height: imgHeight + 'px' });
            $('imageDataContainer').setStyle({ width: widthNew + 'px' });
            this.showImage();
        }).bind(this).delay(timeout / 1000);
    },
    
    //
    //  showImage()
    //  Display image and begin preloading neighbors.
    //
    
    showImage: function(){
        $('loading').hide();
        new Effect.Appear($('lightboxImage'), { 
            duration: this.resizeDuration, 
            queue: 'end', 
            afterFinish: (function(){ this.updateDetails(); }).bind(this) 
        });
        this.preloadNeighborImages();
    },

    //
    //  updateDetails()
    //  Display caption, image number, and bottom nav.
    //
    updateDetails: function() {
        $('caption').update(this.imageArray[LightboxOptions.activeImage][1]).show();
        // if image is part of set display 'Image x of x' 
        if (this.imageArray.length > 1){
            $('numberDisplay').update( LightboxOptions.labelImage + ' ' + (LightboxOptions.activeImage + 1) + ' ' + LightboxOptions.labelOf + '  ' + this.imageArray.length).show();
        }

        new Effect.Parallel(
            [ 
                new Effect.SlideDown($('imageDataContainer'), { sync: true, duration: this.resizeDuration, from: 0.0, to: 1.0 }), 
                new Effect.Appear($('imageDataContainer'), { sync: true, duration: this.resizeDuration }) 
            ], 
            { 
                duration: this.resizeDuration, 
                afterFinish: (function() {
                    // update overlay size and update nav
                    var arrayPageSize = this.getPageSize();
                    $('overlay').setStyle({ width: arrayPageSize[0] + 'px', height: arrayPageSize[1] + 'px' });
                    this.updateNav();
                }).bind(this)
            } 
        );
    },

    //
    //  updateNav()
    //  Display appropriate previous and next hover navigation.
    //
    updateNav: function() {
        $('hoverNav').show();               

        // if not first image in set, display prev image button
        if (LightboxOptions.activeImage > 0) $('prevLink').show();

        // if not last image in set, display next image button
        if (LightboxOptions.activeImage < (this.imageArray.length - 1)) $('nextLink').show();
        
        this.enableKeyboardNav();
    },

    //
    //  enableKeyboardNav()
    //
    enableKeyboardNav: function() {
        document.observe('keydown', this.keyboardAction); 
    },

    //
    //  disableKeyboardNav()
    //
    disableKeyboardNav: function() {
        document.stopObserving('keydown', this.keyboardAction); 
    },

    //
    //  keyboardAction()
    //
    keyboardAction: function(event) {
        var keycode = event.keyCode;

        var escapeKey;
        if (event.DOM_VK_ESCAPE) {  // mozilla
            escapeKey = event.DOM_VK_ESCAPE;
        } else { // ie
            escapeKey = 27;
        }

        var key = String.fromCharCode(keycode).toLowerCase();
        
        if (key.match(/x|o|c/) || (keycode == escapeKey)){ // close lightbox
            this.end();
        } else if ((key == 'p') || (keycode == 37)){ // display previous image
            if (LightboxOptions.activeImage != 0){
                this.disableKeyboardNav();
                this.changeImage(LightboxOptions.activeImage - 1);
            }
        } else if((key == 'n') || (keycode == 39)){ // display next image
            if (LightboxOptions.activeImage != (this.imageArray.length - 1)){
                this.disableKeyboardNav();
                this.changeImage(LightboxOptions.activeImage + 1);
            }
        }
    },

    //
    //  preloadNeighborImages()
    //  Preload previous and next images.
    //
    preloadNeighborImages: function(){
        var preloadNextImage, preloadPrevImage;
        if (this.imageArray.length > LightboxOptions.activeImage + 1){
            preloadNextImage = new Image();
            preloadNextImage.src = this.imageArray[LightboxOptions.activeImage + 1][0];
        }
        if (LightboxOptions.activeImage > 0){
            preloadPrevImage = new Image();
            preloadPrevImage.src = this.imageArray[LightboxOptions.activeImage - 1][0];
        }
    },

    //
    //  end()
    //
    end: function() {
        this.disableKeyboardNav();
        $('lightbox').hide();
        new Effect.Fade($('overlay'), { duration: this.overlayDuration });
        $$('select', 'object', 'embed').each(function(node){ node.style.visibility = 'visible' });
        LightboxOptions.flag = 0;
    },

    //
    //  getPageSize()
    //
    getPageSize: function() {
            
         var xScroll, yScroll;
        
        if (window.innerHeight && window.scrollMaxY) {    
            xScroll = window.innerWidth + window.scrollMaxX;
            yScroll = window.innerHeight + window.scrollMaxY;
        } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
            xScroll = document.body.scrollWidth;
            yScroll = document.body.scrollHeight;
        } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
            xScroll = document.body.offsetWidth;
            yScroll = document.body.offsetHeight;
        }
        
        var windowWidth, windowHeight;
        
        if (self.innerHeight) {    // all except Explorer
            if(document.documentElement.clientWidth){
                windowWidth = document.documentElement.clientWidth; 
            } else {
                windowWidth = self.innerWidth;
            }
            windowHeight = self.innerHeight;
        } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
            windowWidth = document.documentElement.clientWidth;
            windowHeight = document.documentElement.clientHeight;
        } else if (document.body) { // other Explorers
            windowWidth = document.body.clientWidth;
            windowHeight = document.body.clientHeight;
        }    

        // for small pages with total height less then height of the viewport
        if(yScroll < windowHeight){
            pageHeight = windowHeight;
        } else { 
            pageHeight = yScroll;
        }
    
        // for small pages with total width less then width of the viewport
        if(xScroll < windowWidth){    
            pageWidth = xScroll;        
        } else {
            pageWidth = windowWidth;
        }

        return [pageWidth,pageHeight];
    },
    viewerOpen: function()
    {
        $('oImg').show();
        $('zoomimg').style.display = 'block';
        
        new Effect.Opacity('newdiv', { from: 0.5, to: 1.0, duration: 0.5 });
        
        $('newdiv').style.padding = this.paddingValue +'px';
        $('scroller').style.width = this.viewerWidth + 'px'; 
        $('scroller').style.height = this.viewerHeight + 'px';
        
        if(this.showProductName)
        {
            $('divName').style.left = this.paddingValue +'px';
            $('divName').style.top = this.paddingValue +'px';
            $('divName').style.height = this.divNameHeight + 'px';
            $('divName').style.lineHeight = this.divNameHeight + 'px';
            $('divName').style.width = this.viewerWidth + 'px';
            if($('image').getAttribute('title') != "")
            {
               $('divName').innerHTML = $('image').getAttribute('title');
            }
            else
            {
               $('divName').innerHTML = $('image').getAttribute('alt');      
            }      
            $('divName').style.display ='block';
        }
    }
});

function viewerClose()
{
    $('zoomimg').style.display ='none';
    $('oImg').hide();
    
    $('scroller').style.width = 0 + 'px';
    $('scroller').style.height = 0 + 'px';
    $('newdiv').style.padding = 0 +'px';
    
    $('divName').style.display ='none';
    $('divName').style.height = 0 + 'px';
    $('divName').style.width = 0 + 'px';
    $('divName').innerHTML = '';
}
