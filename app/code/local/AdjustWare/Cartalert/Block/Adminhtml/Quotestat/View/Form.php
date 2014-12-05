<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ ashZqSeapayomhBh('eb4b21317780153132f29b01c1ba0e3b');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_Quotestat_View_Form extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adjcartalert/quotestat.phtml'); 
    }
    
    public function getProductsHtml($products = array())
    {
        if(!is_array($products))        
        {
            return '';
        }
        $productModel = Mage::getModel('catalog/product');
        $html = '<table style="margin: 3px; width: 95%;">';
        foreach($products as $productId => $productQty)
        {
            $html .= '<tr><td>'.$productModel->load($productId)->getName().'</td><td><b>&nbsp;x&nbsp;</b></td><td>'.(int)$productQty.'</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }
    
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }


    public function getCustomer()
    {
        return $this->getParentBlock()->getCustomer();
    }

    public function getCustomerGroup()
    {
        return $this->getParentBlock()->getCustomerGroup();
    }    

    
    
} } 