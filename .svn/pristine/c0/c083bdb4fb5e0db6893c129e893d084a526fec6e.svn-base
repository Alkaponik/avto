<?php

class Testimonial_MageDoc_Helper_Price extends Mage_Core_Helper_Abstract
{
    const DEFAULT_CUSTOMER_GROUP_ID = 1;
    const MARGIN_RATIO_PATH = 'magedoc/import/price_margin_ratio';
    const CONFIG_XML_PATH_DISCOUNT_TABLE = 'magedoc/import/price_discount_table';
    const CONFIG_XML_PATH_MARGIN_TABLE = 'magedoc/import/price_margin_table';
    const CONFIG_XML_PATH_SIGNIFICANT_DEVIATION_PERCENT = 'magedoc/import/significant_deviation_percent';
    const AGGREGATOR_RETAILER_ID = 0;

    protected $_marginRatio = NULL;

    /** Default discount table @var array   */
    protected $_discountTable = array(
        /*1 => array(
                1000    => 25,
                500     => 20,
                200     => 15,
                50      => 12,
                0       => 10),*/
        1 => array(
            1000 => 30,
            500 => 25,
            200 => 20,
            50 => 17,
            0 => 15),
        2 => array(
            1000 => 30,
            500 => 25,
            200 => 20,
            50 => 17,
            0 => 15),
        3 => array(
            1000 => 35,
            500 => 30,
            200 => 25,
            50 => 23,
            0 => 20),
        4 => array(
            1000 => 40,
            500 => 35,
            200 => 30,
            50 => 25,
            0 => 22)
    );

    /** Default margin table @var array */
    protected $_marginTable = array(
        /*1 => array(
                0   => 100,
                5   => 50,
                100 => 40,
                200 => 35,
                500 => 27,
                1000 => 25,
                3000 => 20),*/
        1 => array(
            0 => 80,
            5 => 40,
            100 => 30,
            200 => 25,
            500 => 23,
            1000 => 20,
            3000 => 17,
            5000 => 15,
            10000 => 13),
        2 => array(
            0 => 80,
            5 => 40,
            100 => 30,
            200 => 25,
            500 => 23,
            1000 => 20,
            3000 => 17,
            5000 => 15,
            10000 => 13),
        3 => array(
            0 => 65,
            5 => 30,
            100 => 25,
            200 => 20,
            500 => 19,
            1000 => 18,
            3000 => 15,
            5000 => 14,
            10000 => 12),
        4 => array(
            0 => 50,
            5 => 20,
            100 => 17,
            200 => 16,
            500 => 15,
            1000 => 13,
            3000 => 10)
    );

    protected $_retailers = array();

    public function __construct()
    {
        if ($discountTable = @unserialize(Mage::getStoreConfig(self::CONFIG_XML_PATH_DISCOUNT_TABLE))) {
            $this->_discountTable[self::DEFAULT_CUSTOMER_GROUP_ID] = $this->arrayToHash($discountTable);
        }
        if ($marginTable = @unserialize(Mage::getStoreConfig(self::CONFIG_XML_PATH_MARGIN_TABLE))) {
            $this->_marginTable[self::DEFAULT_CUSTOMER_GROUP_ID] = $this->arrayToHash($marginTable);
        }
    }

    public function getMarginRatio()
    {
        if(is_null($this->_marginRatio)) {
            $this->_marginRatio = Mage::getStoreConfig(static::MARGIN_RATIO_PATH);
        }

        return $this->_marginRatio;
    }

    public function getRetailerById($retailerId)
    {
        if (!isset($this->_retailers[$retailerId])){
            $this->_retailers[$retailerId] = Mage::getModel('magedoc/retailer')
                ->load($retailerId);
        }
        return $this->_retailers[$retailerId];
    }

    public function getCost($item, $rawCost = null)
    {
        if (is_null($rawCost)){
            $rawCost = $item['cost'];
        }

        $retailer = $this->getRetailerById($item['retailer_id']);
        return  $rawCost * $retailer->getRate();
    }

