<?php
class Testimonial_Autolux_Model_Shipping_Carrier_Autolux
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'autolux';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @internal param \Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var $result Mage_Shipping_Model_Rate_Result */
        $result = Mage::getModel('shipping/rate_result');

        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }
}
