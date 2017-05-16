<?php

class Testimonial_MageDoc_Model_Retailer_Data_Update_Trost 
    extends Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
{    
    const DOMESTIC_LOCATION_NAME = 'Харьков';
    const GENERAL_LOCATION_NAME = 'Киев';
    protected $_item;
    
    public function getDataFromResponse($response)
    {
        if(!preg_match('/.*Out of stock.*/mi', $response)){
            $i = 1;        
            $nodesArray = array();           
            $dom = $this->getConfig()->getRequestAdapter()->loadHTML($response);
            $dom->preserveWhiteSpace = false;
            $tables = $dom->getElementsByTagName('table');
            $rows = $tables->item(1)->getElementsByTagName('tr');
            $cols = $rows->item($i)->getElementsByTagName('td');  
            for($j = 0; $j < $rows->length; $j++){
                $cols = $rows->item($j)->getElementsByTagName('td');
                if($cols->length > 1){
                    $nodesArray[$j] = array();
                    foreach ($cols as $col) {
                        $nodesArray[$j][] =  utf8_decode($col->nodeValue);
                    }
                }
            }
        }
        if(preg_match_all('([0-9\S]+\,[0-9]+)', $response, $prices)){
            if(isset($prices[0])){
                $nodesArray['price'] = str_replace(' ', '', $prices[0][0]);
                $nodesArray['cost'] = str_replace(' ', '', $prices[0][1]);
            }
        }
        return $nodesArray;
    }

    public function getItemArticleData($artId)
    {
        if(!isset($this->_item)){
            $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
            $collection->getSelect()
                ->joinInner(array('trost_supplier' => $collection->getTable('magedoc/trost_supplier')),
                        "trost_supplier.supplier_id = main_table.ART_SUP_ID");
            $collection->addFieldToFilter('main_table.ART_ID', array('eq' => $artId));
            $this->_item = $collection->fetchItem();
        }
        return $this->_item;        
    }
    
    public function _getSourceParam($artId)
    {
        $item = $this->getItemArticleData($artId);
        if($item){
            return array($item->getAlias(), $item->getArtArticleNrNormalized());
        }
        return '';
    }
    
    public function _processProductResponse($response)
    {        
        $rawData = $this->getDataFromResponse($response);
        $domesticQty = 0;
        $generalQty  = 0;
        $qty         = 0;
        foreach($rawData as $rest){
            if(isset($rest[0]) && isset($rest[1])){
                if(strpos($rest[0], self::DOMESTIC_LOCATION_NAME) !== false){
                    $domesticQty += $rest[1];
                }
                if(strpos($rest[0], self::GENERAL_LOCATION_NAME) !== false){
                    $generalQty += $rest[1];
                }
                $qty += $rest[1];
            }
        }
        $artId = $this->_item->getArtId();
        $cost = $rawData['cost'];
        $price = $rawData['price'];
        $supplierId = $this->_item->getArtSupId();
        $code = $this->_item->getArtArticleNrNormalized();  
        $currentDate = $this->getConfig()->getCurrentDate();
        $data[$code] = array(
            'td_art_id'             => $artId,
            'code'                  => $code,
            'cost'                  => $cost,
            'price'                 => $price,
            'supplier_id'           => $supplierId,
            'retailer_id'           => $this->getRetailer()->getId(),
            'manufacturer'          => $this->_item->getSupplier(),
            'domestic_stock_qty'    => (int) $domesticQty,
            'general_stock_qty'     => (int) $generalQty,
            'qty'                   => (int) $qty,
            'created_at'            => $currentDate,
            'updated_at'            => $currentDate
        );
       return $data;
    }
    
    protected function _processAuthResponse($response)
    {
        $header = $response->getHeader('set-cookie');
        if($header){
            preg_match('/^ASP.NET_SessionId=([^;]+);.*/', $header, $cookie);
            if (!empty($cookie) && isset($cookie[1])){
                $this->getRetailer()->getSessionData()->setData('session_id', $cookie[1]);
                $this->getRetailer()->save();
            }
        }       
        return $this;
    }  
    
    protected function _checkValidProductResponse($response) 
    {
        if(!$response || strpos($response, 'error') !== false){    
            return false;
        }
        return true;
    }      
}