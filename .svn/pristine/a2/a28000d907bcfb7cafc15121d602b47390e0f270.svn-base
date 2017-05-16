<?php
class Testimonial_MageDoc_Model_Retailer_Data_Update_Vlad
    extends Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
{
    const MAX_PRODUCT_QTY = 10;
    public function _getSourceParam($artId)
    {
        $collection = Mage::getResourceModel('magedoc/vladislav_collection');
        $collection->getSelect()
            ->joinInner(array('vlad_link' => $collection->getTable('magedoc/vladislav_tdLink')),
                "main_table.kod = vlad_link.vlad_code AND vlad_link.art_id = {$artId}");
        if($item = $collection->fetchItem()){
            return $item->getKod();
        }
        return '';
    }

    protected function _processProductResponse($response)
    {
        $rawData = $this->getDataFromResponse($response);
        $this->updateVladBase($rawData);
        $data = $this->getItemsVladData(array_keys($rawData));
        $items = array();
        foreach($data as $item){
            if(!$item->getTdArtId()){
                if($artId = $this->getItemArtId($item)){
                    $item->setTdArtId($artId);
                    $this->insertLink($item->getVladCode(), $artId);
                }
            }
            $item->setCost($item->getCost() * $this->getRetailer()->getRate());
            $item->setPrice($item->getPrice() * $this->getRetailer()->getRate());
            $item->setCreatedAt($this->getConfig()->getCurrentDate());
            $item->setUpdatedAt($this->getConfig()->getCurrentDate());
            $item->setRetailerId($this->getRetailer()->getId());
            $importRetailerDataData = $this->getItemImportRetailerData($item->getTdArtId());
            $this->setManufacturer($importRetailerDataData->getManufacturer());
            $items[$item->getCode()] = $item->getData();
        }
        return $items;
    }

    public function insertLInk($code, $artId)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('magedoc/vladislav_tdLink');
        if ($artId) {
            Mage::getSingleton('core/resource')->getConnection('write')->insertOnDuplicate(
                $table,
                array('art_id' => $artId, 'vlad_code' => $code),
                array());
        }
        return $this;
    }

    public function getItemArtId($item)
    {
        $code =  Mage::helper('magedoc')->normalizeCode( $item->getCode());
        $articleCollection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        $articleCollection->getSelect()->joinInner(array('td_article_normalized' =>
            $articleCollection->getResource()->getTable('magedoc/tecdoc_articleNormalized')),
            'main_table.ART_ID  = td_article_normalized.ARN_ART_ID',
            array());
        $articleCollection->addFieldToFilter('td_article_normalized.ARN_ARTICLE_NR_NORMALIZED', array('eq' => $code));
        $articleCollection->addFieldToFilter('ART_SUP_ID', array('eq' => $item->getSupplierId()));
        return $articleCollection->fetchItem();
    }

    public function getDataFromResponse($response)
    {
        $rate = $this->getRetailer()->getRate();
        preg_match_all('/dwwatb\(([^)]*)\);/', $response, $products);
        $productArray = array();
        foreach ($products[1] as $product){
            $product = preg_replace('/(.*"[0-9]+),([0-9]+","[0-9]+),([0-9]+".*)/', '$1.$2.$3', $product);
            $product = str_replace('"', '', $product);
            $data = explode(',', $product);
//            preg_match('/^[\S]+/', $data[4], $manufacturer);
            $productArray[$data[0]] = array(
//                'manufacturer'  =>  $manufacturer[0],
                'kod'  =>  $data[0],
                'cena'  =>  str_replace(',','.',$data[6]),
                'cost'  =>  str_replace(',','.',$data[7]),
//                //Dnepr
                'kol1'  =>  $this->_parseQty($data[13]),
//                //Kharkov
                'kol2'  =>  $this->_parseQty($data[10]),
//                //Donezk
                'kol3'  =>  $this->_parseQty($data[10]),
//                //Kiev
                'kol4'  =>  $this->_parseQty($data[9]),
                );
        }
        return $productArray;
    }

    public function updateVladBase($products)
    {
        $products_arr = array();
        foreach ($products as $key => $value) {
            unset($value['cost']);
            $products_arr[$key] =$value;
        }
        $table = Mage::getResourceModel('magedoc/vladislav')->getMainTable();
        if ($products_arr) {
            Mage::getSingleton('core/resource')->getConnection('write')->insertOnDuplicate(
                $table,
                $products_arr,
                array( 'kol1', 'kol2', 'kol3', 'kol4', 'cena'));
        }
        return $this;
    }


    public function getItemsVladData($items)
    {
        $collection = Mage::getResourceModel('magedoc/vladislav_collection');
        $collection->getSelect()
            ->joinInner(array('vlad_s_gr' => $collection->getTable('magedoc/vladislav_sGr')),
                "vlad_s_gr.kodgr = main_table.kodgr AND vlad_s_gr.SUP_ID IS NOT NULL")
            ->joinInner(array('vlad_s_pgr' => $collection->getTable('magedoc/vladislav_sPgr')),
                "vlad_s_pgr.kodpgr = main_table.kodpgr")
            ->joinInner(array('vlad_td_link' => $collection->getTable('magedoc/vladislav_tdLink')),
                "vlad_td_link.vlad_code = main_table.kod")
            ->joinLeft(array('article' => Mage::getResourceSingleton('magedoc/tecdoc_article')->getTable('magedoc/tecdoc_article')),
                    "article.ART_ID = vlad_td_link.art_id",
                    array('td_art_id' => 'article.ART_ID'));
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'td_art_id' => 'vlad_td_link.art_id',
            'code' => new Zend_Db_Expr("REPLACE(SUBSTRING_INDEX(SUBSTR(
                    IF(main_table.name LIKE '% SP',
                        SUBSTR(main_table.name, 1, LENGTH(main_table.name)-3),
                        main_table.name),
                    LOCATE(' ', main_table.name)+1), ' =', 1), ' ', '')"),
            'domestic_stock_qty' => new Zend_Db_Expr("IF(main_table.kol2 ='*',
                            100, IF(main_table.kol2 = '', 0, main_table.kol2))"),
            'general_stock_qty' => new Zend_Db_Expr("IF(main_table.kol4 ='*',
                            100, IF(main_table.kol4 = '', 0, main_table.kol4))"),
            'qty' => new Zend_Db_Expr("IF(main_table.kol1 = '*'
                                        OR main_table.kol2 = '*'
                                        OR main_table.kol3 = '*'
                                        OR main_table.kol4 = '*'
                                        OR main_table.kol5 = '*' ,'100',
                                        (main_table.kol1+main_table.kol2
                                        +main_table.kol3+main_table.kol4+main_table.kol5))"),
            'cost' => new Zend_Db_Expr("IF(main_table.sale = 'T',
                        main_table.cena, (main_table.cena
                        * (100 - IFNULL(vlad_s_pgr.discount, 0)) / 100))"),
            'price' => 'main_table.cena',
            'supplier_id' => 'vlad_s_gr.SUP_ID',
            'manufacturer' => 'vlad_s_gr.name'
            ));
        $collection->addFieldToFilter('main_table.kod', array('in' => $items));
        return $collection;
    }

    protected function _processAuthResponse($response)
    {
        $header = $response->getHeader('set-cookie');
        if($header){
            preg_match('/^sid=([^;]+);.*/', $header, $cookie);
            if (!empty($cookie) && isset($cookie[1])){
                $this->getRetailer()->getSessionData()->setData('session_id', $cookie[1]);
                $this->getRetailer()->save();
            }
        }
        return $this;
    }

    protected function _checkValidProductResponse($response)
    {
        if(preg_match('/alert.*/', $response)){
            return false;
        }
        return true;
    }

    protected function _parseQty($qty)
    {
        return $qty == "есть"
            ? self::MAX_PRODUCT_QTY : (int)trim(strip_tags($qty),' ');
    }
}