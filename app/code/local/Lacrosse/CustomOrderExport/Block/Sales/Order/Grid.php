<?php
/**
 * Overrides Mage_Adminhtml_Block_Sales_Order_Grid to append option to export to csv 
 * to mass action select box in the orders grid.
 */
class Lacrosse_CustomOrderExport_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    /**
     * Extends the mass action select box to append the option to export to csv.
     */
    protected function _prepareMassaction()
    {
        // Let the base class do its work
        parent::_prepareMassaction();
        
        // Append option to export to csv to select box
        $this->getMassactionBlock()->addItem(
      		'customorderexport',
            array('label'=>$this->__('Custom Export to .csv file'), 'url'=>$this->getUrl('customorderexport/export_order/csvexport'))
        );
    }
}
?>