<?php

/**
 * Exports orders to csv file. If an order contains multiple ordered items, each item gets
 * added on a separate row.
 */
class Lacrosse_CustomOrderExport_Model_Export_Csv extends SLandsbek_SimpleOrderExport_Model_Export_Abstract
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    /**
     * Concrete implementation of abstract method to export given orders to csv file in var/export.
     *
     * @param $orders List of orders of type Mage_Sales_Model_Order or order ids to export.
     * @return String The name of the written csv file in var/export
     */
    public function exportOrders($orders) 
    {
        $fileName = 'order_custom_export_'.date("Ymd_His").'.csv';
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');

        $this->writeHeadRow($fp);
        foreach ($orders as $order) {
            $order = Mage::getModel('sales/order')->load($order);
            $this->writeOrder($order, $fp);
        }
		#exit;
        fclose($fp);

        return $fileName;
    }

    /**
	 * Writes the head row with the column names in the csv file.
	 * 
	 * @param $fp The file handle of the csv file
	 */
    protected function writeHeadRow($fp) 
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
	 * Writes the row(s) for the given order in the csv file.
	 * A row is added to the csv file for each ordered item. 
	 * 
	 * @param Mage_Sales_Model_Order $order The order to write csv of
	 * @param $fp The file handle of the csv file
	 */
    protected function writeOrder($order, $fp) 
    {
        $common = $this->getCommonOrderValues($order);

        $orderItems = $order->getItemsCollection();
        $itemInc = 0;
        foreach ($orderItems as $item)
        {
            if (!$item->isDummy()) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
            }
        }
    }

    /**
	 * Returns the head column names.
	 * 
	 * @return Array The array containing all column names
	 */
    protected function getHeadRowValues() 
    {
        return array(
            'Order Number',
            'Order Date',
            'Order Subtotal',
            'Order Subtotal with Tax',
            'Order Tax',
            'Order Shipping',
            'Order Grand Total',
            'Team Store',
            'Total Qty Items Ordered',
            'Customer Name',
            'Customer Email',
            'Shipping Name',
            'Shipping Address',
            'Shipping City',
            'Shipping State',
            'Shipping State Name',
            'Shipping Zip',
            'Shipping Country',
            'Shipping Country Name',
            'Shipping Phone',
            'Billing Name',
            'Billing Address',
            'Billing City',
            'Billing State',
            'Billing State Name',
            'Billing Zip',
            'Billing Country',
            'Billing Country Name',
            'Billing Phone Number',
	    'Order Item Increment',
            'Item Category',
            'Item Name',
            'Item SKU',
            'Item Size',
            'Item Price',
            'Item Tax',
            'Item Qty Ordered',
            'Item Total',
            'Custom Number',
            'Custom Name',
'Size & Options'
    	);
    }

    /**
	 * Returns the values which are identical for each row of the given order. These are
	 * all the values which are not item specific: order data, shipping address, billing
	 * address and order totals.
	 * 
	 * @param Mage_Sales_Model_Order $order The order to get values from
	 * @return Array The array containing the non item specific values
	 */
    protected function getCommonOrderValues($order) 
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();
        /*
		$tracknums = array();
		$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($order)->load();
		foreach ($shipmentCollection as $shipment){
			#print_r($shipment->getData()); 
			#print_r($shipment->getAllTracks());
			foreach($shipment->getAllTracks() as $tracknum)
			{
				#print_r($tracknum->getData());
				$tracknums[]=$tracknum->getNumber();
			}
		}
		*/
		$store_id = $order->getStoreId();
		$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
		if($valueid < 1) { $valueid =1; }
		$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
		$groupId = $customer->getData('group_id');
		$group = Mage::getModel('customer/group')->load($groupId);
		$customergroupname = $group->getCode();
		$finalsubtotalplustax = $order->getData('subtotal') + $order->getData('tax_amount');
        return array(
            $order->getRealOrderId(),
            Mage::helper('core')->formatDate($order->getCreatedAt(), 'medium', true),
			$this->formatPrice($order->getData('subtotal'), $order),
			$this->formatPrice($finalsubtotalplustax, $order),
			$this->formatPrice($order->getData('tax_amount'), $order),
            $this->formatPrice($order->getData('shipping_amount'), $order),
			$this->formatPrice($order->getData('grand_total'), $order),
			$customergroupname,
            $this->getTotalQtyItemsOrdered($order),
            $order->getCustomerName(),
            $order->getCustomerEmail(),
			$shippingAddress ? $shippingAddress->getName() : '',
		    $shippingAddress ? $shippingAddress->getData("street") : '',
		    $shippingAddress ? $shippingAddress->getData("city") : '',
			$shippingAddress ? $shippingAddress->getRegionCode() : '',
			$shippingAddress ? $shippingAddress->getRegion() : '',
		    $shippingAddress ? $shippingAddress->getData("postcode") : '',
			$shippingAddress ? $shippingAddress->getCountry() : '',
			$shippingAddress ? $shippingAddress->getCountryModel()->getName() : '',
		    $shippingAddress ? $shippingAddress->getData("telephone") : '',
			$billingAddress->getName(),
			$billingAddress->getData("street"),
			$billingAddress->getData("city"),
			$billingAddress->getRegionCode(),
			$billingAddress->getRegion(),
			$billingAddress->getData("postcode"),
			$billingAddress->getCountry(),
			$billingAddress->getCountryModel()->getName(),
			$billingAddress->getData("telephone")
        );
    }

    /**
	 * Returns the item specific values.
	 * 
	 * @param Mage_Sales_Model_Order_Item $item The item to get values from
	 * @param Mage_Sales_Model_Order $order The order the item belongs to
	 * @return Array The array containing the item specific values
	 */
    protected function getOrderItemValues($item, $order, $itemInc=1) 
    {
		
		 /* ADDITIONAL CATEGORY ID EXPORT FOR 1.4 ONLY [START] */
		$productSku = $this->getItemSku($item);
		$product = Mage::getModel('catalog/product');
		$productId = $product->getIdBySku($productSku);
		#echo "iD: " . $productId . "<br/>";
		 $finalcategoryIds = "";
		 $resource = Mage::getSingleton('core/resource');
		 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		 $read = $resource->getConnection('core_read');
		 $select_qryvalues2 = $read->query("SELECT category_id FROM `".$prefix."catalog_category_product` WHERE product_id = '".$productId."'");
		 foreach($select_qryvalues2->fetchAll() as $datavalues2)
		 { 	
		    #echo "CAT ID: " . $datavalues2['category_id'];
			$finalcategoryIds = $datavalues2['category_id'];
		 }
		 
		$cat = Mage::getModel('catalog/category')->load($finalcategoryIds);
		#echo "CAT: " . $cat->getName();
		$finalcategoriesproductname = $cat->getName();
		
		 /* ADDITIONAL CATEGORY ID EXPORT FOR 1.4 ONLY [END] */
		 
		if($this->getItemOptions($item) !="") {
			$sizedata = $this->getItemOptions($item);
			#print_r($sizedata);
			$customnumber = explode(",", $sizedata);
			#print_r($customnumber);
			if(isset($customnumber[2])) {
				#echo "D: " . $customnumber[1] . "<br/>";
				#echo "D: " . $customnumber[2] . "<br/>";
				$customnamedata = explode(":", $customnumber[1]);
				$customnamedata2 = explode(":", $customnumber[2]);
				#print_r($customnamedata);
				if(isset($customnamedata[0])) {
					if(trim($customnamedata[0]) == "Add Name") {	
						$finalsizedata = "One Size";
						$finalcustomname = $customnamedata[0] . ": ". $customnamedata[1];
					} else if(trim($customnamedata[0]) == "Add Number") {
						$finalsizedata = "One Size";
						$finalcustomnumber = trim($customnumber[1]);
					} 
				}
				if(isset($customnamedata2[0])) {
					if(trim($customnamedata2[0]) == "Add Name") {	
						$finalsizedata = "One Size";
						$finalcustomname = $customnamedata2[0] . ": ". $customnamedata2[1];
					} else if(trim($customnamedata2[0]) == "Add Number") {
						$finalsizedata = "One Size";
						$finalcustomnumber = trim($customnumber[2]);
					} 
				}
				
				$customsizedata = explode(":", $customnumber[0]);
				if(trim($customsizedata[0]) == "Size") {
					$finalsizedata = trim($customnumber[0]);
				} else if(trim($customsizedata[0]) == "Kilt or Short (Select one below)") {
					$finalsizedata = trim($customnumber[1]);
				}
			} else if(isset($customnumber[1])) {
				#echo "E: " . $customnumber[1] . "<br/>";
				$customnamedata = explode(":", $customnumber[1]);
				#print_r($customnamedata);
				if(isset($customnamedata[0])) {
					if(trim($customnamedata[0]) == "Add Name") {	
						$finalsizedata = "One Size";
						$finalcustomname = $customnamedata[0] . ": ". $customnamedata[1];
					} else if(trim($customnamedata[0]) == "Add Number") {
						$finalsizedata = "One Size";
						$finalcustomnumber = trim($customnumber[1]);
					} 
				}
				
				$customsizedata = explode(":", $customnumber[0]);
				if(trim($customsizedata[0]) == "Size") {
					$finalsizedata = trim($customnumber[0]);
				} else if(trim($customsizedata[0]) == "Kilt or Short (Select one below)") {
					$finalsizedata = trim($customnumber[1]);
				}
			} else {
				$finalcustomnumber = '';
				$finalcustomname = '';
				$customotherdata = explode(":", $customnumber[0]);
				#print_r($customotherdata);
				if(isset($customotherdata[0])) {
					if(trim($customotherdata[0]) == "Add Name") {
						$finalsizedata = "One Size";
						$finalcustomname = trim($customotherdata[0]) . ": ". $customotherdata[1];
					} else if(trim($customotherdata[0]) == "Add Number") {
						$finalsizedata = "One Size";
						$finalcustomnumber = trim($customotherdata[0]) . ": ". $customotherdata[1];
					} else if(trim($customotherdata[0]) == "Size") {
						$finalsizedata = trim($customotherdata[0]) . ": ". $customotherdata[1];
					} else if(trim($customotherdata[0]) == "Kilt or Short (Select one below)") {
						$finalsizedata = trim($customotherdata[1]);
					}
				}
			}
		} else {
			$finalsizedata = "One Size";
			$finalcustomnumber = '';
		}
        return array(
			$itemInc,
			$finalcategoriesproductname,
            $item->getName(),
            $this->getItemSku($item),
         $finalsizedata,
            $this->formatPrice($item->getData('price'), $order),
            $this->formatPrice($item->getTaxAmount(), $order),
            (int)$item->getQtyOrdered(),
            $this->formatPrice($this->getItemTotal($item), $order),
			$finalcustomnumber,
			$finalcustomname,
$this->getItemOptions($item)
        );
    }
}
?>