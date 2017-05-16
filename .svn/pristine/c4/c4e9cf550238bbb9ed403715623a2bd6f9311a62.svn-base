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

class Phoenix_Multipletablerates_Model_Carrier_Multipletablerates extends Pw_Multipletablerates_Model_Carrier_Multipletablerates
{

    const TRANSPORT_TYPE_CONDITION = 'transport_type';
    const CUSTOMER_GROUP_CONDITION = 'customer_group';
    const PRODUCT_GROUP_CONDITION = 'product_group';
    const PACKAGE_VALUE_INCL_TAX_CONDITION = 'package_value_incl_tax';
    const TRANSPORT_TYPE_ATTRIBUTE_CODE = 'transport_type';
    const DEFAULT_TRANSPORT_TYPE = 2;
    
    protected static $_sortOrder;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$request->getConditionName()) {
            $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

        if ($request->getConditionName() == self::TRANSPORT_TYPE_CONDITION){
            $request->setData(self::TRANSPORT_TYPE_CONDITION, $this->getRequestTransportType($request));
        }elseif($request->getConditionName() == self::PACKAGE_VALUE_INCL_TAX_CONDITION){
            $request->setData(self::PACKAGE_VALUE_INCL_TAX_CONDITION, $this->getRequestPackageValueInclTax($request));
        }

        $request->setCarrierModel($this);

        $result = Mage::getModel('shipping/rate_result');

        $rates = $this->getRate($request);

        foreach($rates as $rate)
        {
            if (!empty($rate) && $rate['price'] >= 0)
            {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier('multipletablerates');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('bestway_' . $rate['method_code'] . '_' 
                        . str_replace(array('\'', '"', ',', '/', '\\', '.', ':'), '', mb_ereg_replace('/(\s+)/', '_', $rate['method_name'])));
                $method->setMethodTitle($rate['method_name']);

				$price = $rate['price'];

				if ($rate['condition_type'] == 'percent')
				{
					// This COULD be troublesome, looks like $request->getConditionName() might be an array in some
					// scenarios. I'm not sure how to process that if it is. The following should fail if it does turn out
					// to be an array, so just keep a mental note of this.
					$price = ($price * $request->getData($request->getConditionName())) / 100;
				}

                $method->setPrice($this->getFinalPriceWithHandlingFee($price));
                $method->setCost($rate['cost']);
                $method->setSortOrder($rate['sort']);

                $result->append($method);
            }
        }

        return $result;
	}

	public function getCode($type, $code='')
    {
        $codes = array(
            'condition_name'=>$this->getConditionNames(),
            'condition_name_short'=>$this->getConditionShortNames(),
        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Tablerate Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Tablerate Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    public function getConditionNames()
    {
        return array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                self::PACKAGE_VALUE_INCL_TAX_CONDITION  => Mage::helper('phoenix_multipletablerates')->__('Price Incl. Tax vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
                self::TRANSPORT_TYPE_CONDITION => Mage::helper('phoenix_multipletablerates')->__('Transport Type')
            );
    }

    public function getConditionShortNames()
    {
        $operatorText = Mage::helper('phoenix_multipletablerates') ->getConditionOperatorText();
        if ($operatorText){
            $operatorText = " ($operatorText)";
        }
        return array(
                'package_weight' => Mage::helper('shipping')->__('Weight'.$operatorText),
                'package_value'  => Mage::helper('shipping')->__('Order Subtotal'.$operatorText),
                self::PACKAGE_VALUE_INCL_TAX_CONDITION  => Mage::helper('phoenix_multipletablerates')->__('Order Subtotal Incl Tax'.$operatorText),
                'package_qty'    => Mage::helper('shipping')->__('# of Items'.$operatorText),
                self::TRANSPORT_TYPE_CONDITION => Mage::helper('phoenix_multipletablerates')->__('Transport Type'.$operatorText)
            );
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
		return Mage::getResourceSingleton('multipletablerates_shipping/carrier_multipletablerates')->getRate($request);
	}

    public function getRequestTransportType($request)
    {
        $transportType = false;

        foreach ($request->getAllItems() as $item){
            $productTransportType = $item->getProduct()->getData(self::TRANSPORT_TYPE_ATTRIBUTE_CODE);
            if ($productTransportType > $transportType){
                $transportType = $productTransportType;
            }
        }
        
        if ($transportType === false){
            $transportType = self::DEFAULT_TRANSPORT_TYPE;
        }

        return $transportType;
    }

    public function getRequestPackageValueInclTax($request)
    {
        $subtotalInclTax = $request->getPackageValue();
        $address = $this->getShippingAddressByRequest($request);
        
        if ($address && ($totals = $address->getTotals())){
            if (isset($totals['subtotal']) && $totals['subtotal']->getData('value_incl_tax')){
                return $totals['subtotal']->getData('value_incl_tax');
            }
        }

        return $subtotalInclTax;
    }

    public function getQuoteByRequest($request)
    {
        foreach ($request->getAllItems() as $item){
            if ($item->getQuote()){
                return $item->getQuote();
            }
        }
        return null;
    }

    public function getShippingAddressByRequest($request)
    {
        $quote = $this->getQuoteByRequest($request);
        return $quote
                    ? $quote->getShippingAddress()
                    : null;

    }

    public function getBillingAddressByRequest($request)
    {
        $quote = $this->getQuoteByRequest($request);
        return $quote
                    ? $quote->getBillingAddress()
                    : null;

    }

    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = parent::checkAvailableShipCountries($request);
        //Mage_Shipping_Model_Carrier_Abstract
        if (!$result instanceof self){
            return $result;
        }
        $availableCountries = $this->getConfigData('strict_countries') ? 
                explode(',',$this->getConfigData('strict_countries'))
                : array();
        $showMethod = $this->getConfigData('showmethod');
        $billingAddress = $this->getBillingAddressByRequest($request);
        if ($availableCountries && $billingAddress){
            if (in_array($request->getDestCountryId(), $availableCountries)
                    && $billingAddress->getCountryId() != $request->getDestCountryId()){
                if ($showMethod){
                    $error = Mage::getModel('shipping/rate_result_error');
                    $error->setCarrier($this->_code);
                    $error->setCarrierTitle($this->getConfigData('title'));
                    $errorMsg = $this->getConfigData('strict_countries_message');
                    if (!$errorMsg){
                        $errorMsg = Mage::helper('shipping')->__('Shipping to selected country requires the same country specified in billing address.');
                    }
                    $error->setErrorMessage($errorMsg);
                    return $error;
                }else {
                    return false;
                }
            }
       }
        
        return $this;
    }
}