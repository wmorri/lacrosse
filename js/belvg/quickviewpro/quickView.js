/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Quickviewpro
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

var Quickview = Class.create();
Quickview.prototype   = {
    initialize: function(config)
    {
        this.config   = Object.extend({
            itemTags       : '.category-products .item',
            settings       : new Object,
            productIds     : new Array,
            ajaxUrl        : '',
            cachePrefix    : 'qCache_',
            buttonTemplate : '<a class="quickviewpro-button" rel="{{productId}}" href="javascript:void(0);">Quickview</a>'
        }, config);
        
        this.showFlag;
        this.closeFlag;
    },

    init: function()
    {
        this.showFlag  = true;
        this.closeFlag = true;

        this.initButtons();
        this.initAction();
    },

    initButtons: function()
    {
        var thisQ  = this;
        var button = new Template(this.config.buttonTemplate, new RegExp('(^|.|\\r|\\n)({{\\s*(\\w+)\\s*}})', ""));

        $$(this.config.itemTags).each(function(item, index) {
            item.insert({
                top : button.evaluate({
                    productId : thisQ.config.productIds[index]
                })
            });
        });
    },

    initAction: function()
    {
        var thisQ = this;
        $$(this.config.itemTags + ' .quickviewpro-button').each(function(button) {
            button.up().addClassName('quickviewpro-block');
            Event.observe(button, 'click', function(event) {
                var pro_id = button.readAttribute('rel');
                thisQ.ajax(pro_id);
            });
            
            if (thisQ.config.settings.jsposition) {
                thisQ.setButtonPosition(button);
            }
        });
    },
    
    setButtonPosition: function(button)
    {
        var img  = button.up().select('img')[0];
        button.writeAttribute('style', 'display: block !important;');
        var top  = img.positionedOffset().top + parseInt(img.getHeight() / 2 - button.clientHeight / 2);
        var left = img.positionedOffset().left + parseInt(img.getWidth() / 2 - button.clientWidth / 2);
        button.writeAttribute('style', 'top: ' + top + 'px; left: ' + left + 'px;');
    },

    slide: function(pro_id)
    {
        var popup = $('popup').select('.main-popup')[0];
        $('popup').select('.quickviewpro_popup_alpha')[0].setStyle( {height: popup.getStyle('height')} );
        $('popup').select('.quickviewpro_popup_alpha')[0].setStyle( {width: popup.getStyle('width')} );
        $('popup').select('.quickviewpro_popup_alpha')[0].show();

        if ($('popup').select('.next-product-view').size()) {
            $('popup').select('.next-product-view')[0].hide();
        }

        if ($('popup').select('.prev-product-view').size()) {
            $('popup').select('.prev-product-view')[0].hide();
        }

        this.ajax(pro_id);
    },
    
    ajax: function(pro_id) {
        if(this.showFlag){
            this.showFlag  = false;
            this.closeFlag = false;
            loader.show();
            var html = this.getCache(pro_id);
            if (html) {
                loader.hide();
                this.show(pro_id, html);
            } else {
                var thisQ = this;
                new Ajax.Request( this.config.ajaxUrl, {
                    method:      'post',
                    parameters: {'pro_id': pro_id},
                    onSuccess:  function(transport) {
                        loader.hide();
                        if (!thisQ.closeFlag) {
                            thisQ.show(pro_id, transport.responseText);
                            thisQ.setCache(pro_id, transport.responseText);
                        }
                    }
                });
            }
        }
    },
    
    show: function(pro_id, html) {
        var heightBody = document.getElementsByTagName('body')[0].clientHeight;
        if (this.config.settings.overlay_show) {
            $('quickviewpro-hider').setStyle({height: heightBody + 'px'});
            $('quickviewpro-hider').show();
        }

        this.setPosition();
        $('popup').update(html);
        $('popup').select('.quickviewpro_popup_alpha')[0].hide();
        $('popup').show();

        this.showAfter(pro_id);
        this.showAfterYourCode(pro_id);
    },

    setPosition: function() {
        var scrollPos = _getScroll();
        var topPos    = parseInt(0.1 * document.documentElement.clientHeight + scrollPos['scrollTop']);

        $('popup').setStyle({top: topPos + 'px'});
        if (window.outerWidth < parseInt($('popup').getStyle('width'))) {
            $('popup').setStyle({left: '0px', margin: '0 0 0 20px'});
        } else {
            $('popup').setStyle({left: '', margin: ''});
        }
    },

    close: function() {
        this.closeFlag = true;
        this.showFlag  = true;
        loader.hide();
        this.closeBefore();
        $('popup').update('');
        $('popup').hide();
        $('quickviewpro-hider').hide();
    },
    
    initPopupActions: function(pro_id) {
        var thisQ  = this;
        if (this.config.settings.navigation) {
            var index  = this.config.productIds.indexOf(pro_id.toString());
            var prevId = index - 1;
            var nextId = index + 1;

            if (typeof this.config.productIds[prevId] !== 'undefined' && $('popup').select('.prev-product-view').size()) {
                $('popup').select('.prev-product-view')[0]/*.writeAttribute('onclick', 'quickview.slide(' + this.config.productIds[prevId] + ')')*/.show();
                Event.observe($('popup').select('.prev-product-view')[0], 'click', function(event) {
                    thisQ.slide(thisQ.config.productIds[prevId]);
                });
            }

            if (typeof this.config.productIds[nextId] !== 'undefined' && $('popup').select('.next-product-view').size()) {
                $('popup').select('.next-product-view')[0]/*.writeAttribute('onclick', 'quickview.slide(' + this.config.productIds[nextId] + ')')*/.show();
                Event.observe($('popup').select('.next-product-view')[0], 'click', function(event) {
                    thisQ.slide(thisQ.config.productIds[nextId]);
                });
            }
        }

        Event.observe($('popup').select('.close-popap')[0], 'click', function(event) {
            thisQ.close();
        });

    },

    /* Required javascript initialization after quickview display */
    showAfter: function(pro_id)
    {
        this.initPopupActions(pro_id);

        this.showFlag = true;    
        new Varien.BTabs('.quickviewpro_tabs');

        switch (this.config.settings.media) {
            case 'quickviewpro_media_cloudzoom':
                break;
            case 'quickviewpro_media_fancybox':
                break;
            default:
                Event.observe($$('.popup .product-image img')[0], 'load', function() {
                    var product_zoom = new Product.Zoom('image', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint');
                });
        }

        if (this.config.settings.quickview_scroll) {
            var block = $$('.product-essential')[0];
            if ( this.config.settings.max_height >= (parseInt(block.getStyle('height')) + parseInt(block.getStyle('padding-top')) + parseInt(block.getStyle('padding-bottom'))) ) {
                $$('.product-view')[0].removeClassName('quickviewpro_scroll');
            }
        }

        if (this.config.settings.add_to_cart) {
            q_productAddToCartForm = new VarienForm('product_addtocart_form_' + pro_id);
            $$('body .popup .button').each(function(element) {
                element.setAttribute('onclick', 'q_productAddToCartForm.submit(this)');
            });
            //jQblvg('.wrap-qty').initQty();
        }

    },

    /* Your code after quickview display */
    showAfterYourCode: function(pro_id) {

    },

    /* Required javascript initialization before quickview close */
    closeBefore: function() {
        if (this.config.settings.media == 'quickviewpro_media_fancybox') {
            jQblvg('#fancybox-close').click();
        }
    },
    
    getCache: function(key) {
        return $.jStorage.get(this.config.cachePrefix + key);
    },
    
    setCache: function(key, value) {
        /*if($.jStorage.storageSize() > 128000)     // Clear cache
            $.jStorage.flush();*/
        $.jStorage.set(this.config.cachePrefix + key, value, {TTL: 600000});  // Removes a key from the storage after 10 min
    }
    
}

var mediaZoomer;

var czZoom = Class.create();
czZoom.prototype   = {
    initialize: function(config)
    {
        this.config   = Object.extend({
            // default options
        }, config);
        
        this.init();
    },

    init: function()
    {
        jQblvg('.cloud-zoom, .cloud-zoom-gallery').CloudZoom(this.config);
    },

    refresh: function(productId)
    {
        var big = jQblvg('.belvgcolorswatch-anchor');
        big.addClass('cloud-zoom').attr('id', 'zoom' + productId);

        jQblvg('.belvgcolorswatch-gallery').find('li > a').each(function() {
            if (!jQblvg(this).hasClass('cloud-zoom-gallery')) {
                var colorSwatchRel = eval('(' + jQblvg(this).attr('rel') + ')');
                jQblvg(this).addClass('cloud-zoom-gallery')
                    .attr('href', colorSwatchRel.largeimage)
                    .attr('rel', "useZoom: 'zoom" + productId + "', smallImage: '" + colorSwatchRel.smallimage + "' ");
            }
        });

        this.init();
    }
}

var fbZoom = Class.create();
fbZoom.prototype   = {
    initialize: function(config)
    {
        this.config   = Object.extend({
            // default options
        }, config);
        
        this.init();
    },

    init: function()
    {
        jQblvg("a.fancybox").fancybox(this.config);
    },

    refresh: function(productId)
    {
        var big = jQblvg('.belvgcolorswatch-anchor');
        big.addClass('fancybox').attr('id', 'zoom' + productId).attr('rel', 'fancy' + productId);

        jQblvg('.belvgcolorswatch-gallery').find('li > a').each(function() {
            if (!jQblvg(this).hasClass('fancybox')) {
                var colorSwatchRel = eval('(' + jQblvg(this).attr('rel') + ')');
                jQblvg(this).addClass('fancybox')
                    .attr('href', colorSwatchRel.largeimage)
                    .attr('rel', 'fancy' + productId);
            }
        });

        this.init();
    }
}
