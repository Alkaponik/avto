<?php

class MageDoc_DirectoryTecdoc_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    public function getTable( $entity_name )
    {
        $tableName = parent::getTable( $entity_name );

        $explodedTableName = explode('/', $entity_name) ;
        $lastPartOfTableName = end( $explodedTableName );

        if( strpos($lastPartOfTableName, 'tecdoc_') === 0 ) {
            $prefix = Mage::helper('magedoc')->getTecDocTablePrefix();
            $suffix = Mage::helper('magedoc')->getTecDocTableSuffix();

            return $prefix . $tableName . $suffix;
        }

        return $tableName;
    }
}
