<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Articles
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Mysql4_Tecdoc_Manufacturer extends Testimonial_MageDoc_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_manufacturer', 'mfa_id');
    }

    protected function _prepareFullSelect($select, $mainTableAlias = null)
    {
        if (is_null($mainTableAlias)){
            $mainTableAlias = $this->getMainTable();
        }

        $select
            ->joinLeft(array('md_manufacturer' => $this->getTable('magedoc/manufacturer')),
            "$mainTableAlias.MFA_ID = md_manufacturer.td_mfa_id",
            array(
                'td_mfa_id'   => new Zend_Db_Expr("IFNULL(md_manufacturer.td_mfa_id, {$mainTableAlias}.MFA_ID)"),
                'enabled'     => new Zend_Db_Expr('IFNULL(md_manufacturer.enabled, 1)'),
                'title'       => new Zend_Db_Expr("IFNULL(md_manufacturer.title, {$mainTableAlias}.MFA_BRAND)"),
                'meta_title'  => new Zend_Db_Expr("IFNULL(md_manufacturer.title, {$mainTableAlias}.MFA_BRAND)"),
                'name'        => new Zend_Db_Expr("IFNULL(md_manufacturer.name, $mainTableAlias.MFA_BRAND)"),
                'logo',
                'description',
                'url_key',
                'url_path'
            ));

        return $select;
    }
}

