<?php

class Testimonial_MageDoc_Model_Mysql4_Indexer_Tecdoc extends Mage_Index_Model_Resource_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/searchTree', 'str_id');
    }

    public function reindexAll()
    {
        $this->generateSearchTree();
        return $this;
    }

    public function generateSearchTree()
    {
        $resource = Mage::getResourceModel('magedoc/tecdoc_searchTree');
        $sourceTable = $resource->getTable('magedoc/tecdoc_searchTree');

        $this->_getIndexAdapter()->query("INSERT IGNORE INTO " . $sourceTable .
            " (STR_ID, STR_ID_PARENT, STR_TYPE, STR_LEVEL, STR_DES_ID, STR_SORT, STR_NODE_NR)
                VALUES (1,0,1,0,42252,1,0)");

        $this->_getIndexAdapter()->update($sourceTable,
            array('STR_ID_PARENT' => 1),
            'STR_ID_PARENT IS NULL');

        $this->_getIndexAdapter()->query("INSERT IGNORE INTO " . $this->getTable('magedoc/searchTree') . " (str_id, path) VALUES (1, 1)");

        $sourceColumns = array('STR_ID', new Zend_Db_Expr("CONCAT(searchTree.path, '/', tdSearchTree.STR_ID)"));
        $maxLevelSelect = $this->_getIndexAdapter()
            ->select()->from(
            array('tdSearchTree' => $sourceTable),
            array(new Zend_Db_Expr('MAX(STR_LEVEL)'))
        );
        $maxLevel = $this->_getIndexAdapter()->fetchOne($maxLevelSelect);

        for ($i = 1; $i <= $maxLevel; $i++) {
            $searchTreeSelect = $this->_getIndexAdapter()
                ->select()->from(
                    array('tdSearchTree' => $sourceTable),
                    $sourceColumns
            );
            $searchTreeSelect->join(array('searchTree' => $this->getTable('magedoc/searchTree')),
                'searchTree.str_id = tdSearchTree.STR_ID_PARENT',
                ''
            );
            $searchTreeSelect->where('tdSearchTree.STR_LEVEL = ?', $i);

            $this->_getIndexAdapter()->query(
                $this->_getIndexAdapter()->insertFromSelect(
                    $searchTreeSelect,
                    $this->getTable('magedoc/searchTree'),
                    array('str_id', 'path'),
                    Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE)
            );
        }
    }
}