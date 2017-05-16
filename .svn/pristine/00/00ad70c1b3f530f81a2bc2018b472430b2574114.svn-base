<?php

class Testimonial_MageDoc_Model_Source_Model extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_yearStart;
    protected $_manufacturerId;
    protected $_isGrouped = false;
    
    public function setYearStart($yearStart)
    {
        $this->_yearStart = $yearStart;
        return $this;
    }

    public function setIsGrouped($grouped = true)
    {
        $this->_isGrouped = $grouped;
        return $this;
    }
    
    public function getYearStart()
    {
        return sprintf("%0-6s", $this->_yearStart);
    }

    public function setManufacturerId($manufacturerId)
    {
        $this->_manufacturerId = $manufacturerId;
        return $this;
    }
    
    public function getManufacturerId()
    {
        return $this->_manufacturerId;
    }

    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            /* @var $models Testimonial_MageDoc_Model_Mysql4_Tecdoc_Model_Collection */
            $models = Mage::getResourceModel('magedoc/tecdoc_model_collection')
                ->addManufacturerFilter($this->getManufacturerId())
                ->addEnabledFilter()
                ->addDateIntervalFilter($this->getYearStart(), $this->getYearStart() + 12);

            if ($this->_isGrouped){
                $models->getSelect()->columns(array(
                    'MOD_ID' => new Zend_Db_Expr("GROUP_CONCAT(MOD_ID)")
                ));
                $models->joinCountryDesignation(null, 'main_table', 'MOD_CDS_ID', array('MOD_CDS_TEXT' => 'SUBSTRING_INDEX({{des_text}}.TEX_TEXT, \' \', 1)'));
                $columns = $models->getResource()->getLastDesignationColumns();
                $models->getSelect()->group($columns['MOD_CDS_TEXT']);
            } else {
                $models->joinDesignations()
                    ->setOrder('mod_cds_text', $this->getSortOrder());
            }
            $models->renderAll();
            while($model = $models->fetchItem()){
                $this->_collectionArray[] = array('label' => $model->getModCdsText(),
                            'value' => $model->getModId());
            }
        
        }
        
        return $this->_collectionArray;
    }
}
