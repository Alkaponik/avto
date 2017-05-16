<?php

class Testimonial_MageDoc_Model_Retailer_Data_Update_Elit 
    extends Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
{    
    protected $_item;    
    protected $_codeExpression = 'elit_base.code';

    public function _processProductResponse($response)
    {
        $item = $this->_item;
        $rawData = $this->getDataFromResponse($response);
        $productData = array();

        $cost = preg_replace("/[^\d\.]/","", $rawData['cost']);
        $price = preg_replace("/[^\d\.]/","", $rawData['prise']);
        $domesticQty = $rawData['generalQty'];
        $generalQty  = $rawData['domesticQty'];
        $qty         = $rawData['generalQty'] + $rawData['domesticQty'] + $rawData['qty'];

        $code = $item->getArtArticleNrNormalized();
        $productData[$code] = array(
            'td_art_id'             => $item->getArtId(),
            'code'                  => $code,
            'cost'                  => $cost,
            'price'                 => $price,
            'supplier_id'           => $item->getArtSupId(),
            'manufacturer'          => $rawData['manufacturer'],
            'retailer_id'           => $this->getRetailer()->getId(),
            'domestic_stock_qty'    => (int) $domesticQty,
            'general_stock_qty'     => (int) $generalQty,
            'qty'                   => (int) $qty,
            'created_at'            => $this->getConfig()->getCurrentDate(),
            'updated_at'            => $this->getConfig()->getCurrentDate()
        );
        return $productData;
    }
    
    public function getItemArticleData($code, $supplier)
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        $collection
            ->getSelect()
            ->joinInner(array('td_article_normalized' =>
            $collection->getResource()->getTable('magedoc/tecdoc_articleNormalized')),
            'main_table.ART_ID  = td_article_normalized.ARN_ART_ID',
            array())
            ->joinInner(array('elit_supplier' => $collection->getTable('magedoc/elit_supplier')),
               "elit_supplier.supplier = '{$supplier}' 
               AND elit_supplier.supplier_id = main_table.ART_SUP_ID", array())
            ->joinInner(array('elit_base' => $collection->getTable('magedoc/elit_base')),
               "elit_base.supplier = elit_supplier.supplier 
               AND elit_base.id = '{$code}'
               AND {$this->_codeNormalizedExpression} = td_article_normalized.ARN_ARTICLE_NR_NORMALIZED",
                       array());
        return $collection->fetchItem();            
    }
    
    
    public function checkResult($result)
    {
        $item = $this->getItemArticleData($result['code'], $result['supplier']);
        
        if(!$item){
            return false;
        }
        if($item->getArtSupId() == $this->_item->getArtSupId()
            && $item->getArtId() == $this->_item->getArtId()
        ){
            return true;
        }
        return false;
    }
    
    public function getDataFromResponse($response)
    {        
        $nodesArray = array();
        $cost = array();
        $prise = array();
        $manufacturer = array();
        preg_match ('/padding-top:5px;">(\d+)[^a-z].*(\d+)[^a-z].*(\d+)/i', $response, $matches);
        $nodesArray['generalQty'] = $matches[1][0];
        $nodesArray['domesticQty'] = $matches[2][0];
        $nodesArray['qty'] = $matches[3][0];

        preg_match('/id="ctl00_cphBody_ArtDetailControl_lblPrizeFinalValue" title="([0-9]?.*[0-9\S]+\,[0-9]+) .*"/m', $response, $cost);
        preg_match('/id="ctl00_cphBody_ArtDetailControl_lblPrizeFinalValue" title=".*"\>([0-9]?.*[0-9]+,[0-9]+).*/m', $response, $prise);
        preg_match('/ctl00_cphBody_ArtDetailControl_lblSupplierValue\">(\w+)/m', $response, $manufacturer);

        $nodesArray['cost'] = str_replace(',', '.', $cost[1]);
        $nodesArray['prise'] = str_replace(',', '.', $prise[1]);
        $nodesArray['manufacturer'] = $manufacturer[1];

        return $nodesArray;
    }
    
    
    public function _getSourceParam($artId)
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        $collection
            ->getSelect()
            ->joinInner(array('td_article_normalized' =>
            $collection->getResource()->getTable('magedoc/tecdoc_articleNormalized')),
            'main_table.ART_ID  = td_article_normalized.ARN_ART_ID',
            array())
            ->joinInner(array('elit_supplier' => $collection->getTable('magedoc/elit_supplier')),
               "elit_supplier.supplier_id = main_table.ART_SUP_ID", array())
            ->joinInner(array('elit_base' => $collection->getTable('magedoc/elit_base')),
               "elit_base.supplier = elit_supplier.supplier
               AND {$this->_codeNormalizedExpression} = td_article_normalized.ARN_ARTICLE_NR_NORMALIZED",
                       array('card' => 'elit_base.id'))
                ->where("main_table.ART_ID = {$artId}");
        if($this->_item = $collection->fetchItem()){
            return str_replace(' ', '+', $this->_item->getCard());
        }
        return '';
    }
       
    protected function _processAuthResponse($response)
    {
        $header = $response->getHeader('Set-cookie');
        $cookies = array();
        if($header){
            preg_match('/^\.ASPXAUTH=([^;]+);.*/', $header[6], $cookies['aspxauth']);
            preg_match('/^ASP.NET_SessionId=([^;]+);/', $header[1], $cookies['session_id']);
            if (!empty($cookies) && !empty($cookies['aspxauth']) && !empty($cookies['session_id'])){
                    $this->getRetailer()->getSessionData()->setData('aspxauth', $cookies['aspxauth'][1]);
                    $this->getRetailer()->getSessionData()->setData('session_id', $cookies['session_id'][1]);
            }
            $this->getRetailer()->save();
        }
        return $this;
    }  
    
    protected function _checkValidProductResponse($response) 
    {
        if(preg_match('/^.*login-box-content-data-input.*/im', $response) || preg_match('/(404)/', $response)){
            return false;
        }
        return true;
    }    
}