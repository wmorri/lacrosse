<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alex
 * Date: 3/20/12
 * Time: 11:10 PM
 * To change this template use File | Settings | File Templates.
 */
class Intersec_Orderimportexport_Block_Importedpayment extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('orderimportexport/importedpayment.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('orderimportexport/pdf/importedpayment.phtml');
        return $this->toHtml();
    }
}