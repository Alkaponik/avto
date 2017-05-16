<?php

class Phoenix_Multipletablerates_Model_Shipping_Rate_Result extends Mage_Shipping_Model_Rate_Result
{
    
    /**
     *  Sort rates by sort_order from min to max
     *
     *  @return	  Mage_Shipping_Model_Rate_Result
     */
    public function sortRatesBySortOrder()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        $tmp = array();
        foreach ($this->_rates as $i => $rate) {
            $tmp[$i] = $rate->getSortOrder();
        }

        natsort($tmp);

        foreach ($tmp as $i => $sortOrder) {
            $result[] = $this->_rates[$i];
        }

        $this->reset();
        $this->_rates = $result;
        return $this;
    }
}
