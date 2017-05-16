<?php

class Testimonial_ImportExport_Model_Import_Entity_Product_Type_ExtendedGrouped
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Grouped
{

    protected $_requiredAttributesToSkip = array(
        'short_description' => '',
        'weight'            => 0,
        'price'             => 0,
        'description'       => 0
    );

    protected function _isAttributeRequiredCheckNeeded($attrCode)
    {
        return parent::_isAttributeRequiredCheckNeeded($attrCode)
            && !isset($this->_requiredAttributesToSkip[$attrCode]);
    }

    public function addAttributeParams($attrSetName, array $attrParams)
    {
        return $this->_addAttributeParams($attrSetName, $attrParams);
    }

    /**
     * Core bug fixes required:
     * 1. if parent product validation fails
     * the simples following it will be associated with the previous parent
     * 2. if no parent product occured yet and the associated is present
     * the import will fail
     */

    /**
     * Save product type specific data.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract
     */
    public function saveData()
    {
        $groupedLinkId = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;
        $connection    = Mage::getSingleton('core/resource')->getConnection('write');
        $resource      = Mage::getResourceModel('catalog/product_link');
        $mainTable     = $resource->getMainTable();
        $relationTable = $resource->getTable('catalog/product_relation');
        $newSku        = $this->_entityModel->getNewSku();
        $oldSku        = $this->_entityModel->getOldSku();
        $attributes    = array();

        // pre-load attributes parameters
        $select = $connection->select()
            ->from($resource->getTable('catalog/product_link_attribute'), array(
                'id'   => 'product_link_attribute_id',
                'code' => 'product_link_attribute_code',
                'type' => 'data_type'
            ))->where('link_type_id = ?', $groupedLinkId);
        foreach ($connection->fetchAll($select) as $row) {
            $attributes[$row['code']] = array(
                'id' => $row['id'],
                'table' => $resource->getAttributeTypeTable($row['type'])
            );
        }
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $linksData     = array(
                'product_ids'      => array(),
                'links'            => array(),
                'attr_product_ids' => array(),
                'position'         => array(),
                'qty'              => array(),
                'relation'         => array()
            );
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)
                    || empty($rowData['_associated_sku'])
                ) {
                    continue;
                }
                if (isset($newSku[$rowData['_associated_sku']])) {
                    $linkedProductId = $newSku[$rowData['_associated_sku']]['entity_id'];
                } elseif (isset($oldSku[$rowData['_associated_sku']])) {
                    $linkedProductId = $oldSku[$rowData['_associated_sku']]['entity_id'];
                } else {
                    Mage::log('Missing associated SKU: '.$rowData['_associated_sku']);
                    continue;
                }
                $scope = $this->_entityModel->getRowScope($rowData);
                if (Mage_ImportExport_Model_Import_Entity_Product::SCOPE_DEFAULT == $scope) {
                    $productData = $newSku[$rowData[Mage_ImportExport_Model_Import_Entity_Product::COL_SKU]];
                } else {
                    $colAttrSet = Mage_ImportExport_Model_Import_Entity_Product::COL_ATTR_SET;
                    $rowData[$colAttrSet] = $productData['attr_set_code'];
                    $rowData[Mage_ImportExport_Model_Import_Entity_Product::COL_TYPE] = $productData['type_id'];
                }
                $productId = $productData['entity_id'];

                if ($this->_type != $rowData[Mage_ImportExport_Model_Import_Entity_Product::COL_TYPE]) {
                    continue;
                }
                $linksData['product_ids'][$productId] = true;
                $linksData['links'][$productId][$linkedProductId] = $groupedLinkId;
                /* added empty values check */
                if ($productId && $linkedProductId){
                    $linksData['relation'][] = array('parent_id' => $productId, 'child_id' => $linkedProductId);
                }
                $qty = empty($rowData['_associated_default_qty']) ? 0 : $rowData['_associated_default_qty'];
                $pos = empty($rowData['_associated_position']) ? 0 : $rowData['_associated_position'];

                if ($qty || $pos) {
                    $linksData['attr_product_ids'][$productId] = true;
                    if ($pos) {
                        $linksData['position']["{$productId} {$linkedProductId}"] = array(
                            'product_link_attribute_id' => $attributes['position']['id'],
                            'value' => $pos
                        );
                    }
                    if ($qty) {
                        $linksData['qty']["{$productId} {$linkedProductId}"] = array(
                            'product_link_attribute_id' => $attributes['qty']['id'],
                            'value' => $qty
                        );
                    }
                }
            }
            // save links and relations
            if ($linksData['product_ids'] && $this->getBehavior() != Mage_ImportExport_Model_Import::BEHAVIOR_APPEND) {
                $connection->delete(
                    $mainTable,
                    $connection->quoteInto(
                        'product_id IN (?) AND link_type_id = ' . $groupedLinkId,
                        array_keys($linksData['product_ids'])
                    )
                );
            }
            if ($linksData['links']) {
                $mainData = array();

                foreach ($linksData['links'] as $productId => $linkedData) {
                    foreach ($linkedData as $linkedId => $linkType) {
                        /* added empty values check */ 
                        if ($productId && $linkedId && $linkType){
                            $mainData[] = array(
                                'product_id'        => $productId,
                                'linked_product_id' => $linkedId,
                                'link_type_id'      => $linkType
                            );
                        }
                    }
                }
                //Safe insertOnDuplicate
                $this->_insertOnDuplicate($mainTable, $mainData);
                $this->_insertOnDuplicate($relationTable, $linksData['relation']);
                
            }
            // save positions and default quantity
            if ($linksData['attr_product_ids']) {
                $savedData = $connection->fetchPairs($connection->select()
                    ->from($mainTable, array(
                        new Zend_Db_Expr('CONCAT_WS(" ", product_id, linked_product_id)'), 'link_id'
                    ))
                    ->where(
                        'product_id IN (?) AND link_type_id = ' . $groupedLinkId,
                        array_keys($linksData['attr_product_ids'])
                    )
                );
                foreach ($savedData as $pseudoKey => $linkId) {
                    if (isset($linksData['position'][$pseudoKey])) {
                        $linksData['position'][$pseudoKey]['link_id'] = $linkId;
                    }
                    if (isset($linksData['qty'][$pseudoKey])) {
                        $linksData['qty'][$pseudoKey]['link_id'] = $linkId;
                    }
                }
                if ($linksData['position']) {
                    $this->_insertOnDuplicate($attributes['position']['table'], $linksData['position']);
                }
                if ($linksData['qty']) {
                    $this->_insertOnDuplicate($attributes['qty']['table'], $linksData['qty']);
                }
            }
        }
        return $this;
    }

    protected function _insertOnDuplicate($table, $data)
    {
        $connection    = Mage::getSingleton('core/resource')->getConnection('write');
        try {
            $connection->insertOnDuplicate($table, $data);
        }
        catch (Exception $e) {
            $resultBlock = Mage::app()->getLayout()->getMessagesBlock();
            if ($resultBlock) {
                $resultBlock->addError($e->getMessage());
            }
            Mage::logException($e);
            Mage::log("insertOnDuplicate failed into table $table:");
            Mage::log($data);
        }
    }
    
}
