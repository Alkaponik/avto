<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @product    Phoenix
 * @package    Phoenix_Multipletablerates
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_Multipletablerates_Model_Mysql4_Carrier_Multipletablerates extends Pw_Multipletablerates_Model_Mysql4_Carrier_Multipletablerates
{
    const PRODUCT_GROUP_DEFAULT = 0;
    const PRODUCT_GROUP_SPECIAL = 1;
    const CUSTOMER_GROUP_DEFAULT = 0;
    CONST SORT_DEFAULT = 10;
    CONST TRANSPORT_TYPE_DEFAULT = 0;
    const CONDITION_OPERATOR_DEFAULT = '>=';
    const XML_CONFIG_SPECIAL_ATTRIBUTESET_PATH = 'carriers/multipletablerates/product_special_attributeset';
    const XML_CONFIG_SPECIAL_TAXCLASS_PATH = 'carriers/multipletablerates/product_special_taxclass';

    protected $_rates = array();

    protected $_additionalConditions = array();

    protected $_hasAdditionalCoditionSpecificRate = array();

    public function _construct()
    {
        parent::_construct();
        $this->_initAdditionalConditions();
    }

    protected function _initAdditionalConditions()
    {
        $this->_additionalConditions = Mage::helper('phoenix_multipletablerates')
            ->getEnabledRateConditionCallbacks();
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
		$read = $this->_getReadAdapter();
        $additionalConditions = array_keys($this->_additionalConditions);
        $this->_hasAdditionalCoditionSpecificRate = array();

        $conditionOperator = Mage::helper('phoenix_multipletablerates')->getConditionOperator();

        if (!$conditionOperator){
            $conditionOperator = self::CONDITION_OPERATOR_DEFAULT;
        }

		$table = Mage::getSingleton('core/resource')->getTableName('multipletablerates_shipping/multipletablerates');

		$select = $read->select()->from($table);

		$select->where(
            $read->quoteInto(" (dest_country_id=? ", $request->getDestCountryId()).
                $read->quoteInto(" AND dest_region_id=? ", $request->getDestRegionId()).
                $read->quoteInto(" AND dest_zip=?) ", $request->getDestPostcode()).

            $read->quoteInto(" OR (dest_country_id=? ", $request->getDestCountryId()).
                $read->quoteInto(" AND dest_region_id=? AND dest_zip='') ", $request->getDestRegionId()).

            $read->quoteInto(" OR (dest_country_id=? AND dest_region_id='0' AND dest_zip='') ", $request->getDestCountryId()).

            $read->quoteInto(" OR (dest_country_id=? AND dest_region_id='0' ", $request->getDestCountryId()).
                $read->quoteInto("  AND dest_zip=?) ", $request->getDestPostcode()).

            " OR (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
        );

        if (is_array($request->getConditionName()))
        {
            $i = 0;

            foreach ($request->getConditionName() as $conditionName)
            {
                if ($i == 0)
                {
                    $select->where('condition_name=?', $conditionName);
                }
                else
                {
                	$select->orWhere('condition_name=?', $conditionName);
                }

                $select->where("condition_value{$conditionOperator}", $request->getData($conditionName));
                $i++;
            }
        }
        else
        {
            $select->where('condition_name=?', $request->getConditionName());
            $select->where("? {$conditionOperator} condition_value", $request->getData($request->getConditionName()));
        }

        $select->where('website_id=?', $request->getWebsiteId());

        foreach ($this->_additionalConditions as $key => $method){
            if (method_exists($this, $method)) {
                if ($$key = $this->$method($request)) {
                    $select->where($read->quoteInto("({$key} = ? OR {$key}= 0)", $$key));
                }else {
                    $select->where("{$key} = 0");
                }
            }else{
                Mage::throwException("Rate condition method ($method) doesn't exist");
            }
            $this->_hasAdditionalCoditions[$key] = false;
        }

		//$select->group('method_code');
		//$select->group('dest_zip');
		//$select->group('dest_region_id');
		//$select->group('dest_country_id');
        //$select->group('customer_group_id');

        $groupColumns = array(
            'method_code',
            'dest_zip',
            'dest_region_id',
            'dest_country_id',
        );

        $groupColumns = array_merge($groupColumns, array_keys($this->_additionalConditions));
        
        $select->order('dest_zip DESC');
        $select->order('dest_region_id DESC');
        $select->order('dest_country_id DESC');

        $order = $conditionOperator == self::CONDITION_OPERATOR_DEFAULT
                ? 'DESC'
                : 'ASC';
        $select->order("condition_value {$order}");

        foreach ($additionalConditions as $key){
            $select->order("{$key} DESC");
        }
        $select->order('sort ASC');

        $rows = $read->fetchAll($select);

        /*
         * Check to see if any zip code or region specific rates exist.
         */
        $specific = array();
        $failover = array();

        $zipFound = $regionFound = false;

        /*
         * Most specific: Zip code.
         * Check for any not null zip codes, which would mean that we have a specific shipping method for that zipcode
         */
        $hasCustomerGroupSpecificRate = false;
        $hasProductGroupSpecificRate = false;

        $filteredRows = array();
        foreach ($rows as $row){
            $key = array();
            foreach ($groupColumns as $column){
                $key[] = $row[$column];
            }
            $key = implode('_', $key);
            if (!isset($filteredRows[$key])){
                $filteredRows[$key] = $row;
            }
        }

        $rows = $filteredRows;

        foreach($rows as $row)
        {
            foreach ($additionalConditions as $key){
                if ($$key
                        && empty($this->_hasAdditionalCoditionSpecificRate[$key])
                        && !empty($row[$key])){
                    $this->_hasAdditionalCoditionSpecificRate[$key] = true;
                }
            }

        	if (!empty($row['dest_zip']))
        	{
        		$specific[] = $row;
        		$zipFound = true;
        	}
        	elseif (!empty($row['dest_region_id']) && $zipFound == false)
        	{
        		$specific[] = $row;
        		$regionFound = true;
        	}
        	elseif (!empty($row['dest_country_id']) && $zipFound == false && $regionFound == false)
        	{
        		$specific[] = $row;
        	}

			if (empty($row['dest_country_id']) && empty($row['dest_region_id']) && empty($row['dest_zip']))
			{
				$failover[] = $row;
			}
        }

        if (count($specific) > 0)
        {
        	$rates = $specific;
        }
        else
        {
        	$rates = $failover;
        }

        //Removing all rates without additional condition if one defined
        foreach (array_keys($this->_hasAdditionalCoditionSpecificRate) as $conditionKey){
            foreach ($rates as $key => $value){
                if (empty($value[$conditionKey])){
                    unset($rates[$key]);
                }
            }
        }

        /*
         * Great, we have something specific for the zip code or region, let's return just those
         */

        return $rates;
	}

    public function getRequestCustomerGroupId($request)
    {
        $firstItem = current($request->getAllItems());
        $customerGroupId = self::CUSTOMER_GROUP_DEFAULT;

        if ($firstItem) {
            $customerGroupId = $firstItem->getQuote()->getCustomerGroupId();
        }

        return $customerGroupId;
    }

    public function getRequestProductGroupId($request)
    {
        $productGroupId = self::PRODUCT_GROUP_DEFAULT;
        foreach ($request->getAllItems() as $item){
            $productGroupId = $this->getProductGroup($item->getProduct());
            if ($productGroupId == self::PRODUCT_GROUP_DEFAULT){
                break;
            }
        }

        return $productGroupId;
    }

    public function getRequestTransportType($request)
    {
        return $request->getCarrierModel()
                ? $request->getCarrierModel()->getRequestTransportType($request)
                : null;
    }
	
    public function uploadAndImport(Varien_Object $object)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["multipletablerates"]["fields"]["import"]["value"];

        if (!empty($csvFile)) 
        {
            $csv = trim(file_get_contents($csvFile));
            
            $table = Mage::getSingleton('core/resource')->getTableName('multipletablerates_shipping/multipletablerates');

            $websiteId = $object->getScopeId();

            if (isset($_POST['groups']['multipletablerates']['fields']['condition_name']['inherit'])) 
            {
                $conditionName = (string)Mage::getConfig()->getNode('default/carriers/multipletablerates/condition_name');
            } 
            else 
            {
                $conditionName = $_POST['groups']['multipletablerates']['fields']['condition_name']['value'];
            }

            $conditionFullName = Mage::getModel('multipletablerates_shipping/carrier_multipletablerates')->getCode('condition_name_short', $conditionName);
            
            if (!empty($csv)) 
            {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 9) 
                {
                    $exceptions[0] = Mage::helper('shipping')->__('Invalid Table Rates File Format');
                }

                $countryCodes = array();
                $regionCodes = array();
                
                foreach ($csvLines as $k => $csvLine) 
                {
                    $csvLine = $this->_getCsvValues($csvLine);
                    
                    if (count($csvLine) > 0 && count($csvLine) < 9) 
                    {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Table Rates File Format');
                    } 
                    else 
                    {
                        $countryCodes[] = $csvLine[0];
                        $regionCodes[] = $csvLine[1];
                    }
                }

                if (empty($exceptions)) 
                {
                    $data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();

                    $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                    
                    foreach ($countryCollection->getItems() as $country) 
                    {
                        $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                        $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                        $countryCodesIso2[] = $country->getData('iso2_code');
                    }

                    $regionCollection = Mage::getResourceModel('directory/region_collection')
                        ->addRegionCodeFilter($regionCodes)
                        ->addCountryFilter($countryCodesIso2)
                        ->load();

                    foreach ($regionCollection->getItems() as $region) 
                    {
                        $regionCodesToIds[$countryCodesToIds[$region->getData('country_id')]][$region->getData('code')] = $region->getData('region_id');                        
                    }
                    
                    foreach ($csvLines as $k => $csvLine) 
                    {
                    	
                        $csvLine = $this->_getCsvValues($csvLine);

                        /*
                         * Column 1 - Country
                         */
                        if (empty($countryCodesToIds) || !array_key_exists($csvLine[0], $countryCodesToIds)) 
                        {
                            $countryId = '0';
                            
                            if ($csvLine[0] != '*' && $csvLine[0] != '') 
                            {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $csvLine[0], ($k+1));
                            }
                        } 
                        else 
                        {
                            $countryId = $countryCodesToIds[$csvLine[0]];
                        }

                        
                        /*
                         * Column 2 - Region/State
                         */
#                        if (empty($regionCodesToIds[$countryCodesToIds[$csvLine[0]]]) || !array_key_exists($csvLine[1], $regionCodesToIds[$countryCodesToIds[$csvLine[0]]]))                         
	 					if ($countryId == '0')
	 					{
	 						$regionId = '0';
	 					}
	 					else
	 					{
                        	if (empty($regionCodesToIds[$countryCodesToIds[$csvLine[0]]]) || !array_key_exists($csvLine[1], $regionCodesToIds[$countryCodesToIds[$csvLine[0]]])) 
                        	{
                            	$regionId = '0';
                            
                            	if ($csvLine[1] != '*' && $csvLine[1] != '') 
                            	{
                                	$exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $csvLine[1], ($k+1));
                            	}
                        	} 
                        	else 
                        	{
                            	$regionId = $regionCodesToIds[$countryCodesToIds[$csvLine[0]]][$csvLine[1]];
                        	}
	 					}
                        /*
                         * Column 3 - Zip/Postal Code
                         */
                        if ($csvLine[2] == '*' || $csvLine[2] == '') 
                        {
                            $zip = '';
                        } 
                        else 
                        {
                            $zip = $csvLine[2];
                        }

                        /*
                         * Column 4 - Order Subtotal
                         */
                        if (!$this->_isPositiveDecimalNumber($csvLine[3]) || $csvLine[3] == '*' || $csvLine[3] == '') 
                        {
                            $exceptions[] = Mage::helper('shipping')->__('Invalid %s "%s" in the Row #%s', $conditionFullName, $csvLine[3], ($k+1));
                        } 
                        else 
                        {
                            $csvLine[3] = (float)$csvLine[3];
                        }

                        /*
                         * Column 5 - Shipping Price
                         */
                        if (!$this->_isPositiveDecimalNumber($csvLine[4])) 
                        {
                            $exceptions[] = Mage::helper('shipping')->__('Invalid Shipping Price "%s" in the Row #%s', $csvLine[4], ($k+1));
                        } 
                        else 
                        {
                            $csvLine[4] = (float)$csvLine[4];
                        }
                        
                        /* 
                         * Column 6 - Method Code
                         */
                        $methodCode = strtolower($csvLine[5]);
                        
                        /*
                         * Column 7 - Method Name
                         */
                        $methodName = $csvLine[6];
                        
                        /*
                         * Column 8 - Method Description
                         */
                        $methodDescription = $csvLine[7];
                        
                        /*
                         * Column 9 - Condition type ("percent" or "value")
                         */
                        $conditionType = $csvLine[8];
                        
                        $data[] = array(
                        	'website_id' => $websiteId, 
                        	'dest_country_id' => $countryId, 
                        	'dest_region_id' => $regionId, 
                        	'dest_zip' => $zip, 
                        	'condition_name' => $conditionName,
                        	'condition_value' => $csvLine[3],
                        	'condition_type' => $conditionType,
                        	'method_code' => $methodCode, 
                        	'method_name' => $methodName, 
                        	'method_description' => $methodDescription,
                        	'price' => $csvLine[4],
                            'cost'  => $csvLine[9],
                            'customer_group_id' => isset($csvLine[10])
                                                        ? $csvLine[10]
                                                        : self::CUSTOMER_GROUP_DEFAULT,
                            'product_group_id'  => isset($csvLine[11])
                                                        ? $csvLine[11]
                                                        : self::PRODUCT_GROUP_DEFAULT,
                            'sort'              => isset($csvLine[12])
                                                        ? $csvLine[12]
                                                        : self::SORT_DEFAULT,
                            'transport_type'    => isset($csvLine[13])
                                                        ? $csvLine[13]
                                                        : self::TRANSPORT_TYPE_DEFAULT,
                        );
                    }
                }
                
                if (empty($exceptions)) 
                {
                    $connection = $this->_getWriteAdapter();

                    $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                        $connection->quoteInto('condition_name = ?', $conditionName),            
                    );

                    $connection->delete($table, $condition);

                    foreach($data as $k=>$dataLine) 
                    {
                        try 
                        {
                            $connection->insert($table, $dataLine);
                        } 
                        catch (Exception $e) 
                        {
                        	// This should probably show the exception message too.
                            $exceptions[] = Mage::helper('shipping')->__('Import error: ' . $e->getMessage());
                        }
                    }
                }

                if (!empty($exceptions)) 
                {
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }
            }
        }
    }

    public function getSpecialAttributeSet()
    {
        $result = Mage::getStoreConfig(self::XML_CONFIG_SPECIAL_ATTRIBUTESET_PATH);
        return $result ? explode(',', $result) : array();
    }

    public function getSpecialTaxclass()
    {
        $result = Mage::getStoreConfig(self::XML_CONFIG_SPECIAL_TAXCLASS_PATH);
        return $result ? explode(',', $result) : array();
    }

    public function getProductGroup($product)
    {
        if (in_array($product->getAttributeSetId(), $this->getSpecialAttributeSet())
                || in_array($product->getTaxClassId(), $this->getSpecialTaxclass())){
            return self::PRODUCT_GROUP_SPECIAL;
        }
        return self::PRODUCT_GROUP_DEFAULT;
    }

}