    public function getPrice($item, $rawPrice = null)
    {
        if (is_null($rawPrice)){
            $rawPrice = $item['price'];
        }

        $retailer = $this->getRetailerById($item['retailer_id']);
        return  $rawPrice * $retailer->getRate();
    }



    public function getPriceWithDiscount($price, $marginRatio = 1, &$table = null)
    {
        return ceil($price / (100+$this->getDiscount($price, $table) / $marginRatio) * 100 * $this->getMarginRatio());
    }

    public function getPriceWithMargin($cost, $marginRatio = 1, &$table = null)
    {
        return ceil($cost * ($this->getMargin($cost, $table) * $marginRatio + 100) / 100 * $this->getMarginRatio());
    }

    public function _getFinalPrice($cost, $price, $marginRatio = 1,  &$discountTable = null, &$marginTable = null)
    {
        return max($this->getPriceWithMargin($cost, $marginRatio, $marginTable), $this->getPriceWithDiscount($price, $marginRatio, $discountTable));
    }

    public function getFinalPrice($productPrice, &$discountTable = null, &$marginTable = null)
    {
        $retailer = $this->getRetailerById($productPrice['retailer_id']);
        $cost = $this->getCost($productPrice) + $retailer['fixed_fee'];
        $priceWithMargin = $this->getPriceWithMargin($cost,
            $retailer['margin_ratio'] ? $retailer['margin_ratio'] : 1,
            $marginTable);
        if( isset($productPrice['min_discounted_price_retailer_id'])
            && $productPrice['min_discounted_price_retailer_id'] != $productPrice['retailer_id']
        ) {
            $retailer = $this->getRetailerById($productPrice['min_discounted_price_retailer_id']);
        }

        $priceWithDiscount = $this->getPriceWithDiscount(
            isset($productPrice['min_retailer_price'])
                ? $productPrice['min_retailer_price']
                : $this->getPrice($productPrice),
            $retailer['margin_ratio'] ? $retailer['margin_ratio'] : 1,
            $discountTable);

        return max($priceWithMargin, $priceWithDiscount);
    }

    public function getDiscount($price, &$table = null)
    {
        if (is_null($table)){
            $table = $this->getDiscountTable();
        }
        return $this->getPartiallyLinear($price, $table);
    }

    public function getMargin($cost, &$table = null)
    {
        if (is_null($table)){
            $table = $this->getMarginTable();
        }
        return $this->getPartiallyLinear($cost, $table);
    }

    /**
     * Возвращает дисконт для данной цены.
     * @param float $value - цена товара
     * @param array $table - массив дисконта для группы, по умолчанию - розница.
     * @return type
     */
    public function getPartiallyLinear($value, &$table)
    {
        krsort($table);
        reset($table);

        $currentValue = current($table);
        while ($currentValue !== false){
            $lowerLimit = key($table);
            if ($value > $lowerLimit){
                $prevValue = prev($table);
                $prevLowerLimit = key($table);
                next($table);
                if ($prevValue !== false){
                    $currentValue = round(($currentValue + ($value-$lowerLimit)*($prevValue-$currentValue)
                        / ($prevLowerLimit-$lowerLimit)),2);
                }
                break;
            }
            $currentValue = next($table);
        }
        return $currentValue !== false ? $currentValue : 0;
    }

    public function getDiscountTable($customerGroupId = self::DEFAULT_CUSTOMER_GROUP_ID)
    {
        return $this->_discountTable[$customerGroupId];
    }

    public function getMarginTable($customerGroupId = self::DEFAULT_CUSTOMER_GROUP_ID)
    {
        return $this->_marginTable[$customerGroupId];
    }

    public function arrayToHash($array, $key = null)
    {
        $key = is_null($key) ? 'lower_limit' : $key;
        $hash = array();
        foreach ($array as $value){
            $hash[$value[$key]] = $value['value'];
        }
        arsort($hash);
        return $hash;
    }

    public function getSignificantDeviationPercent($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SIGNIFICANT_DEVIATION_PERCENT, $storeId);

    }
}


