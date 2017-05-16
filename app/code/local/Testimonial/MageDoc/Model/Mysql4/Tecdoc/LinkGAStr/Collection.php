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
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_LinkGAStr_Collection extends Testimonial_MageDoc_Model_Mysql4_Collection_Abstract
{

    protected function _construct() 
    {
        $this->_init('magedoc/tecdoc_linkGAStr');
    }   
    
    public function getLinkGAStrByTypeAndGroup($typeId, $strId = 'td_str_id', $limit = 0)
    {
        $this->getSelect()
                ->joinInner(array('td_linkLaTyp' => $this->getTable('magedoc/tecdoc_linkLaTyp')),
                    "td_linkLaTyp.LAT_TYP_ID = {$typeId} AND td_linkLaTyp.LAT_GA_ID = main_table.LGS_GA_ID",
                            array())
                ->joinInner(array('td_linkArt' => $this->getTable('magedoc/tecdoc_linkArt')),
                        "td_linkArt.LA_ID = td_linkLaTyp.LAT_LA_ID", array())
                ->where("main_table.LGS_STR_ID = {$strId}")
                ->limit($limit);
        return $this;
    }
    
    
}


