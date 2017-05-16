<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Criteria
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Block_LinkArt extends Mage_Core_Block_Template
{
    protected $_relatedTypes;
    protected $_joinEngines = false;
    
    public function getRelatedTypes($artId = null)
    {
        if(!isset($this->_relatedTypes)){
            $this->_relatedTypes = Mage::getResourceModel("magedoc/tecdoc_linkArt_collection");
            $this->_relatedTypes->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $this->_initTypes($artId);
        }
        return $this->_relatedTypes;
    }

    protected function _clearTypes()
    {
        if (isset($this->_relatedTypes)){
            $this->_relatedTypes->clear();
            $this->_relatedTypes->getSelect()->reset()
                    ->from(array('main_table' => $this->_relatedTypes->getMainTable()), '');
        }
        return $this;
    }

    public function setJoinEngines($flag = false)
    {
        $this->_joinEngines = (bool)$flag;
        return $this;
    }
    
    public function isJoinEngines()
    {
        return $this->_joinEngines;
    }
    
    public function getTypeIds()
    {
        return $this->hasTypeIds()
            ? $this->getData('type_ids')
            : array_keys($this->getRelatedTypes()->getItems());
    }
    
    
    protected function _initTypes($artId = null)
    {
        if (isset($this->_relatedTypes)){
            if (is_null($artId)){
                $artId = $this->getProduct()->getTdArtId();
            }
            $typeCollection = Mage::getResourceSingleton("magedoc/tecdoc_type_collection")
                ->joinTypes($this->_relatedTypes, $artId, $this->isJoinEngines());
            if($this->isJoinEngines()){
                $typeCollection->joinTypeEngine('td_type', 'td_engine', $this->_relatedTypes);
            }
        }
        return $this;
    }

    public function setProduct($product)
    {
        $this->setData('product', $product);
        $this->_clearTypes();
        $this->_initTypes();
        return $this;
    }
    
    public function getProduct()
    {
        return $this->hasProduct()
                ? $this->getData('product')
                : Mage::registry('product');
    }
    
    public function getManufacturerList()
    {
        $manufacturers = array();
        $manufacturerName = '';
        $types = $this->getRelatedTypes();
        foreach ($types as $type){
            if($manufacturerName != $type->getMfaBrand()){
                $manufacturers[] = $manufacturerName = $type->getMfaBrand();
            }
        }
        return $manufacturers;
    }
   
}
