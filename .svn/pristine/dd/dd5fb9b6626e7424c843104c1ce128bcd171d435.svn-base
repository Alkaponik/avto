<?php
/**
 * Description of Abstract
 *
 * @author Oleg Frolov
 */
abstract class Testimonial_MageDoc_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_lastDesignationColumns = array();

    public function setKeysToLowerCase($data)
    {
        return array_change_key_case($data, CASE_LOWER);
    }
    
    public function setKeysToUpperCase($data)
    {
        return array_change_key_case($data, CASE_UPPER);
    }

    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        return $this->setKeysToUpperCase($this->_prepareDataForTable($object, $this->getMainTable()));
    }
    
    
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
       
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }
        
        $read = $this->_getReadAdapter();
        if ($read && !is_null($value)) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $read->fetchRow($select);

            if ($data) {
                
                $object->setData($this->setKeysToLowerCase($data));
            }
        }
        
        $this->unserializeFields($object);
        $this->_afterLoad($object);
        return $this;
    }   
    
     public function joinDesignation($select, $joinTableAlias = 'main_table',
            $joinTableColumn = 'ART_COMPLETE_DES_ID', $columns = '',
            $desTextAlias = null, $joinTemplates = false, $lngId = null)
    {
        if (is_null($lngId)){
            $lngId = $this->getLngId();
        }
        $designationAlias = $this->_getTableAlias($select, 'td_designation');
        if(is_null($desTextAlias)){
            $desTextAlias = $this->_getTableAlias($select, 'td_desText');
        }
        $desTextTemplatesAlias = $this->_getTableAlias($select, 'des_text_template');
        $templateColumns = '';
        if(!is_array($columns)){
            if(strlen($columns)){
                $column = $columns;
                $columns = array($column => "{$desTextAlias}.TEX_TEXT");
                $templateColumns = array($column.'_template' => "{$desTextTemplatesAlias}.text");
            }
        }else{
            foreach($columns as $key => $column){
                $columns[$key] = str_replace('{{des_text}}', $desTextAlias, $column);
            }
        }
        if (is_array($columns)){
            $this->_lastDesignationColumns = $columns;
        }
        $select
            ->joinLeft(array($designationAlias => $this->getTable('magedoc/tecdoc_designation')),
                "{$designationAlias}.DES_ID = {$joinTableAlias}.{$joinTableColumn} AND {$designationAlias}.DES_LNG_ID = {$lngId}", '')
            ->joinLeft(array($desTextAlias => $this->getTable('magedoc/tecdoc_desText')), "{$desTextAlias}.TEX_ID = {$designationAlias}.DES_TEX_ID",
                $columns);
        if ($joinTemplates){
           $select->joinLeft(array($desTextTemplatesAlias => $this->getTable('magedoc/des_text_template')), "{$desTextTemplatesAlias}.td_tex_id = {$designationAlias}.DES_TEX_ID",
               $templateColumns);
        }
               
        return $this;
    }

    public function joinCountryDesignation($select, $joinTableAlias = 'main_table',
            $joinTableColumn = 'TYP_CDS_ID', $columns = '', $desTextAlias = null,
            $joinTemplates = false, $lngId = null)
    {
        if (is_null($lngId)){
            $lngId = $this->getLngId();
        }
        $countryDesignationAlias = $this->_getTableAlias($select, 'td_cDesignation');
        if(is_null($desTextAlias)){
            $desTextAlias = $this->_getTableAlias($select, 'td_desText');
        }
        $desTextTemplatesAlias = $this->_getTableAlias($select, 'des_text_template');
        $templateColumns = '';

        if(!is_array($columns)){
            if(strlen($columns)){
                $column = $columns;
                $columns = array($columns => "{$desTextAlias}.TEX_TEXT");
                $templateColumns = array($column.'_template' => "{$desTextTemplatesAlias}.text");
            }
        } else {
            foreach($columns as $key => $column){
                $columns[$key] = str_replace('{{des_text}}', $desTextAlias, $column);
            }
        }
        if (is_array($columns)){
            $this->_lastDesignationColumns = $columns;
        }
        $select
            ->joinInner(array($countryDesignationAlias => $this->getTable('magedoc/tecdoc_countryDesignation')),
                "{$countryDesignationAlias}.CDS_ID = {$joinTableAlias}.{$joinTableColumn} 
                AND {$countryDesignationAlias}.CDS_LNG_ID = {$this->getLngId()}", '')
            ->joinLeft(array($desTextAlias => $this->getTable('magedoc/tecdoc_desText')), 
            "{$desTextAlias}.TEX_ID = {$countryDesignationAlias}.CDS_TEX_ID",
                $columns);
        if ($joinTemplates){
            $select->joinLeft(array($desTextTemplatesAlias => $this->getTable('magedoc/des_text_template')), "{$desTextTemplatesAlias}.td_tex_id = {$designationAlias}.DES_TEX_ID",
                $templateColumns);
        }
     
        return $this;
    }

    protected function _getTableAlias($select, $tableAlias)
    {
        $newTableAlias = $tableAlias;
        $tables = $select->getPart(Zend_Db_Select::FROM);
        $i = 0;
        do{
            if(!isset($tables[$newTableAlias])){
                break;
            }
            $i++;
            $newTableAlias = $tableAlias . $i;
        }while($i <= count($tables));
        
        return $newTableAlias;
    }
    
    public function getLngId()
    {
        return Mage::helper('magedoc')->getLngId();
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if(!$object->isPartialLoad()){
            $this->_prepareFullSelect($select);
        }
        return $select;
    }

    public function prepareFullSelect($select, $mainTableAlias = null)
    {
        return $this->_prepareFullSelect($select, $mainTableAlias);
    }
    
    protected function _prepareFullSelect($select)
    {
        return $select;
    }

    public function getTable( $entity_name, $addDatabase = true )
    {
        $tableName = parent::getTable( $entity_name );

        $explodedTableName = explode('/', $entity_name) ;
        $lastPartOfTableName = end( $explodedTableName );

        if( $addDatabase && strpos($lastPartOfTableName, 'tecdoc_') === 0) {
            $prefix = Mage::helper('magedoc')->getTecDocTablePrefix();
            $suffix = Mage::helper('magedoc')->getTecDocTableSuffix();

            return $prefix . $tableName . $suffix;
        }

        return $tableName;
    }
    
    public function getLastDesignationColumns()
    {
        return $this->_lastDesignationColumns;
    }
}

