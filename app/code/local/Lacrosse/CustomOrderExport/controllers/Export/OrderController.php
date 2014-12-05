<?php

/**
 * Controller handling order export requests.
 */
class Lacrosse_CustomOrderExport_Export_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Exports orders defined by id in post param "order_ids" to csv and offers file directly for download
     * when finished.
     */
    public function csvExportAction()
    {
        $orders = $this->getRequest()->getPost('order_ids', array());
        $file = Mage::getModel('lacrosse_customorderexport/export_csv')->exportOrders($orders);
        $this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
}
?>