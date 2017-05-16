<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_LinkArt_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_linkArt');
    }   
    
    public function getTypesByArtId($artId)
    {
        $this->getSelect()
                ->joinInner(array('td_linkLaTyp' => $this->getTable('magedoc/tecdoc_linkLaTyp')), 
                        'LAT_LA_ID = LA_ID', array('type_ids' => new Zend_Db_Expr('GROUP_CONCAT(LAT_TYP_ID SEPARATOR ",")')))
                ->where("LA_ART_ID = {$artId}")
                ->group('main_table.LA_ART_ID');
                
        return $this;
    }

    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $this->processItemAfterLoad($item);
        }
        return $this;
    }

    public function processItemAfterLoad($item)
    {
        
        $typeIds = $item->getTypeIds();
        if(strlen($typeIds)) {
            $typeIds = array_unique(explode(',', $typeIds));
        }else {
            $typeIds = array();
        }
        $item->setTypeIds($typeIds);
        return $this;
    }
    
    public function joinLinks($collection = null,$joinAlias = 'main_table')
    {
        if(is_null($collection)){
            $collection = $this;
        }
                        
        return $collection;
                
    }
    
}


