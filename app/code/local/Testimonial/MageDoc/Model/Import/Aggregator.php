<?php

class Testimonial_MageDoc_Model_Import_Aggregator extends Testimonial_MageDoc_Model_Import_Abstract
{
    const IMPORT_ENABLED = 1;
    const IMPORT_DISABLED = 0;
    const AUTOPRICING_ENABLED = 1;
    const AUTOPRICING_DISABLED = 0;

    protected $_aggregateConditionsMap;

    protected function _initExpressions()
    {
        $this->_aggregateConditionsMap = array(
            self::IMPORT_ENABLED  . self::AUTOPRICING_ENABLED  . Testimonial_MageDoc_Model_Source_Stock_Status::IN_STOCK,
            self::IMPORT_ENABLED  . self::AUTOPRICING_ENABLED  . Testimonial_MageDoc_Model_Source_Stock_Status::AVAILABLE_FOR_PURCHASE,
            self::IMPORT_ENABLED  . self::AUTOPRICING_DISABLED . Testimonial_MageDoc_Model_Source_Stock_Status::IN_STOCK,
            self::IMPORT_ENABLED  . self::AUTOPRICING_DISABLED . Testimonial_MageDoc_Model_Source_Stock_Status::AVAILABLE_FOR_PURCHASE,
            self::IMPORT_ENABLED  . self::AUTOPRICING_ENABLED  . Testimonial_MageDoc_Model_Source_Stock_Status::OUT_OF_STOCK,
            self::IMPORT_ENABLED  . self::AUTOPRICING_DISABLED . Testimonial_MageDoc_Model_Source_Stock_Status::OUT_OF_STOCK,
            self::IMPORT_DISABLED . self::AUTOPRICING_DISABLED . Testimonial_MageDoc_Model_Source_Stock_Status::OUT_OF_STOCK,
        );
    }

    protected function _construct()
    {
        $this->_initExpressions();
        return parent::_construct();
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $this->addAggregatedColumnsToCollection($this->_collection);
        $this->_collection->getSelect()
               ->group(array('main_table.td_art_id'));

        return $this;
    }

    public function addAggregatedColumnsToCollection($collection)
    {
        $collection->getSelect()->columns(new Zend_Db_Expr('(GROUP_CONCAT(main_table.cost SEPARATOR ", ")) as aggregated_cost'))
            ->columns(new Zend_Db_Expr('(GROUP_CONCAT(main_table.price SEPARATOR ", ")) as aggregated_price'))
            ->columns(new Zend_Db_Expr('(GROUP_CONCAT(main_table.qty SEPARATOR ", ")) as aggregated_qty'))
            ->columns(new Zend_Db_Expr('(GROUP_CONCAT(main_table.retailer_id SEPARATOR ", ")) as aggregated_retailer_id'))
            ->columns(new Zend_Db_Expr('(GROUP_CONCAT(main_table.data_id SEPARATOR ", ")) as aggregated_data_id'));
        return $collection;
    }

