<?php
    /**
    * EcommerceTeam.com
    *
    * Seo Layered Navigation
    *
    * @category     Magento Extension
    * @copyright    Copyright (c) 2011 Ecommerce Team (http://www.ecommerce-team.com)
    * @author       Ecommerce Team
    * @version      3.0
    */


class EcommerceTeam_Sln_Block_Page_Vars extends Mage_Core_Block_Abstract
{
    protected function _toHtml(){
        $html  = '';
        /** @var $layer EcommerceTeam_Sln_Model_Layer */
        $layer = Mage::registry('current_layer');

        if (!$layer) {
            $layer = Mage::getSingleton('catalog/layer');
        }
        $attributes = $layer->getFilterableAttributes();
        $attributeComments = array();
        foreach ($attributes as $attribute) {
            if ($comment = $attribute->getComment()) {
                $attributeComments[$attribute->getAttributeCode()] = $comment;
            }
        }
        if (!empty($attributeComments)) {
            $html .= '<script type="text/javascript">
                        /* <!-- */
                        var attributeComment = '.Mage::helper('core')->jsonEncode($attributeComments).'
                        var showFilterComment = function(id, element){
                            if (attributeComment && attributeComment[id]) {
                                var text = attributeComment[id];
                                var noticeBlock = $(id+"-notice");
                                if (!noticeBlock) {
                                    noticeBlock = document.createElement("div");
                                    noticeBlock.id        = id+"-notice";
                                    noticeBlock.className = "sln-notice-block";
                                    noticeBlock.style.position = "absolute";
                                    if (text.length > 200) {
                                        noticeBlock.style.width = "300px";
                                    }
                                    noticeBlock.innerHTML      = text;

                                    document.body.appendChild(noticeBlock);
                                }

                                var offset = Element.cumulativeOffset(element);
                                noticeBlock.style.left = offset[0] + 30 + "px";
                                noticeBlock.style.top  = offset[1] + "px";
                                noticeBlock.style.display = "block";
                            }
                        }
                        var hideFilterComment = function(id, element){
                            var noticeBlock = $(id+"-notice");
                            if (noticeBlock) {
                                noticeBlock.style.display = "none";
                            }
                        }
                      /* --> */
                      </script>';
        }
        $html .= '<!--[if lt IE 8]><script type="text/javascript"> var SLN_IS_IE = true;</script><![endif]-->';
        if ($layer->getIsSliderEnabled()) {
            $html .= '<script type="text/javascript"> /* <!-- */ var imgPath = "'.$this->getSkinUrl('images/ecommerceteam/sln').'/"; /* --> */</script>';
        }
        return $html;
    }
}
