<?php
class Pw_Multipletablerates_Model_Carrier_Multipletablerates extends Mage_Shipping_Model_Carrier_Abstract
{
	protected $_code = 'multipletablerates';

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$request->getConditionName()) {
            $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

        $result = Mage::getModel('shipping/rate_result');
        
        $rates = $this->getRate($request);
        
        foreach($rates as $rate)
        {
            if (!empty($rate) && $rate['price'] >= 0) 
            {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier('multipletablerates');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('bestway_' . $rate['pk']);
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
    
                $result->append($method);
            }            
        }

        return $result;
	}
	
	public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
		return Mage::getResourceModel('multipletablerates_shipping/carrier_multipletablerates')->getRate($request);
	}	

	public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight (and above)'),
                'package_value'  => Mage::helper('shipping')->__('Order Subtotal (and above)'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items (and above)'),
            ),

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

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('bestway'=>$this->getConfigData('name'));
    }
}