<?php


class Intersec_Orderimportexport_Model_Payment_Method_Importedpayment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'imported';

    protected $_infoBlockType = 'intersec_orderimportexport/importedpayment';

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $this->getInfoInstance()->setAdditionalInformation('method',$data['additional_information']);
        return $this;
    }
}