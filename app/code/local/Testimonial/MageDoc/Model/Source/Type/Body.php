<?php

class Testimonial_MageDoc_Model_Source_Type_Body extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_modelIds;
    protected $_productionYear = null;

    public function setModelIds($modelIds)
    {
        $this->_modelIds = $modelIds;

        return $this;
    }

    public function setProductionYear($year)
    {
        $this->_productionYear = $year;

        return $this;
    }

    public function getModelIds()
    {
        return $this->_modelIds;
    }


    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            /* @var $modelTypesCollection Testimonial_MageDoc_Model_Mysql4_Tecdoc_Type_Collection */
            $modelTypesCollection = Mage::getResourceModel('magedoc/tecdoc_type_collection')
                //->joinTypeDesignation()
                ->addModelFilter($this->getModelIds());
            if (!is_null($this->_productionYear)){
                $modelTypesCollection->addYearFilter($this->_productionYear);
            }

            $modelTypesCollection->joinDesignation(null, 'main_table', 'TYP_KV_BODY_DES_ID', '', 'BODY_DES_TEX', true);
            $modelTypesCollection->getSelect()->columns(array(
                'TYP_BODY_DES_TEXT' =>
                    new Zend_Db_Expr("IFNULL(des_text_template.text, IFNULL(BODY_DES_TEX.TEX_TEXT, 'All'))"),
                'TYP_IDS' => new Zend_Db_Expr("GROUP_CONCAT(TYP_ID)")
            ));
            $modelTypesCollection->getSelect()->group('TYP_KV_BODY_DES_ID');
            $modelTypesCollection->renderAll();

            while($type = $modelTypesCollection->fetchItem()){
                $this->_collectionArray[] = array('label' => $type->getTypBodyDesText(), 'value' => $type->getTypIds());
            }
        }

        return $this->_collectionArray;
    }
}
