<?php
/**
 * Observer to append option to export to csv to mass action select box in the orders grid.
 */
class Lacrosse_CustomOrderExport_Model_Observer
{
    /**
     * Extends the mass action select box to append the option to export to csv.
     * Event: core_block_abstract_prepare_layout_before
     */
    public function addMassaction($observer) 
    {
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order') 
        {
            $block->addItem('customorderexport', array(
                'label' => 'Custom Export to .csv file',
                'url' => Mage::app()->getStore()->getUrl('customorderexport/export_order/csvexport'),
            ));
        }
    }
}