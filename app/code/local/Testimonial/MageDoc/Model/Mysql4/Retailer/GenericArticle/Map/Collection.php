<?php
class Testimonial_MageDoc_Model_Mysql4_Retailer_GenericArticle_Map_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/retailer_genericArticle_map');
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        $groupPart = $countSelect->getPart(Zend_Db_Select::GROUP);
        if (count($groupPart)){
            $countSelect->reset(Zend_Db_Select::GROUP);
            $idFieldName = $this->getIdFieldName()
                ? $this->getIdFieldName()
                : $this->getResource()->getIdFieldName();
            $countSelect->columns("COUNT(DISTINCT {$idFieldName})");
        }else{
            $countSelect->columns('COUNT(*)');
        }

        return $countSelect;
    }
}