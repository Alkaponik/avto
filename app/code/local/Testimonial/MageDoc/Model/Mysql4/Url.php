<?php

class Testimonial_MageDoc_Model_Mysql4_Url extends Mage_Catalog_Model_Resource_Url
{
    /**
     * Retrieve manufacturer data objects for store
     *
     * @param int $storeId
     * @param int $lastEntityId
     * @return array
     */
    public function getManufacturersByStore($storeId, &$lastEntityId)
    {
        return $this->_getManufacturers(null, $storeId, $lastEntityId, $lastEntityId);
    }

    /**
     * Retrieve manufacturer data object
     *
     * @param int $manufacturerId
     * @param int $storeId
     * @return Varien_Object
     */
    public function getManufacturer($manufacturerId, $storeId)
    {
        $entityId = 0;
        $manufacturers = $this->_getManufacturers($manufacturerId, $storeId, 0, $entityId);
        if (isset($manufacturers[$manufacturerId])) {
            return $manufacturers[$manufacturerId];
        }
        return false;
    }

    /**
     * Retrieve Manufacturers data objects
     *
     * @param int|array $manufacturerIds
     * @param int $storeId
     * @param int $entityId
     * @param int $lastEntityId
     * @return array
     */
    protected function _getManufacturers($manufacturerIds, $storeId, $entityId, &$lastEntityId)
    {
        $manufacturers   = array();
        $websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
        $adapter    = $this->_getReadAdapter();
        if ($manufacturerIds !== null) {
            if (!is_array($manufacturerIds)) {
                $manufacturerIds = array($manufacturerIds);
            }
        }
        $bind = array(
            'td_mfa_id'  => (int)$entityId,
        );
        $select = $adapter->select()
            ->from(
                array('e' => $this->getTable('magedoc/manufacturer')),
                array('td_mfa_id', 'name', 'url_key', 'url_path'))
            ->where('e.td_mfa_id > :td_mfa_id')
            ->order('e.td_mfa_id')
            ->limit($this->_productLimit);
        if ($manufacturerIds !== null) {
            $select->where('e.td_mfa_id IN(?)', $manufacturerIds);
        }

        $rowSet = $adapter->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $manufacturer = new Varien_Object($row);
            $manufacturer->setIdFieldName('td_mfa_id');
            $manufacturer->setStoreId($storeId);
            $manufacturers[$manufacturer->getId()] = $manufacturer;
            $lastEntityId = $manufacturer->getId();
        }

        unset($rowSet);

