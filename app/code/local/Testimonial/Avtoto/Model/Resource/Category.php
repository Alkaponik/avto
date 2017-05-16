<?php

class Testimonial_Avtoto_Model_Resource_Category extends Mage_Catalog_Model_Resource_Category
{
    /**
     * Process category data before saving
     * prepare path and increment children count for parent categories
     *
     * @param Varien_Object $object
     * @return Mage_Catalog_Model_Resource_Category
     */
    protected function _beforeSave(Varien_Object $object)
    {
        Mage_Catalog_Model_Resource_Abstract::_beforeSave($object);

        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }
        if ($object->getLevel() === null) {
            $object->setLevel(1);
        }

        if (!$object->getId()) {
            $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            $path  = explode('/', $object->getPath());
            $level = count($path);
            $object->setLevel($level);
            if ($level) {
                $object->setParentId($path[$level - 1]);
            }
            $object->setPath($object->getPath() . '/');

            /*$toUpdateChild = explode('/',$object->getPath());

            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('children_count'  => new Zend_Db_Expr('children_count+1')),
                array('entity_id IN(?)' => $toUpdateChild)
            );*/

        }
        return $this;
    }

    protected function _getMaxPosition($path)
    {
        $adapter = $this->getReadConnection();
        $positionField = $adapter->quoteIdentifier('sort_order');
        $level   = count(explode('/', $path));
        $bind = array(
            'c_level' => $level,
            'c_path'  => $path . '/%'
        );
        $select  = $adapter->select()
            ->from($this->getTable('catalog/category'), 'MAX(' . $positionField . ')')
            ->where($adapter->quoteIdentifier('path') . ' LIKE :c_path')
            ->where($adapter->quoteIdentifier('level') . ' = :c_level');

        $position = $adapter->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        return $position;
    }

    /**
     * Update path field
     *
     * @param Mage_Catalog_Model_Category $object
     * @return Mage_Catalog_Model_Resource_Category
     */
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('path' => $object->getPath()),
                array('categoryID = ?' => $object->getId())
            );
        }
        return $this;
    }
}
