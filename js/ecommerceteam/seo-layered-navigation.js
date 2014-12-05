var layered = {
    overlay:{},
    inProccess:false,
    navigationBlocks:[],
    init:function(){
        $$('div.block-layered-nav-new, div.toolbar').each(function(e){

            e.select('a').each(function(e){
                // Fix for stupid IE9
                e.onclick = function(){
                    if (this.hasClassName('checked')) {
                        this.removeClassName('checked');
                    } else {
                        this.addClassName('checked');
                    }
                    layered.setUrl(this.href);
                    return false;
                };
            });
            e.select('select').each(function(e){
                e.onchange = 'return false';
                Event.observe(e, 'change', function(e){
                    layered.setUrl(this.value);
                    Event.stop(e);
                });
            });
        });
        if (typeof TPG != 'undefined'
            && typeof TPG.Control != 'undefined'
            && typeof TPG.Control.Slider != 'undefined') {
            TPG.Control.Slider.manager.createAll();
        }


    },
    setUrl:function(url, method, params){
        if(!layered.inProccess){
            layered.inProccess = true;
            layered.showOverlay();
            var parameters = {'is_ajax':1};
            if (params) {
                Object.extend(parameters, params);
            }
            var request = new Ajax.Request(url,{
                    method: method || 'get',
                    parameters:parameters,
                    onSuccess: function(response){
                        var title = layered.updateBlocks(response.responseText.evalJSON());
                        layered.inProccess = false;
                        layered.hideOverlay();
                        if (window.History.enabled ) {
						   url = url.replace("is_ajax=1&",'');	
		                   window.History.replaceState('',title,url);
                        }
                    },
                    onFailure: function(){
                        setLocation(url);
                        layered.inProccess = false;
                        layered.hideOverlay();
                    }
                }
            );
        }
    },
    updateBlocks:function(data){

        var bufer = document.createElement('div');

        if (data.navigation_block_html) {
            for (blockId in data.navigation_block_html) {
                var html = data.navigation_block_html[blockId]['html']
                if (html) {
                    bufer.innerHTML = html;
                    if(script = data.navigation_block_html[blockId]['script']){
                        try {
                            eval(script);
                        } catch (e) {
                            if (console) {
                                console.log(e);
                            }
                        }
                    }
                }
                var navigationBlock = $(blockId);
                if(navigationBlock && html){
                    navigationBlock.parentNode.replaceChild(bufer.firstChild, navigationBlock);
                } else if(navigationBlock){
                    var emptyNode = document.createTextNode('');
                    this.navigationBlocks[blockId] = emptyNode;
                    navigationBlock.parentNode.replaceChild(emptyNode, navigationBlock);
                } else if(html){
                    if (navigationBlock = this.navigationBlocks[blockId]) {
                         navigationBlock.parentNode.replaceChild(bufer.firstChild, navigationBlock);
                        this.navigationBlocks[blockId] = null;
                    }
                }


            }
        }

        bufer.innerHTML = data.product_list_block_html;
        var product_list_block = $$('.category-products')[0];
        if (!product_list_block) {
            product_list_block = $$('.col-main .note-msg')[0];
        }

        product_list_block.parentNode.replaceChild(bufer.firstChild, product_list_block);

        var title = data.page_title;

        layered.init();

        return title;
    },
    showOverlay:function() {
        this.showIndicator();
        var product_list = $$('div.category-products');
        if(product_list.length > 0){
            product_list = product_list[0];
        }else if(product_list = $$('div.col-main p.note-msg')){
            product_list = product_list[0];
        }else{
            return false;
        }
        this.createOverlay('products-list', product_list, false);
        var navigationBlocks = $$('div.block-layered-nav-new .block-content');
        for(var i = 0; i < navigationBlocks.length;i++){
            this.createOverlay('layered-navigation-'+i, navigationBlocks[i], true);
        }

    },
    hideOverlay:function(){
        for(i in this.overlay){
            this.overlay[i].style.display = 'none';
        }
        this.hideIndicator();
    },
    showIndicator:function() {
        var indicator = $('sln-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'sln-indicator';
            indicator.innerHTML = '<span>Please wait...</span>'
            indicator.style.display = 'none';
            document.body.appendChild(indicator);
        }
        indicator.style.display = 'block';
    },
    hideIndicator:function() {
        var indicator = $('sln-indicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    },
    createOverlay:function(id, container, showIndicator){
        if(this.overlay['sln-overlay-'+id]){
            var overlay = this.overlay['sln-overlay-'+id];
        }else{
            var overlay = document.createElement('div');
            overlay.id = 'sln-overlay-'+id;
            document.body.appendChild(overlay);
            this.overlay['sln-overlay-'+id] = overlay;
        }

        if(typeof SLN_IS_IE == 'boolean'){
            container.style.position = 'relative';
        }else{
            SLN_IS_IE = false;
        }

        overlay.style.top           = container.offsetTop + 'px';
        overlay.style.left          = container.offsetLeft - (SLN_IS_IE ? 1 : 0) + 'px';
        overlay.style.width         = container.offsetWidth + (SLN_IS_IE ? 1 : 0) + 'px';
        overlay.style.height        = container.offsetHeight + 'px';
        overlay.style.display       = 'block';
        overlay.style.background    = '#ffffff';
        overlay.style.position      = 'absolute';
        overlay.style.opacity       = '0.4';
        overlay.style.filter        = 'alpha(opacity: 40)';
    }
}

document.observe("dom:loaded", function() { layered.init(); });
