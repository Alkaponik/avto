<?php

class Testimonial_MageDoc_Model_Source_Type_Supplier extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_typeId;
    protected $_strId;
    
    public function getCollectionArray()
    {
        if(empty($this->_collectionArray)) {
            $collection = Mage::getResourceModel('magedoc/tecdoc_linkGAStr_collection');
            if($this->getTypeId() !== null){
                $collection->getSelect()
                    ->joinInner(array('td_linkLaTyp' => $collection->getTable('magedoc/tecdoc_linkLaTyp')),
                        "td_linkLaTyp.LAT_TYP_ID = {$this->getTypeId()} AND td_linkLaTyp.LAT_GA_ID = main_table.LGS_GA_ID",
                            array('supplier_id' => 'td_linkLaTyp.LAT_SUP_ID'))
                    ->joinInner(array('td_supplier' => $collection->getTable('magedoc/tecdoc_supplier')),
                        "td_supplier.SUP_ID = td_linkLaTyp.LAT_SUP_ID",
                                array('supplier_name' => 'td_supplier.SUP_BRAND'))
                    ->where("main_table.LGS_STR_ID = {$this->getStrId()}")
                    ->group('td_linkLaTyp.LAT_SUP_ID');
            }else{   
                $collection->getSelect()
                    ->joinInner(array('td_linkArtGA' => $collection->getTable('magedoc/tecdoc_linkArtGA')),
                        "td_linkArtGA.LAG_GA_ID = main_table.LGS_GA_ID",
                                array())
                    ->joinInner(array('td_article' => $collection->getTable('magedoc/tecdoc_article')),
                        "td_article.ART_ID = td_linkArtGA.LAG_ART_ID",
                                array())
                    ->joinInner(array('td_supplier' => $collection->getTable('magedoc/tecdoc_supplier')),
                        "td_supplier.SUP_ID = td_article.ART_SUP_ID",
                                array('supplier_id' => 'td_supplier.SUP_ID'
                                    ,'supplier_name' => 'td_supplier.SUP_BRAND'))
                    ->where("main_table.LGS_STR_ID = {$this->getStrId()}")
                    ->group('td_supplier.SUP_ID');

            }
            $collection->getSelect()->order('td_supplier.SUP_BRAND ASC');

            while ($item = $collection->fetchItem()){
                $this->_collectionArray[] = array('label' => $item->getSupplierName(),
                            'value' => $item->getSupplierId());
            }
        }
        return $this->_collectionArray;
    }
    
    public function getTypeId()
    {
        if(!isset($this->_typeId)){
            return null;
        }
        return $this->_typeId;
    }

    public function getStrId()
    {
        if(!isset($this->_strId)){
            return null;
        }
        return $this->_strId;
    }

    public function setStrId($strId)
    {
        $this->_strId = $strId;
        return $this;
    }

    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }
    
    
}
