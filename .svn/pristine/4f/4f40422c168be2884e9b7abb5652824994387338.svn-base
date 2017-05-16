<?php

class Testimonial_MageDoc_Model_Url extends Mage_Catalog_Model_Url
{
    const CACHE_TAG = 'url_rewrite';

    /**
     * Static URL instance
     *
     * @var Mage_Core_Model_Url
     */
    protected static $_url;

    /**
     * Static URL Rewrite Instance
     *
     * @var Mage_Core_Model_Url_Rewrite
     */
    protected static $_urlRewrite;

    static protected $_refreshedManufacturerRewrites = array();

    static protected $_refreshedModelRewrites = array();

    /**
     * Retrieve URL Rewrite Instance
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewrite()
    {
        if (!self::$_urlRewrite) {
            self::$_urlRewrite = Mage::getModel('core/url_rewrite');
        }
        return self::$_urlRewrite;
    }

    /**
     * Retrieve URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (!self::$_url) {
            self::$_url = Mage::getModel('core/url');
        }
        return self::$_url;
    }

    /**
     * Retrieve resource model
     *
     * @return Testimonial_MageDoc_Model_Mysql4_Url
     */
    public function getResource()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('magedoc/url');
        }
        return $this->_resourceModel;
    }

    /**
     * Retrieve Manufacturer URL using UrlDataObject
     *
     * @param Testimonial_MageDoc_Model_Manufacturer $manufacturer
     * @param array $params
     * @return string
     */
    public function getManufacturerUrl(Varien_Object $manufacturer, $params = array())
    {
        $routePath      = '';
        $routeParams    = $params;
        $entity = Testimonial_MageDoc_Model_Manufacturer::ENTITY;

        $storeId    = $manufacturer->getStoreId();

        if ($manufacturer->hasUrlDataObject()) {
            $requestPath = $manufacturer->getUrlDataObject()->getUrlRewrite();
            $routeParams['_store'] = $manufacturer->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $manufacturer->getRequestPath();
            if (empty($requestPath) && $requestPath !== false) {
                $idPath = sprintf("{$entity}/%d", $manufacturer->getId());

                $rewrite = $this->getUrlRewrite();
                $rewrite->setStoreId($storeId)
                    ->loadByIdPath($idPath);
                if ($rewrite->getId()) {
                    $requestPath = $rewrite->getRequestPath();
                    $manufacturer->setRequestPath($requestPath);
                } else {
                    $manufacturer->setRequestPath(false);
                }
            }
        }

        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'magedoc/make/view';
            $routeParams['id']  = $manufacturer->getId();
            $routeParams['s']   = $manufacturer->getUrlKey();
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return $this->getUrlInstance()->setStore($storeId)
            ->getUrl($routePath, $routeParams);
    }

    /**
     * Retrieve Model URL using UrlDataObject
     *
     * @param Testimonial_MageDoc_Model_Model $model
     * @param array $params
     * @return string
     */
    public function getModelUrl(Varien_Object $model, $params = array())
    {
        $routePath      = '';
        $routeParams    = $params;
        $entity = Testimonial_MageDoc_Model_Model::ENTITY;

        $storeId    = $model->getStoreId();

        if ($model->hasUrlDataObject()) {
            $requestPath = $model->getUrlDataObject()->getUrlRewrite();
            $routeParams['_store'] = $model->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $model->getRequestPath();
            if (empty($requestPath) && $requestPath !== false) {
                $idPath = sprintf("{$entity}/%d", $model->getId());

                $rewrite = $this->getUrlRewrite();
                $rewrite->setStoreId($storeId)
                    ->loadByIdPath($idPath);
                if ($rewrite->getId()) {
                    $requestPath = $rewrite->getRequestPath();
                    $model->setRequestPath($requestPath);
                } else {
                    $model->setRequestPath(false);
                }
            }
        }

        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'magedoc/model/view';
            $routeParams['id']  = $model->getId();
            $routeParams['s']   = $model->getUrlKey();
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return $this->getUrlInstance()->setStore($storeId)
            ->getUrl($routePath, $routeParams);
    }

    /**
     * Retrieve Model URL using UrlDataObject
     *
     * @param Testimonial_MageDoc_Model_Type $type
     * @param array $params
     * @return string
     */
    public function getTypeUrl(Varien_Object $type, $params = array())
    {
        return $this->getModelUrl( $type->getModel(), array('_query' => array('TYP_ID' => $type->getId())));
    }

    /**
     * Refresh all rewrite urls for some store or for all stores
     * Used to make full reindexing of url rewrites
     *
     * @param int $storeId
     * @return Testimonial_MageDoc_Model_Url
     */
    public function refreshRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshRewrites($store->getId());
            }
            return $this;
        }

        $this->refreshManufacturerRewrites($storeId);
        $this->refreshModelRewrites($storeId);

        return $this;
    }

    /**
     * Refresh manufacturer rewrite urls for one store or all stores
     * Called as a reaction on product change that affects rewrites
     *
     * @param int $manufacturerId
     * @param int|null $storeId
     * @return Testimonial_MageDoc_Model_Url
     */
    public function refreshManufacturerRewrite($manufacturerId, $storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshManufacturerRewrite($manufacturerId, $store->getId());
            }
            return $this;
        }

        $manufacturer = $this->getResource()->getManufacturer($manufacturerId, $storeId);
        if ($manufacturer) {
            $store = $this->getStores($storeId);

            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, $manufacturerId, false);

            $this->_refreshManufacturerRewrite($manufacturer);

            //$this->getResource()->clearManufacturerRewrites($manufacturerId, $storeId);

            unset($manufacturer);
        } else {
            // Product doesn't belong to this store - clear all its url rewrites including root one
            $this->getResource()->clearManufacturerRewrites($manufacturerId, $storeId);
        }

        return $this;
    }

    /**
     * Refresh all manufacturer rewrites for designated store
     *
     * @param int $storeId
     * @return Testimonial_MageDoc_Model_Url
     */
    public function refreshManufacturerRewrites($storeId)
    {
        $lastEntityId = 0;
        $process = true;

        while ($process == true) {
            $manufacturers = $this->getResource()->getManufacturersByStore($storeId, $lastEntityId);
            if (!$manufacturers) {
                $process = false;
                break;
            }

            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, array_keys($manufacturers), false);

            foreach ($manufacturers as $manufacturer) {
                $this->_refreshManufacturerRewrite($manufacturer);
            }

            unset($manufacturers);
            $this->_rewrites = array();
        }

        return $this;
    }

    /**
     * Refresh all manufacturer rewrites for designated store
     *
     * @param int $storeId
     * @return Testimonial_MageDoc_Model_Url
     */
    public function refreshModelRewrites($storeId)
    {
        $lastEntityId = 0;
        $process = true;

        while ($process == true) {
            $models = $this->getResource()->getModelsByStore($storeId, $lastEntityId);
            if (!$models) {
                $process = false;
                break;
            }

            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, false, array_keys($models));

            foreach ($models as $model) {
                if ($manufacturer = Mage::getResourceSingleton('magedoc/manufacturer_collection')->getItemById($model->getModMfaId())){
                    $this->_refreshModelRewrite($model, $manufacturer);
                }
            }

            unset($models);
            $this->_rewrites = array();
        }

        return $this;
    }

    /**
     * Refresh manufacturer rewrite urls for one store or all stores
     * Called as a reaction on product change that affects rewrites
     *
     * @param int $manufacturerId
     * @param int|null $storeId
     * @return Testimonial_MageDoc_Model_Url
     */
    public function refreshModelRewrite($modelId, $storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshModelRewrite($modelId, $store->getId());
            }
            return $this;
        }

        $model = $this->getResource()->getModel($modelId, $storeId);
        if ($model) {
            $store = $this->getStores($storeId);

            if ($manufacturer = Mage::getResourceSingleton('magedoc/manufacturer_collection')->getItemById($model->getModMfaId())){
                if ($manufacturer->getUrlPath() == ''){
                    $this->refreshManufacturerRewrite($manufacturer->getId(), $storeId);
                    $manufacturer->setData($this->getResource()->getManufacturer($manufacturer->getId(), $storeId));
                }
                $this->_rewrites = $this->getResource()->prepareRewrites($storeId, $model->getModMfaId(), $modelId);
                $this->_refreshModelRewrite($model, $manufacturer);
                //$this->getResource()->clearModelRewrites($modelId, $storeId);
            }

            unset($manufacturer);
            unset($model);
        } else {
            // Product doesn't belong to this store - clear all its url rewrites including root one
            $this->getResource()->clearModelRewrites($modelId, $storeId);
        }

        return $this;
    }
    
    /**
     * Refresh product rewrite
     *
     * @param Varien_Object $manufacturer
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshManufacturerRewrite(Varien_Object $manufacturer)
    {
        $entityType = Testimonial_MageDoc_Model_Manufacturer::ENTITY;
        if (!$this->_checkManufacturerRewrite($manufacturer)){
            return $this;
        }
        
        if ($manufacturer->getUrlKey() == '') {
            $urlKey = $this->getProductModel()->formatUrlKey($manufacturer->getName());
        }
        else {
            $urlKey = $this->getProductModel()->formatUrlKey($manufacturer->getUrlKey());
        }

        $idPath      = "$entityType/{$manufacturer->getId()}";
        $targetPath  = "magedoc/make/view/id/{$manufacturer->getId()}";
        $requestPath = $this->getEntityRequestPath($manufacturer, $entityType);

        $updateKeys = true;

        $rewriteData = array(
            'store_id'      => $manufacturer->getStoreId(),
            'category_id'   => null,
            'product_id'    => null,
            'id_path'       => $idPath,
            'request_path'  => $requestPath,
            'target_path'   => $targetPath,
            'is_system'     => 1
        );

        $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

        if ($this->getShouldSaveRewritesHistory($manufacturer->getStoreId())) {
            $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
        }

        $manufacturerDummy = Mage::getSingleton('magedoc/manufacturer')
            ->setData($manufacturer->getData());

        if ($updateKeys && $manufacturer->getUrlKey() != $urlKey) {
            $manufacturerDummy->setUrlKey($urlKey);

            Mage::helper('magedoc')->saveStaticAttributes($manufacturerDummy, 'url_key');
        }
        if ($updateKeys && $manufacturer->getUrlPath() != $requestPath) {
            $manufacturerDummy->setUrlPath($requestPath);
            Mage::helper('magedoc')->saveStaticAttributes($manufacturerDummy, 'url_path');
        }

        return $this;
    }

    /**
     * Refresh product rewrite
     *
     * @param Varien_Object $manufacturer
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshModelRewrite(Varien_Object $model, Varien_Object $manufacturer)
    {
        $entityType = Testimonial_MageDoc_Model_Model::ENTITY;
        if (!$this->_checkModelRewrite($model)){
            return $this;
        }

        if ($model->getUrlKey() == '') {
            $urlKey = $this->getProductModel()->formatUrlKey($model->getName());
        }
        else {
            $urlKey = $this->getProductModel()->formatUrlKey($model->getUrlKey());
        }

        $idPath      = "{$entityType}/{$model->getId()}";
        $targetPath  = "magedoc/model/view/id/{$model->getId()}";
        $requestPath = $this->getEntityRequestPath($model, $entityType, $manufacturer);

        $updateKeys = true;

        $rewriteData = array(
            'store_id'      => $model->getStoreId(),
            'category_id'   => null,
            'product_id'    => null,
            'id_path'       => $idPath,
            'request_path'  => $requestPath,
            'target_path'   => $targetPath,
            'is_system'     => 1
        );

        $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

        if ($this->getShouldSaveRewritesHistory($model->getStoreId())) {
            $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
        }

        $modelDummy = Mage::getSingleton('magedoc/model')
            ->setData($model->getData());

        if ($updateKeys && $model->getUrlKey() != $urlKey) {
            $modelDummy->setUrlKey($urlKey);

            Mage::helper('magedoc')->saveStaticAttributes($modelDummy, 'url_key');
        }
        if ($updateKeys && $model->getUrlPath() != $requestPath) {
            $modelDummy->setUrlPath($requestPath);
            Mage::helper('magedoc')->saveStaticAttributes($modelDummy, 'url_path');
        }

        return $this;
    }

    protected function _checkManufacturerRewrite(Varien_Object $manufacturer)
    {
        if (!isset(self::$_refreshedManufacturerRewrites[$manufacturer->getStoreId()][$manufacturer->getId()])){
            self::$_refreshedManufacturerRewrites[$manufacturer->getStoreId()][$manufacturer->getId()] = true;
            return true;
        }
        return false;
    }

    protected function _checkModelRewrite(Varien_Object $model)
    {
        if (!isset(self::$_refreshedModelRewrites[$model->getStoreId()][$model->getId()])){
            self::$_refreshedModelRewrites[$model->getStoreId()][$model->getId()] = true;
            return true;
        }
        return false;
    }

    /**
     * Get unique product request path
     *
     * @param   Varien_Object $manufacturer
     * @param   Varien_Object $category
     * @return  string
     */
    public function getEntityRequestPath($manufacturer, $entityType = 'magedoc_manufacturer', $mfa = null)
    {
        if ($manufacturer->getUrlKey() == '') {
            $urlKey = $this->getProductModel()->formatUrlKey($manufacturer->getName());
        } else {
            $urlKey = $this->getProductModel()->formatUrlKey($manufacturer->getUrlKey());
        }
        $storeId = $manufacturer->getStoreId();
        $suffix  = $this->getCategoryUrlSuffix($storeId);
        $idPath  = "{$entityType}/{$manufacturer->getId()}";

        $requestPath = $urlKey;

        if (!is_null($mfa)){
            $requestPath = $mfa->getUrlPath() . $requestPath;
        }

        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        $this->_rewrite = null;
        /**
         * Check $requestPath should be unique
         */
        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();

            if ($existingRequestPath == $requestPath . $suffix) {
                return $existingRequestPath;
            }

            $existingRequestPath = preg_replace('/' . preg_quote($suffix, '/') . '$/', '', $existingRequestPath);
            /**
             * Check if existing request past can be used
             */
            if ($manufacturer->getUrlKey() == '' && !empty($requestPath)
                && strpos($existingRequestPath, $requestPath) === 0
            ) {
                $existingRequestPath = preg_replace(
                    '/^' . preg_quote($requestPath, '/') . '/', '', $existingRequestPath
                );
                if (preg_match('#^-([0-9]+)$#i', $existingRequestPath)) {
                    return $this->_rewrites[$idPath]->getRequestPath();
                }
            }

            $fullPath = $requestPath.$suffix;
            if ($this->_deleteOldTargetPath($fullPath, $idPath, $storeId)) {
                return $fullPath;
            }
        }

        /**
         * Check 2 variants: $requestPath and $requestPath . '-' . $manufacturerId
         */
        $validatedPath = $this->getResource()->checkRequestPaths(
            array($requestPath.$suffix, $requestPath.'-'.$manufacturer->getId().$suffix),
            $storeId
        );

        if ($validatedPath) {
            return $validatedPath;
        }
        /**
         * Use unique path generator
         */
        return $this->getUnusedPath($storeId, $requestPath.$suffix, $idPath);
    }
}