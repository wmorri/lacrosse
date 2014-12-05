<?php
    /**
    * EcommerceTeam.com
    *
    * Seo Layered Navigation
    *
    * @category     Magento Extension
    * @copyright    Copyright (c) 2011 Ecommerce Team (http://www.ecommerce-team.com)
    * @author       Ecommerce Team
    * @version      3.0
    */

class EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Catalog_Entity_Attribute extends Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute
{
    protected function _saveOption(Mage_Core_Model_Abstract $object)
    {
        $option = $object->getOption();
        if (is_array($option)) {
            $write = $this->_getWriteAdapter();
            $optionTable        = $this->getTable('attribute_option');
            $optionValueTable   = $this->getTable('attribute_option_value');
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();

            if (isset($option['value'])) {
                $attributeDefaultValue = array();
                if (!is_array($object->getDefault())) {
                    $object->setDefault(array());
                }

                foreach ($option['value'] as $optionId => $values) {
                    $intOptionId = (int) $optionId;
                    if (!empty($option['delete'][$optionId])) {
                        if ($intOptionId) {
                            $condition = $write->quoteInto('option_id=?', $intOptionId);
                            $write->delete($optionTable, $condition);
                        }
                        continue;
                    }

                    if (!$intOptionId) {
                        $data = array(
                           'attribute_id'  => $object->getId(),
                           'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        );
                        $write->insert($optionTable, $data);
                        $intOptionId = $write->lastInsertId();

                    } else {
                        $data = array(
                           'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        );
                        $write->update($optionTable, $data, $write->quoteInto('option_id=?', $intOptionId));
                    }
                    $attributeId   = $object->getId();
                    $optionData     = array();

                    if (isset($option['url_key'][$optionId])) {
                        $urlKey = preg_replace('/[^\w\-]/i', '', $option['url_key'][$optionId]);
                        $urlKey = trim($urlKey);
                        if ($urlKey){
                            $optionData['url_key'] = $urlKey;
                        } else {
                            $optionData['url_key'] = trim(preg_replace('/[^\w\-]/i', '', strtolower($option['value'][$optionId][0])));
                        }
                    }

                    if (isset($option['remove_image'][$intOptionId])) {
                        $optionData['image'] = '';
                        $ioObject = new Varien_Io_File();
                        $targetDirectory = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'attribute' . DS . $attributeId . DS . $intOptionId;
                        $ioObject->rmdir($targetDirectory, true);
                    }

                    if (isset($option['image'][$optionId])) {
                        $imageInfo = Mage::helper('core')->jsonDecode($option['image'][$optionId]);
                        if (is_array($imageInfo)) {
                            $imageInfo = array_shift($imageInfo);
                            if (!empty($imageInfo) && isset($imageInfo['status']) && $imageInfo['status'] == 'new') {
                                $image        = Mage::helper('ecommerceteam_sln')->moveImageFromTemp($imageInfo['file'], $attributeId, $intOptionId);
                                $optionData['image'] = $image;
                            }

                        }
                    }

                    if(!empty($optionData)){

                        $optionData['attribute_id']    = $attributeId;
                        $optionData['option_id']       = $intOptionId;

                        $table = Mage::getSingleton('core/resource')->getTableName('ecommerceteam_sln/attribute');
                        $write->insertOnDuplicate($table, $optionData);
                    }

                    if (in_array($optionId, $object->getDefault())) {
                        if ($object->getFrontendInput() == 'multiselect') {
                            $attributeDefaultValue[] = $intOptionId;
                        } else if ($object->getFrontendInput() == 'select') {
                            $attributeDefaultValue = array($intOptionId);
                        }
                    }

                    // Default value
                    if (!isset($values[0])) {
                        Mage::throwException(Mage::helper('eav')->__('Default option value is not defined.'));
                    }

                    $write->delete($optionValueTable, $write->quoteInto('option_id=?', $intOptionId));
                    foreach ($stores as $store) {
                        if (isset($values[$store->getId()]) && (!empty($values[$store->getId()]) || $values[$store->getId()] == "0")) {
                            $data = array(
                                'option_id' => $intOptionId,
                                'store_id'  => $store->getId(),
                                'value'     => $values[$store->getId()],
                            );
                            $write->insert($optionValueTable, $data);
                        }
                    }
                }


                $write->update($this->getMainTable(), array(
                    'default_value' => implode(',', $attributeDefaultValue)
                ), $write->quoteInto($this->getIdFieldName() . '=?', $object->getId()));
            }
        }
        return $this;
    }
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $ioObject = new Varien_Io_File();
        $targetDirectory = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'attribute' . DS . $object->getId();
        $ioObject->rmdir($targetDirectory, true);
        return parent::_afterDelete($object);
    }
}
