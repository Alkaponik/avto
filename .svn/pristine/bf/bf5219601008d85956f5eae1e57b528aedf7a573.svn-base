<?php

class Phoenix_Multipletablerates_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
    const DEFAULT_SORT = 'sortRatesByPrice';
    const XML_CONFIG_SHIPPING_RATE_SORT_PATH = 'shipping/option/rates_sort_method';

    public function collectCarrierRates($carrierCode, $request)
    {
        $carrier = $this->getCarrierByCode($carrierCode, $request->getStoreId());
        if (!$carrier) {
            return $this;
        }
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !($result instanceof Mage_Shipping_Model_Rate_Result_Error)) {
            $result = $carrier->proccessAdditionalValidation($request);
        }
        /*
        * Result will be false if the admin set not to show the shipping module
        * if the devliery country is not within specific countries
        */
        if (false !== $result){
            if (!$result instanceof Mage_Shipping_Model_Rate_Result_Error) {
                $result = $carrier->collectRates($request);
                if (!$result) {
                    return $this;
                }
            }
            if ($carrier->getConfigData('showmethod') == 0 && $result->getError()) {
                return $this;
            }
            // sort rates by price
            $sortFunction = $this->getRatesSortFunction();
            if (method_exists($result, $sortFunction)) {
                $result->$sortFunction();
            }
            $this->getResult()->append($result);
        }
        return $this;
    }

    public function getRatesSortFunction()
    {
        $sortFunction = Mage::getStoreConfig(self::XML_CONFIG_SHIPPING_RATE_SORT_PATH);
        if (!$sortFunction) {
            $sortFunction = self::DEFAULT_SORT;
        }
        return $sortFunction;
    }

}