        return $manufacturers;
    }

    /**
     * Retrieve manufacturer data objects for store
     *
     * @param int $storeId
     * @param int $lastEntityId
     * @return array
     */
    public function getModelsByStore($storeId, &$lastEntityId)
    {
        return $this->_getModels(null, $storeId, $lastEntityId, $lastEntityId);
    }

    /**
     * Retrieve manufacturer data objects for store
     *
     * @param int $storeId
     * @param int $lastEntityId
     * @return array
     */
    public function getModelsByManufacturer($storeId, $manufacturerId)
    {
        $entityId = 0;
        return $this->_getModels(null, $storeId, 0, $entityId, $manufacturerId);
    }

    /**
     * Retrieve model data object
     *
     * @param int $modelId
     * @param int $storeId
     * @return Varien_Object
     */
    public function getModel($modelId, $storeId)
    {
        $entityId = 0;
        $models = $this->_getModels($modelId, $storeId, 0, $entityId);
        if (isset($models[$modelId])) {
            return $models[$modelId];
        }
        return false;
    }

    /**
     * Retrieve Manufacturers data objects
     *
     * @param int|array $manufacturerIds
     * @param int $storeId
     * @param int $entityId
     * @param int $lastEntityId
     * @return array
     */
    protected function _getModels($modelIds, $storeId, $entityId, &$lastEntityId, $manufacturerId = null)
    {
        $models   = array();
        $websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
        $adapter    = $this->_getReadAdapter();
        $tecdocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');
        if ($modelIds !== null) {
            if (!is_array($modelIds)) {
                $modelIds = array($modelIds);
            }
        }
        $bind = array(
            'td_mod_id'  => (int)$entityId,
        );
        $select = $adapter->select()
            ->from(
            array('md_model' => $this->getTable('magedoc/model')),
            array('td_mod_id', 'name', 'url_key', 'url_path'))
            ->join(array('td_model' => $tecdocResource->getTable('magedoc/tecdoc_model')),
            'md_model.td_mod_id = td_model.mod_id',
            array ('mod_mfa_id' => 'td_model.MOD_MFA_ID'))
            ->where('md_model.td_mod_id > :td_mod_id')
            ->order('md_model.td_mod_id')
            ->limit($this->_productLimit);
        if ($modelIds !== null) {
            $select->where('md_model.td_mod_id IN(?)', $modelIds);
        }

        if ($manufacturerId !== null) {
            $select->where('td_model.MOD_MFA_ID = ?', $manufacturerId);
        }

        $rowSet = $adapter->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $model = new Varien_Object($row);
            $model->setIdFieldName('td_mod_id');
            $model->setStoreId($storeId);
            $models[$model->getId()] = $model;
            $lastEntityId = $model->getId();
        }

        unset($rowSet);

        return $models;
    }

    /**
     * Prepare rewrites for condition
     *
     * @param int $storeId
     * @param int|array $manufacturerIds
     * @param int|array $modelIds
     * @return array
     */
    public function prepareRewrites($storeId, $manufacturerIds = null, $modelIds = null)
    {
        $rewrites   = array();
        $adapter    = $this->_getWriteAdapter();
        $select     = $adapter->select()
            ->from($this->getMainTable())
            ->where('store_id = :store_id')
            ->where('is_system = ?', 1);
        $bind = array('store_id' => $storeId);
        if ($manufacturerIds === null) {
            $select->where('category_id IS NULL');
        } elseif ($manufacturerIds) {
            $mfaIds = is_array($manufacturerIds) ? $manufacturerIds : array($manufacturerIds);

            $select->where('category_id IN(?)', $mfaIds);
            $select->where('id_path LIKE "magedoc_manufacturer/%"');
        }

        if ($modelIds === null) {
            $select->where('product_id IS NULL');
        } elseif ($modelIds) {
            $select->where('product_id IN(?)', $modelIds);
            $select->where('id_path LIKE "magedoc_model/%"');
        }

        $rowSet = $adapter->fetchAll($select, $bind);

        foreach ($rowSet as $row) {
            $rewrite = new Varien_Object($row);
            $rewrite->setIdFieldName($this->getIdFieldName());
            $rewrites[$rewrite->getIdPath()] = $rewrite;
        }

        return $rewrites;
    }

    /**
     * Remove unused rewrites for product - called after we created all needed rewrites for product and know the categories
     * where the product is contained ($excludeCategoryIds), so we can remove all invalid product rewrites that have other category ids
     *
     * Notice: this routine is not identical to clearCategoryProduct(), because after checking all categories this one removes rewrites
     * for product still contained within categories.
     *
     * @param int $modelId Product entity Id
     * @param int $storeId Store Id for rewrites
     * @param array $excludeManufacturerIds Array of category Ids that should be skipped
     * @return Testimonial_MageDoc_Model_Mysql4_Url
     */
    public function clearModelRewrites($modelId, $storeId, $excludeManufacturerIds = array())
    {
        $where = array(
            'store_id = ?' => $storeId,
            "id_path LIKE 'magedoc_model/{$modelId}'"
        );

        if (!empty($excludeManufacturerIds)) {
            $where['category_id NOT IN (?)'] = $excludeManufacturerIds;
            // If there's at least one category to skip, also skip root category, because product belongs to website
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    public function clearManufacturerRewrites($manufacturerId, $storeId)
    {
        $where = array(
            'store_id = ?' => $storeId,
            "id_path LIKE 'magedoc_manufacturer/{$manufacturerId}'"
        );

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }
}
