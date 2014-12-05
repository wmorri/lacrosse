function findTopLeft(obj)
{
    var curleft = curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    return [curleft,curtop];
}

//class for onmouseover showing option name
Buble = Class.create();
Buble.prototype = 
{    
    isCreated : false,
    
    bubleTooltip : null,
    
    text : null, 
    
     initialize : function()
    {
        var me = this;    
    },  
        
    showToolTip : function(event)
    {
        if( !this.isCreated ){
            var element = Event.element(event);
            var attributeId = element.parentNode.id.replace('amconf-images-', '');
            if(!parseInt(attributeId))
                var attributeId = element.parentNode.parentNode.id.replace('amconf-images-', '');       
            var optionValues = element.id.split('-');
            var select = $('attribute' + attributeId);
            for (var i = 0; i < select.options.length; i++) {
                var option = select.options[i];
                if(option.value == optionValues[2]){
                    this.text = option.innerHTML; 
                    break;
                }
            }
         
            var bubleTooltip = $('bubble');
            var bubleMiddle = $('buble_middle');
			
			$('bubble').style.opacity = 0;
            new Effect.Opacity('bubble', { from: 0, to: 1, duration: 0.2 });
			
            bubleMiddle.innerHTML = this.text;
            bubleTooltip.style.display = 'block'; 
            var offset = findTopLeft(element);
            bubleTooltip.style.left =  offset[0] -153 + 'px';
            bubleTooltip.style.top =  offset[1] - bubleTooltip.getHeight() + 4 + 'px';

            this.isCreated = true;
            this.bubleTooltip = bubleTooltip;
            if(!this.text){
				$('bubble').hide();
                this.isCreated = false;    
            }
        }
    },
    
    hideToolTip : function()
    {
        if(this.isCreated){
			$('bubble').hide();
            this.isCreated = false;   
        }
    }
}
 var buble = new Buble();
 