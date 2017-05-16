<?php

class Testimonial_MageDoc_Model_Source_Type_Engine extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_typeIds;

    public function setTypeIds($typeIds)
    {
        $this->_typeIds = $typeIds;

        return $this;
    }

    public function getTypeIds()
    {
        return $this->_typeIds;
    }


    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            /* @var $modelTypesCollection Testimonial_MageDoc_Model_Mysql4_Tecdoc_Type_Collection */
            $modelTypesCollection = Mage::getResourceModel('magedoc/tecdoc_type_collection');
            $modelTypesCollection->getSelect()->columns(array(
                'TYP_IDS' => new Zend_Db_Expr("GROUP_CONCAT(TYP_ID)")
            ));
            $modelTypesCollection->joinDesignation(null, 'main_table', 'TYP_KV_FUEL_DES_ID', 'TYP_FUEL_DES_TEXT')
                ->addTypeFilter($this->getTypeIds());
            $modelTypesCollection->getSelect()->group(array('TYP_KV_FUEL_DES_ID', 'TYP_LITRES'));
            $modelTypesCollection->getSelect()->order(array('TYP_LITRES'));
            $modelTypesCollection->renderAll();

            while($type = $modelTypesCollection->fetchItem()){
                $this->_collectionArray[] = array('label' => sprintf('%1.1F', $type->getTypLitres()).' '.$type->getTypFuelDesText(), 'value' => $type->getTypIds());
            }
        }

        return $this->_collectionArray;
    }
}
