<?php

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Type_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_type');
    }
 
    public function joinTypeEngine( $joinTableAlias = 'main_table', 
            $engineTableAlias = 'td_engine', $collection = null, $columns = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        if(is_null($columns)){
            $columns = array('ENG_CODE' => new Zend_Db_Expr("GROUP_CONCAT({$engineTableAlias}.ENG_CODE SEPARATOR ', ')"));
        }
        $collection->getSelect()
            ->joinLeft(array('td_linkTypEng' => $this->getTable('magedoc/tecdoc_linkTypEng')), "td_linkTypEng.LTE_TYP_ID = {$joinTableAlias}.TYP_ID")
            ->joinLeft(array($engineTableAlias => $this->getTable('magedoc/tecdoc_engine')), "{$engineTableAlias}.ENG_ID = td_linkTypEng.LTE_ENG_ID",
                $columns);

        return $this;
    }
    
    public function addTypeDesignation()
    {
        $this->joinTypeDesignation()->getSelect()
            ->order(array('TYP_ENGINE_DES_TEXT', 'TYP_CDS_TEXT', 'TYP_PCON_START DESC', 'TYP_CCM'))
            ->group('TYP_ID');
        return $this;
    }
    
    public function joinTypeDesignation($joinTable = 'main_table', $collection = null)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $collection->joinCountryDesignation($collection, $joinTable, 'TYP_CDS_ID', 'TYP_CDS_TEXT')
            ->joinDesignation($collection, $joinTable, 'TYP_KV_ENGINE_DES_ID', 'TYP_ENGINE_DES_TEXT')
            ->joinDesignation($collection, $joinTable, 'TYP_KV_FUEL_DES_ID', 'TYP_FUEL_DES_TEXT')
            ->joinDesignation($collection, $joinTable, 'TYP_KV_AXLE_DES_ID', 'TYP_AXLE_DES_TEXT')
            ->joinDesignation($collection, $joinTable, 'TYP_KV_MODEL_DES_ID', '', 'MODEL_DES_TEX')
            ->joinDesignation($collection, $joinTable, 'TYP_KV_BODY_DES_ID', '', 'BODY_DES_TEX');
        $collection->getSelect()->columns(array('TYP_BODY_DES_TEXT' => 
                new Zend_Db_Expr("IFNULL(BODY_DES_TEX.TEX_TEXT,
                MODEL_DES_TEX.TEX_TEXT)")));            
        return $this;   
    }

    
    public function joinTypes($collection = null, $artId, $joinTypeDesignation = true)
    {
        if(is_null($collection)){
            $collection = $this;
        }
        if (!$artId){
            $collection->setIsLoaded(true);
            return $this;
        }
        $storeId = Mage::app()->getStore()->getId();
        $startYear = Mage::helper('magedoc')->getProductionStartYear($storeId) . '00';

        $collection->getSelect()
                ->joinInner(array('td_linkLaTyp' => $this->getTable('magedoc/tecdoc_linkLaTyp')), 'LAT_LA_ID = LA_ID', '')
                ->joinInner(array('td_type' => $this->getTable('magedoc/tecdoc_type')), 'TYP_ID = LAT_TYP_ID', 
                        array('TYP_ID', 'TYP_PCON_START', 'TYP_PCON_END', 'TYP_CCM', 'TYP_KW_FROM', 'TYP_KW_UPTO', 'TYP_HP_FROM', 'TYP_HP_UPTO', 'TYP_CYLINDERS', 'TYP_MAX_WEIGHT'))
                ->joinInner(array('td_model' => $this->getTable('magedoc/tecdoc_model')), 'MOD_ID = TYP_MOD_ID')
                ->joinInner(array('td_manufacturer' => $this->getTable('magedoc/tecdoc_manufacturer')), 'MFA_ID = MOD_MFA_ID', array('MFA_ID', 'MFA_BRAND'));
        $collection->joinCountryDesignation($collection, 'td_model', 'MOD_CDS_ID', 'MOD_CDS_TEXT');
        if ($joinTypeDesignation){
            $this->joinTypeDesignation('td_type', $collection);
            $orderBy = array('MFA_BRAND', 'MOD_CDS_TEXT', 'TYP_CDS_TEXT', 'TYP_PCON_START', 'TYP_CCM');
        } else {
            $orderBy = array('MFA_BRAND', 'MOD_CDS_TEXT', 'TYP_PCON_START', 'TYP_CCM');
        }
        $collection->getSelect()
                ->where("LA_ART_ID = {$artId} 
                            AND TYP_CCM < 6000
                            AND TYP_PCON_START > {$startYear}")
           ->order($orderBy)
           ->group('TYP_ID');            
        $collection->setIdFieldName('typ_id');        
        return $this;        
    }

    public function joinModels()
    {
        $this->getSelect()
            ->joinInner(array('td_model' => $this->getTable('magedoc/tecdoc_model')), 'MOD_ID = TYP_MOD_ID');
        $this->joinCountryDesignation($this, 'td_model', 'MOD_CDS_ID', 'MOD_CDS_TEXT');
    }

    public function getVehicleByType($typeId)
    {
        $this->getSelect()->joinInner(array('td_model' => 
                $this->getTable('magedoc/tecdoc_model')),'MOD_ID = TYP_MOD_ID', array('MOD_ID', 'MOD_MFA_ID'));
        $this->joinCountryDesignation($this, 'td_model', 'MOD_CDS_ID', 'MOD_CDS_TEXT')
                ->joinTypeDesignation()
                ->joinTypeEngine()
                ->addFieldToFilter('TYP_ID', $typeId);
        return $this;
    }
    
    public function addModelFilter($modelId)
    {
        if (!is_array($modelId)){
            $modelId = explode(',', $modelId);
        }
        $this->getSelect()
                ->where('TYP_MOD_ID IN (?)', $modelId);
        return $this;
    }

    public function addTypeFilter($typeId)
    {
        if (!is_array($typeId)){
            $typeId = explode(',', $typeId);
        }
        $this->getSelect()
            ->where('TYP_ID IN (?)', $typeId);
        return $this;
    }

    public function addYearFilter($year)
    {
        $year = (int)$year;
        $this->getSelect()
            ->where("(TYP_PCON_START <= {$year}12 OR TYP_PCON_START IS NULL) AND (TYP_PCON_END >= {$year}00 OR TYP_PCON_END IS NULL)");
        return $this;
    }
}

