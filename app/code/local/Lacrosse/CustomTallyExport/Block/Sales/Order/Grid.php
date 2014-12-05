<?php

class Lacrosse_CustomTallyExport_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        
        $this->getMassactionBlock()->addItem(
      		'customtallyexport',
            array('label'=>$this->__('Tally Export to .csv file'), 'url'=>$this->getUrl('customtallyexport/export_order/csvexport'))
        );
    }
}
?>