<?php

/**
 * Exports orders to csv file. If an order contains multiple ordered items, each item gets
 * added on a separate row.
 */
class Lacrosse_CustomTallyExport_Model_Export_Csv extends SLandsbek_SimpleOrderExport_Model_Export_Abstract
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
		$common = array();
		$finaldata = array();
        $fileName = 'order_tally_export_'.date("Ymd_His").'.csv';
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');

        $this->writeHeadRow($fp);
        foreach ($orders as $order) {
            $order = Mage::getModel('sales/order')->load($order);
            $finaldata = $this->writeOrder($order, $fp);
			#print_r($finaldata);
			#echo "<br/>";
			#echo "[END DATA]<br/>";
            $common[] = $finaldata;
			#print_r($common);
			#echo "<br/>";
			#print_r($this->writeOrder($order, $fp));
			#print_r(array_count_values($this->writeOrder($order, $fp)));
        }
		#print_r($common);
		$onesize = "";
		$smallmedium = "";
		$largexlarge = "";
		$adultsmall = "";
		$adultmedium = "";
		$adultlargevalue = "";
		$adultxlargevalue = "";
		$adultxxlargevalue = "";  // create an empty array
		$record = array();
		$csvgroupingofdata = array();
		foreach ($common as $keys => $keyvalue)
		{
				#echo "T: " . $keys;
				#print_r($keyvalue);
				foreach ($keyvalue as $a => $b)
				{
					#echo "SKU: " . $a . "<br/>";
					#echo "ITEM: " . $b . "<br/>";
					$csvgroupingofdata[$a . '+_+' . $b] = $csvgroupingofdata[$a . '+_+' . $b] + 1;
				
				}
				$onesize += $keys['One Size'];
				$smallmedium += $keys['Small-Medium'];
				$largexlarge += $keys['Large-Xlarge'];
				$adultsmall += $keys['Adult-Small'];
				$adultmedium += $keys['Adult-Medium'];
				$adultlargevalue += $keys['Adult-Large'];
				$adultxlargevalue += $keys['Adult-XLarge'];
				$adultxxlargevalue += $keys['Adult-XXLarge'];
				
		}
		#print_r($csvgroupingofdata);
		ksort($csvgroupingofdata);
		
		$store_id = $order->getStoreId();
		$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
		if($valueid < 1) { $valueid =1; }
		$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
		$groupId = $customer->getData('group_id');
		$group = Mage::getModel('customer/group')->load($groupId);
		$customergroupname = $group->getCode();
		
		#echo "<BR><BR>";
		#print_r($csvgroupingofdata);
        foreach ($csvgroupingofdata as $individualsku => $individualskuvalue) {
			#print_r($individualsku);
			#print_r($individualskuvalue);
			$individualskudata = explode("+_+", $individualsku);
			$recordforexport = array($customergroupname, $individualskudata[0], $individualskudata[1], $individualskuvalue);
            fputcsv($fp, $recordforexport, self::DELIMITER, self::ENCLOSURE);
		}
		/*
		echo "One Size: " . $onesize ."<br/>";
		echo "Small-Medium: " . $smallmedium ."<br/>";
		echo "Large-Xlarge: " . $largexlarge ."<br/>";
		echo "Adult-Small: " . $adultsmall ."<br/>";
		echo "Adult-Medium: " . $adultmedium ."<br/>";
		echo "Adult-Large: " . $adultlargevalue ."<br/>";
		echo "Adult-XLarge: " . $adultxlargevalue ."<br/>";
		echo "Adult-XXLarge: " . $adultxxlargevalue;
		*/
		#print_r($date_count);
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
		$datafromorders = array();
        $orderItems = $order->getItemsCollection();
        $itemInc = 0;
		
        foreach ($orderItems as $item)
        {
            if (!$item->isDummy()) {
				$sizedata = $this->getItemOptions($item);
				$customnumber = explode(",", $sizedata);
				#echo "SIZE: " . $customnumber[0];
				if(is_array($customnumber)) {
					$customproductsize = explode(":", $customnumber[0]);
					#echo "SIZE: " . trim($customproductsize[1]) . "<br/>";
					if($customproductsize[1] != "") {
						$datafromorders[$this->getItemSku($item)] = trim($customproductsize[1]);
					} else {
						$datafromorders[$this->getItemSku($item)] = "One Size";
					}
				}
				#print_r($customnumber[1]);
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                #fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
            }
        }
		return $datafromorders;
    }

    /**
	 * Returns the head column names.
	 * 
	 * @return Array The array containing all column names
	 */
    protected function getHeadRowValues() 
    {
        return array(
            'Team Store',
            'Item SKU',
            'Item Size',
            'Total QTY'
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
        
		$store_id = $order->getStoreId();
		$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
		if($valueid < 1) { $valueid =1; }
		$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
		$groupId = $customer->getData('group_id');
		$group = Mage::getModel('customer/group')->load($groupId);
		$customergroupname = $group->getCode();
		
        return array(
			$customergroupname
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
		if($this->getItemOptions($item) !="") {
			$sizedata = $this->getItemOptions($item);
			$customnumber = explode(",", $sizedata);
			if(isset($customnumber[1])) {
				$finalsizedata = $customnumber[0];
				$finalcustomnumber = $customnumber[1];
			} else {
				$finalsizedata = $customnumber[0];
				$finalcustomnumber = '';
			}
		} else {
			$finalsizedata = "One Size";
			$finalcustomnumber = '';
		}
        return array(
			$itemInc,
            $item->getName(),
            $this->getItemSku($item),
            $finalsizedata,
            $this->formatPrice($item->getData('price'), $order),
            $this->formatPrice($item->getTaxAmount(), $order),
            $this->formatPrice($this->getItemTotal($item), $order),
			$finalcustomnumber
        );
    }
}
?>