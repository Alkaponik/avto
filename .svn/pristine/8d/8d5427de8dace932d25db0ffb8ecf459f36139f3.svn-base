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
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Criteria extends Testimonial_MageDoc_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_criteria', 'CRI_ID');
    }

    protected function _prepareFullSelect($select)
    {
        $mainTable = $this->getMainTable();

        $this->joinDesignation($select, $mainTable , 'CRI_DES_ID', 'CRI_DES_TEXT');
        $this->joinDesignation($select, $mainTable , 'CRI_DES_ID', 'CRI_DES_TEXT_ENG', null, false, 255);

        return $select;
    }
}

