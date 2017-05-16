<?php

class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Type extends Testimonial_MageDoc_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_type', 'TYP_ID');
    }   
    
    protected function _prepareFullSelect($select)
    {
        $mainTable = $this->getMainTable();
        $select
            ->joinInner(array('td_model' => 
                $this->getTable('magedoc/tecdoc_model')),"td_model.MOD_ID = {$mainTable}.TYP_MOD_ID", array('MOD_PCON_START', 'MOD_ID', 'MOD_MFA_ID'))
            ->joinInner(array('td_manufacturer' => $this->getTable('magedoc/tecdoc_manufacturer')),
                'MFA_ID = MOD_MFA_ID', array('MFA_BRAND'))
            ->joinLeft(array('md_manufacturer' => $this->getTable('magedoc/manufacturer')),
                'td_mfa_id = MOD_MFA_ID', array('manufacturer_name' => 'IFNULL(md_manufacturer.title, td_manufacturer.MFA_BRAND)'));
        $this->joinCountryDesignation($select, 'td_model', 'MOD_CDS_ID', 'MOD_CDS_TEXT')
            ->joinCountryDesignation($select, $mainTable, 'TYP_CDS_ID', 'TYP_CDS_TEXT')
            ->joinDesignation($select, $mainTable, 'TYP_KV_ENGINE_DES_ID', 'TYP_ENGINE_DES_TEXT')
            ->joinDesignation($select, $mainTable, 'TYP_KV_FUEL_DES_ID', 'TYP_FUEL_DES_TEXT')
            ->joinDesignation($select, $mainTable, 'TYP_KV_AXLE_DES_ID', 'TYP_AXLE_DES_TEXT')
            ->joinDesignation($select, $mainTable, 'TYP_KV_MODEL_DES_ID', '', 'MODEL_DES_TEX')
            ->joinDesignation($select, $mainTable, 'TYP_KV_BODY_DES_ID', '', 'BODY_DES_TEX');
        $select->columns(array('TYP_BODY_DES_TEXT' => 
                new Zend_Db_Expr("IFNULL(BODY_DES_TEX.TEX_TEXT,
                MODEL_DES_TEX.TEX_TEXT)")))
            ->joinLeft(array('td_linkTypEng' => $this->getTable('magedoc/tecdoc_linkTypEng')), "td_linkTypEng.LTE_TYP_ID = {$mainTable}.TYP_ID")
            ->joinLeft(array('td_engine' => $this->getTable('magedoc/tecdoc_engine')), "td_engine.ENG_ID = td_linkTypEng.LTE_ENG_ID",
                array('ENG_CODE' => new Zend_Db_Expr("GROUP_CONCAT(td_engine.ENG_CODE SEPARATOR ', ')")))
                ->group("{$mainTable}.TYP_ID");
        return $select;
    }
    
}