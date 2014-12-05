<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alex
 * Date: 3/23/12
 * Time: 9:02 PM
 * To change this template use File | Settings | File Templates.
 */

class Intersec_Orderimportexport_Model_Shipping_Importedshippingmethod extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_code = 'imported';

    public static $methodQueue = array();

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier('imported');
        $method->setCarrierTitle('Imported');

        $method->setMethod('imported');
        $method->setMethodTitle(array_shift(self::$methodQueue));

        $method->setPrice(0);
        $method->setCost(0);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array('imported'=>'imported');
    }
}