    public function getAdditionalData($rawData)
    {
        /** @var $helper Testimonial_MageDoc_Helper_Price */
        $helper = Mage::helper('magedoc/price');

        $costArray = $origCostArray = explode(', ', $rawData['aggregated_cost']);
        $retailerArray = explode(', ', $rawData['aggregated_retailer_id']);
        $priceArray = $origPriceArray = (explode(',', $rawData['aggregated_price']));
        $qtyArray = explode(', ', $rawData['aggregated_qty']);
        $dataIdsArray = explode(', ', $rawData->getData('aggregated_data_id'));

        /**
         * Trim aggregated data arrays to min length
         * to handle strings which exceed MySql group_concat_max_len
         */
        $minAggregatedDataCount = min(
            count($costArray),
            count($retailerArray),
            count($priceArray),
            count($qtyArray),
            count($dataIdsArray));

        $costArray = array_slice($costArray, 0, $minAggregatedDataCount);
        $retailerArray = array_slice($retailerArray, 0, $minAggregatedDataCount);
        $priceArray = array_slice($priceArray, 0, $minAggregatedDataCount);
        $qtyArray = array_slice($qtyArray, 0, $minAggregatedDataCount);
        $dataIdsArray = array_slice($dataIdsArray, 0, $minAggregatedDataCount);

        $minDiscountedPriceRetailerIndex = null;
        $minMarginedCostRetailerIndex = null;
        $productQty = 0;

        end( $this->_aggregateConditionsMap );
        $conditionIndex = key($this->_aggregateConditionsMap);

        $discountedPriceArray = array();
        $marginedCostArray = array();

        $stockStatuses = array();
        for($i = 0; $i < count($priceArray); $i++) {
            $retailer = $helper->getRetailerById($retailerArray[$i]);
            $priceArray[$i] *= $retailer['rate'];
            $discountedPriceArray[$i] = $retailer->getPriceWithDiscount($priceArray[$i]);
            $costArray[$i] *= $retailer['rate'];
            $costArray[$i] += $retailer['fixed_fee'];
            $marginedCostArray[$i] = $retailer->getPriceWithMargin($costArray[$i]);

            $stockStatuses[$i] = $qtyArray[$i] > 0
                && $discountedPriceArray[$i] > 0
                && $marginedCostArray[$i] > 0
                ? $retailer['stock_status'] : Testimonial_MageDoc_Model_Source_Stock_Status::OUT_OF_STOCK;
            $currentConditionStatus = $retailer['is_import_enabled'] . $retailer['use_for_autopricing'] . $stockStatuses[$i];

            $currentConditionStatusIndex = array_search($currentConditionStatus, $this->_aggregateConditionsMap);
            if( $currentConditionStatusIndex !== false && $conditionIndex > $currentConditionStatusIndex) {
                $conditionIndex = $currentConditionStatusIndex;
            }
        }

        if(self::IMPORT_ENABLED == $this->_aggregateConditionsMap[$conditionIndex][0]) {
            $isImportEnabled = $this->_aggregateConditionsMap[$conditionIndex][0];
            $useForAutopricing = $this->_aggregateConditionsMap[$conditionIndex][1];
            $stockStatus = $this->_aggregateConditionsMap[$conditionIndex][2];
        } else {
            return array(
                'is_imported'   => $rawData->getData('catalog_product_id') !== null
                    ? 1 : 0,
                'retailer_disable_autopricing' => true
            );
        }

        for($i = 0; $i < count($priceArray); $i++) {
            $retailer = $helper->getRetailerById($retailerArray[$i]);
            if( $retailer['is_import_enabled'] != $isImportEnabled
                || $retailer['use_for_autopricing'] != $useForAutopricing
                || $stockStatuses[$i] != $stockStatus
            ) {
                continue;
            }

            if (is_null($minDiscountedPriceRetailerIndex)) {
                $minDiscountedPriceRetailerIndex = $i;
                $minMarginedCostRetailerIndex = $i;
            }

            if( $discountedPriceArray[$i] < $discountedPriceArray[$minDiscountedPriceRetailerIndex] ) {
                $minDiscountedPriceRetailerIndex = $i;
            }
            if( $marginedCostArray[$i] < $marginedCostArray[$minMarginedCostRetailerIndex] ) {
                $minMarginedCostRetailerIndex = $i;
            }

            if($stockStatuses[$i] != Testimonial_MageDoc_Model_Source_Stock_Status::OUT_OF_STOCK) {
                $productQty += $qtyArray[$i];
            }
        }

        $marginRatio = $helper->getRetailerById($retailerArray[$minDiscountedPriceRetailerIndex])->getData('margin_ratio');

        $retailer = $helper->getRetailerById($retailerArray[$minMarginedCostRetailerIndex]);
        $discountTable = $retailer->getDiscountTable();
        $marginTable = $retailer->getMarginTable();

        $minDiscountedPriceRetailer = $helper->getRetailerById($retailerArray[$minDiscountedPriceRetailerIndex]);
        $minDiscountedPriceRetailerDiscountTable = $minDiscountedPriceRetailer->getDiscountTable();

        $cost = $origCostArray[$minMarginedCostRetailerIndex];
        $price = $origPriceArray[$minMarginedCostRetailerIndex];

        $data = array(
            'cost'          => $cost,
            'price'         => $price,
            'qty'           => $productQty,
            'min_discounted_price_retailer_id' => $retailerArray[$minDiscountedPriceRetailerIndex],
            'min_retailer_price' => $priceArray[$minDiscountedPriceRetailerIndex],
            'min_retailer_price_discount' => $helper->getDiscount($priceArray[$minDiscountedPriceRetailerIndex], $minDiscountedPriceRetailerDiscountTable) / $marginRatio,
            'discount'      => $helper->getDiscount($priceArray[$minDiscountedPriceRetailerIndex], $minDiscountedPriceRetailerDiscountTable) / $marginRatio,
            'retailer_id'   => $retailerArray[$minMarginedCostRetailerIndex],
            'is_imported'   => $rawData->getData('catalog_product_id') !== null
                ? 1 : 0,
            'data_id'       => $dataIdsArray,
        );

        $data['base_cost'] = $helper->getCost($data);
        $data['base_price'] = $helper->getPrice($data);
        $data['margin'] = $helper->getMargin($data['base_cost'], $marginTable) *  $retailer->getMarginRatio();
        $data['price_with_margin'] = $marginedCostArray[$minMarginedCostRetailerIndex];
        $data['price_with_discount'] = $discountedPriceArray[$minDiscountedPriceRetailerIndex];
        $data['final_price'] = $helper->getFinalPrice($data, $discountTable, $marginTable);

        return $data;
    }
    
