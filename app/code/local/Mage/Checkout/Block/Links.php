<?php

class Mage_Checkout_Block_Links extends Mage_Core_Block_Template
{

    /**
     * Add shopping cart link to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCartLink()
    {
        if ($parentBlock = $this->getParentBlock()) {
            $count = $this->helper('checkout/cart')->getSummaryCount();

            if( $count == 1 ) {
                $text = $this->__('Cart (%s item)', $count);
            } elseif( $count > 0 ) {
                $text = $this->__('Cart (%s items)', $count);
            } else {
                $text = $this->__('Cart');
            }

            $parentBlock->addLink($text, 'checkout/cart', $text, true, array(), 50, null, 'class="top-link-cart"');
        }
        return $this;
    }
}