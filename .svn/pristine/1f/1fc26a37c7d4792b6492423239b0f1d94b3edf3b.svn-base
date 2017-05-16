<?php

class Testimonial_MageDoc_Model_Retailer_Data_Update_Autotechnics
    extends Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
{
    public function getDataFromResponse($response)
    {
        $i = 1;
        $nodesArray = array();
        $dom = $this->getConfig()->getRequestAdapter()->loadHTML($response);
        $dom->preserveWhiteSpace = false;
        $tables = $dom->getElementsByTagName('table');
        $rows = $tables->item(0)->getElementsByTagName('tr');
        $cols = $rows->item($i)->getElementsByTagName('td');
        for($j = 0; $j < $rows->length; $j++){
            $cols = $rows->item($j)->getElementsByTagName('td');
            if($cols->length > 1){
                $nodesArray[$j] = array();
                foreach ($cols as $col) {
                    $nodesArray[$j][] = $col->nodeValue;
                }
            }
        }
        return $nodesArray;
    }

    public function _processProductResponse($response)
    {
       $rawData = $this->getDataFromResponse($response);
       $data = array();
       foreach($rawData as $item){
           $code        = substr(str_replace(' ', '', $item[1]), 2);
           $supplier    = $item[4];
           $price       = $item[5];
           $retailerId  = $this->getRetailer()->getId();
           $domesticQty = str_replace(array('<', '>'), '', $item[6]);
           $generalQty  = str_replace(array('<', '>'), '', $item[7]);
           $currentDate = $this->getConfig()->getCurrentDate();

           $articleData = $this->getItemArticleData($code, $supplier);
           if($articleData){
               $artId = $articleData->getData('art_id');
               $importRetailerDataData = $this->getItemImportRetailerData($artId);
               if (empty($importRetailerDataData)){
                   $manufacturer = $supplier;
               } else {
                   $manufacturer = $importRetailerDataData->getManufacturer();
               }
               $cost = $price - (100 - $articleData->getDiscountPrecent()) / 100;
               $supplierId = $articleData->getArtSupId();
               $data[$code] = array(
                    'td_art_id'     => $artId,
                    'code'          => $code,
                    'cost'          => $cost,
                    'price'         => $price,
                    'supplier_id'   => $supplierId,
                    'retailer_id'   => $retailerId,
                    'manufacturer'  => $manufacturer,
                    'domestic_stock_qty' => (int) $domesticQty,
                    'general_stock_qty' => (int) $generalQty,
                    'qty'           => (int) ($domesticQty + $generalQty),
                    'created_at'    => $currentDate,
                    'updated_at'    => $currentDate
               );
           }
       }
       return $data;
    }

    public function getArticleCollection()
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        $collection->getSelect()
            ->joinInner(array('td_article_normalized' =>
            $collection->getResource()->getTable('magedoc/tecdoc_articleNormalized')),
            'main_table.ART_ID  = td_article_normalized.ARN_ART_ID',
            array())
            ->joinInner(array('at_supplier' => $collection->getTable('magedoc/autotechnics_supplier')),
                "at_supplier.supplier_id = main_table.ART_SUP_ID",
                array('supplier_alias'))
            ->joinInner(array('at_discount' => $collection->getTable('magedoc/autotechnics_discount')),
                "at_discount.supplier = at_supplier.supplier",
                array('discount_precent'));
        return $collection;
    }

    public function getItemArticleData($code, $supplier)
    {
        $code =  Mage::helper('magedoc')->normalizeCode($code);
        $collection = $this->getArticleCollection()
            ->addFieldToFilter('td_article_normalized.ARN_ARTICLE_NR_NORMALIZED',
                    array('eq' => $code))
            ->addFieldToFilter('at_supplier.supplier',
                    array('eq' => $supplier));
        return $collection->fetchItem();
    }


    public function _getSourceParam($artId)
    {
        $stripSpacesSuppliers = array(4, 19, 30, 40, 254, 1156);
        $collection = $this->getArticleCollection()
            ->addFieldToFilter('main_table.ART_ID', array('eq' => $artId));

        $item = $collection->fetchItem();
        if($item){
            if (in_array($item->getArtSupId(), $stripSpacesSuppliers)){
                return $item->getSupplierAlias() . '+' . $item->getArtArticleNrNormalized();
            }else{
                return $item->getSupplierAlias() . '+' . $item->getArtArticleNr();
            }
        }
        return '';
    }

    protected function _processAuthResponse($response)
    {
        $header = $response->getHeader('set-cookie');
        if($header){
            preg_match('/^PHPSESSID=([^;]+);.*/', $header, $cookie);
            if (!empty($cookie) && isset($cookie[1])){
                $this->getRetailer()->getSessionData()->setData('session_id', $cookie[1]);
                $this->getRetailer()->save();
            }
            $this->getConfig()->getRequestAdapter()->check();
        }
        return $this;
    }

    protected function _checkValidProductResponse($response)
    {
        if(!$response){
            return false;
        }
        return true;
    }
}