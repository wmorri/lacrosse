<?php

/**
 * Controller handling order export requests.
 */
class Lacrosse_CustomTallyExport_Export_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function csvExportAction()
    {
        $orders = $this->getRequest()->getPost('order_ids', array());
        $file = Mage::getModel('lacrosse_customtallyexport/export_csv')->exportOrders($orders);
        $this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
}
?>