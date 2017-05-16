<?php
class Phoenix_Brands_Model_Catalog_Category extends Mage_Catalog_Model_Category
{
    const BLOCK_TYPE_BRAND = 1;
    const BLOCK_TYPE_COLLECTION = 2;

    /**
     * Retrieve logo image URL
     *
     * @return string
     */
    public function getLogoImageUrl()
    {
        $url = false;
        if ($image = $this->getLogoImage()) {
            $url = Mage::getBaseUrl('media') . 'catalog/category/' . $image;
        }
        return $url;
    }

    /**
     * Retrieve seo image URL
     *
     * @return string
     */
    public function getSeoImageUrl()
    {
        $url = false;
        if ($image = $this->getSeoImage()) {
            $url = Mage::getBaseUrl('media') . 'catalog/category/' . $image;
        }
        return $url;
    }

    protected function _afterSave()
    {
        if ($this->getBlockType() != self::BLOCK_TYPE_BRAND) {
            return parent::_afterSave();
        }
        $this->updateBrandRewrite();
        return parent::_afterSave();
    }

    public function updateBrandRewrite()
    {
        //if ($this->getBlockType() != self::BLOCK_TYPE_BRAND) {
        //    return;
        //}
        //$stores = Mage::getResourceModel('catalog/url')->getStores();
        //foreach (Mage::getResourceModel('catalog/url')->getStores() as $store) {
        //    $model = Mage::getModel('core/url_rewrite');
        //    $model->setStoreId($store->getId());
        //    $model->loadByIdPath($this->getBrandRewriteIdPath());
        //    if (!$model->getId() || $this->getBrandRewriteRequestPath() != $model->getRequestPath()) {
        //        $model->setIdPath($this->getBrandRewriteIdPath())
        //            ->setTargetPath($this->getBrandRewriteIdPath())
        //            ->setOptions(null)
        //            ->setRequestPath($this->getBrandRewriteRequestPath());
        //        $model->save();
        //    }
        //}
    }
    public function getBrandRewriteIdPath()
    {
        return 'phoenixpbrands/brand/view/id/' . $this->getId(). '/';
    }

    public function getBrandRewriteRequestPath()
    {
        if (!$this->getUrlKey()) {
            $this->load($this->getId());
        }
        return 'shop/' . $this->getUrlKey();
    }

    public function getBrandCategoryUrl()
    {   
        $model = Mage::getModel('core/url_rewrite');
        $model->loadByIdPath($this->getBrandRewriteIdPath());
        if ($model->getId()) {
            return Mage::getModel('core/url')->getUrl() . $model->getRequestPath();
        } else {
            return Mage::getModel('core/url')->getUrl('phoenixbrands/brand/view', array('id'=>$this->getId()));
        }
    }
}