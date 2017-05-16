<?php

class Testimonial_MageDoc_Model_Source_Manufacturer extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_enabledOnly = false;
    protected $_addTitles = true;
    protected $_valueField = 'mfa_id';
    protected $_labelField = 'mfa_brand';
    protected $_year = null;
    
    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            if(!$this->_year){
                /* @var $items Testimonial_MageDoc_Model_Mysql4_Tecdoc_Manufacturer_Collection */
                $items = Mage::getResourceModel('magedoc/tecdoc_manufacturer_collection');
            } else {
                /* @var $items Testimonial_MageDoc_Model_Mysql4_Tecdoc_Model_Collection */
                $items = Mage::getResourceModel('magedoc/tecdoc_model_collection');
                $items->getSelect()->group('MOD_MFA_ID');
            }

            $valueField = $this->_valueField;
            $labelField = $this->_labelField;
            if ($this->_addTitles){
                $items->joinManufacturers();
                $valueField = $this->_year
                    ? 'mod_mfa_id'
                    : 'td_mfa_id';
                $labelField = $this->_year
                    ? 'mfa_brand'
                    : 'title';
            }
            if($this->_enabledOnly){
                $items->addEnabledFilter(true);
            }
            if($this->_year){
                $items->addYearFilter($this->_year);
            }
            $items->setOrder($labelField, $this->getSortOrder());
            $items->renderAll();
            while($item = $items->fetchItem()){
                $this->_collectionArray[] = array(
                    'value' => $item->getData($valueField),
                    'label' => $item->getData($labelField),
                );
            }
        }

        return $this->_collectionArray;
    }
    
    public function setEnabledFilter($enabled)
    {
        $this->_enabledOnly = (bool)$enabled;
        return $this;
    }

    public function setYearFilter($year)
    {
        $this->_year = $year;
        return $this;
    }

    public function addTitles($addTitles = true)
    {
        $this->_addTitles = $addTitles;

        return $this;
    }
    
}