    public function _getAdditionalData($rawData)
    {   
        $helper = Mage::helper('magedoc/price');
        if(count(explode(', ', $rawData->getData('aggregated_cost'))) == 1){          
            return parent::getAdditionalData($rawData);
        }
        $costArray = $origCostArray = explode(', ', $rawData->getData('aggregated_cost'));
        $retailerArray = explode(', ', $rawData->getData('aggregated_retailer_id'));        
        $priceArray = $origPriceArray = explode(', ', $rawData->getData('aggregated_price'));
        $arrayDataIds = explode(', ', $rawData->getData('aggregated_data_id'));
        $minPriceRetailerIndex = null;
        $minCostRetailerIndex = null;
        $disabledAll = $this->isAllRetailersAutopricingDisabled($retailerArray);
        /**
         * Determining min price/cost indices for is stock and out of stock offers separately
         */
        for($i = 0; $i < count($priceArray); $i++){
            $retailer = $helper->getRetailerById($retailerArray[$i]);
            if($retailer->getUseForAutopricing() == 0 && !$disabledAll){
                continue;
            }
            $priceArray[$i] *= $retailer->getRate();
            $costArray[$i] *= $retailer->getRate();
            $costArray[$i] += $retailer->getFixedFee();
            if (is_null($minPriceRetailerIndex)){
                $minPriceRetailerIndex = $i;
                $minCostRetailerIndex = $i;
            }
            if ($priceArray[$i] < $priceArray[$minPriceRetailerIndex]){
                $minPriceRetailerIndex = $i;
            }
            if ($costArray[$i] < $costArray[$minCostRetailerIndex]){
                $minCostRetailerIndex = $i;
            }
        }
        $retailer = $helper->getRetailerById($retailerArray[$minCostRetailerIndex]);
        $cost = $origCostArray[$minCostRetailerIndex] * $retailer->getRate() + $retailer->getFixedFee();
        $price = $origPriceArray[$minPriceRetailerIndex] * $retailer->getRate();
        $data = array(
            'cost'          => $cost,
            'price'         => $price,
            'qty'           => array_sum(explode(', ', $rawData->getData('aggregated_qty'))),
            'discount'      => $helper->getDiscount($cost) / $retailer->getMarginRatio(),            
            'margin'        => $helper->getMargin($price) *  $retailer->getMarginRatio(),
            'final_price'   => $helper->getFinalPrice($cost, $price,  $retailer->getMarginRatio()),
            'retailer_id'   => $retailerArray[$minCostRetailerIndex],
            'is_imported'   => $rawData->getData('catalog_product_id') !== null 
                                                ? 1 : 0,   
            'data_id'       => $arrayDataIds[$minCostRetailerIndex],
            'retailer_disable_autopricing'   => $disabledAll,

        );               
        return $data;
    }    
    
    
    public function isAllRetailersAutopricingDisabled($retailers)
    {
        return false;
        foreach($retailers as $retailer){
            $retailer = Mage::helper('magedoc/price')->getRetailerById($retailer);
            if($retailer->getUseForAutopricing() != 0){
                return false;
            }
        }
        return true;
    }
}
