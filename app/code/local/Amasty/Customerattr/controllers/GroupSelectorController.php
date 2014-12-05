<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_GroupSelectorController extends Mage_Core_Controller_Front_Action
{
    public function getGroupDataAction()
    {
        $param = Mage::app()->getRequest()->getParam('param');
        if (!($param))
        {
            $this->getResponse()->setBody('');
        } else 
        {
            $customer = Mage::getModel('customer/customer');
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setEntity($customer)
                         ->setFormCode('adminhtml_customer')
                         ->initDefaultValues();
            $attributes = $customerForm->getAttributes();
            $values = array();
            foreach ($attributes as $attribute) {
                 if ($attribute->getAttributeCode() == 'group_id') {
                    $values = $attribute->getSource()->getAllOptions(true, true);
                 }
            }
            foreach($values as $key=>$val){
                $response[$val['value']] = $val['label'];
            }
            $result = Zend_Json::encode($response);
            $this->getResponse()->setBody(
                $result
            );
        }
    }
}