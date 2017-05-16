<?php

class Testimonial_MageDoc_Model_Retailer_Data_Update_Omega
    extends Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
{
    const DISCOUNT_PERCENT = 25;
    protected $_item;
    protected $_codeExpression = 'base.code';

    public function _processProductResponse($response)
    {
       $item = $this->_item;
       $rawData = $this->_getDataFromJson($response, $item->getCard());
       $data['price'] = str_replace(',', '.', $rawData['price']);
       $productData = array(
            'td_art_id'     => $item->getArtId(),
            'code'          => $item->getArtArticleNrNormalized(),
            'cost'          =>  str_replace(',', '.', $rawData['cost']),
            'price'         => $data['price'] * ((100 + self::DISCOUNT_PERCENT) / 100),
            'supplier_id'   => $item->getArtSupId(),
            'retailer_id'   => $this->getRetailer()->getId(),
            'card'          => $rawData['card'],
            'name'          => $rawData['name'],
            'manufacturer'  => $rawData['manufacturer'],
            'domestic_stock_qty' => (int) $rawData['domestic_stock_qty'],
            'general_stock_qty' => (int) $rawData['general_stock_qty'],
            'qty' => (int) $rawData['general_stock_qty'] + (int) $rawData['domestic_stock_qty'],
            'created_at'    => $this->getConfig()->getCurrentDate(),
            'updated_at'    => $this->getConfig()->getCurrentDate()
                    );
        return $productData;
    }

    protected function _getDataFromJson($json, $code)
    {
        $data = array();
        $matches = array();
        preg_match('/serviceResponse:(\{.*)\}$/ms', $json, $matches);
        if (!empty($matches)){
            $matches[1] = str_replace('data:', '"data":', $matches[1]);
            $matches[1] = str_replace('total:', '"total":', $matches[1]);
            $matches[1] = str_replace('success:', '"success":', $matches[1]);


            $object = json_decode($matches[1],true);
            if ($object && !empty($object['success']) && isset($object['data']['data'])){
                $rawData = $object['data']['data'];
                foreach ($rawData as $item){
                    if (strpos($item['art_kart'], $code) === 0){
                        $data['code']               = $code;
                        $data['domestic_stock_qty'] = $item['art_rest_1'];
                        $data['general_stock_qty']  = $item['art_rest_2'];
                        $data['manufacturer']       = $item['bra_desc'];
                        $data['cost']               = $item['art_customer_price'];
                        $data['price']              = $item['art_price'];
                        $data['card']               = trim($item['art_kart']);
                        $data['name']               = iconv("utf-8", "windows-1251", $item['art_desc']);
                        break;
                    }
                }
            }
        }

        return $data;
    }

    public function _getSourceParam($artId)
    {
        $item = $this->getItemArticleData($artId);
        if($item){
            return $item->getCard();
        }
        return '';
    }

    public function getItemArticleData($artId)
    {
        if(!isset($this->_item)){
            $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
            $collection->getSelect()
                ->joinInner(array('td_article_normalized' =>
                $collection->getResource()->getTable('magedoc/tecdoc_articleNormalized')),
                'main_table.ART_ID  = td_article_normalized.ARN_ART_ID',
                array())
                ->joinInner(array('supplier' => $collection->getTable('magedoc/omega_supplier')),
                    "supplier.supplier_id = main_table.ART_SUP_ID")
                ->joinInner(array('base' => $collection->getTable('magedoc/omega_base')),
                    "{$this->_codeNormalizedExpression} = td_article_normalized.ARN_ARTICLE_NR_NORMALIZED and supplier.supplier = base.supplier");
            $collection->addFieldToFilter('main_table.ART_ID', array('eq' => $artId));
            $this->_item = $collection->fetchItem();
        }
        return $this->_item;
    }

    protected function _processAuthResponse($response)
    {
        $header = $response->getHeader('set-cookie');
        if($header){
            preg_match('/\.ASPXAUTH=([^;]+);/', $header, $cookie);
            if (!empty($cookie) && isset($cookie[1])){
                $this->getRetailer()->getSessionData()->setData('aspxauth', $cookie[1]);
            }
            $checkResult = $this->getConfig()->getRequestAdapter()->check();
            preg_match('/ASP\.NET_SessionId=([^;]+);/', $checkResult, $sessionId);
            if (!empty($sessionId[0])){
                $this->getRetailer()->getSessionData()->setData('session_id', $sessionId[1]);
            }
            $this->getRetailer()->save();
        }
        return $this;
    }

    protected function _checkValidProductResponse($response)
    {
        if(preg_match('/^.*LoginUser_UserName.*/im', $response) || preg_match('/^.*Account\/Login.aspx/im', $response)){
            // Ask for the login page to parse the value of the form authorization (__EVENTVALIDATION / __VIEWSTATE)
            $checkResult = $this->getConfig()->getRequestAdapter()->check();
            $this->_getLoginFormData($checkResult);
            return false;
        }
        return true;
    }

    protected function _getLoginFormData($result) {
        if (preg_match('/id="__VIEWSTATE" value="([^"]+)".*id="__EVENTVALIDATION" value="([^"]+)"/sm', $result, $matches)){
            $this->getConfig()->setViewState(urlencode($matches[1]));
            $this->getConfig()->setEventValidation(urlencode($matches[2]));
        }else{
            return false;
        }
    }
}