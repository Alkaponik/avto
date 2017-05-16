<?php

class Phoenix_Brands_BrandController extends Mage_Core_Controller_Front_Action
{
    public function listAction()
    {
        $this->loadLayout();
		$this->renderLayout();
    }

    /**
     * Initialize requested category object
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCatagory()
    {
        Mage::dispatchEvent('phoenixbrands_controller_brand_init_before', array('controller_action'=>$this));
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('phoenixbrands/catalog_category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
        Mage::register('current_category', $category);
        try {
            Mage::dispatchEvent('phoenixbrands_controller_brand_init_after', array('category'=>$category, 'controller_action'=>$this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }
        return $category;
    }

    /**
     * Category view action
     */
    public function viewAction()
    {

        if ($category = $this->_initCatagory()) {

            Mage::getModel('catalog/design')->applyDesign($category, Mage_Catalog_Model_Design::APPLY_FOR_CATEGORY);
            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();

            //$update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('catalog_category_brand_layered');
            $update->addHandle('CATEGORY_'.$category->getId());



            if ($category->getPageLayout()) {
                    $this->getLayout()->helper('page/layout')
                        ->applyHandle($category->getPageLayout());
            }

            $this->loadLayoutUpdates();

            $update->addUpdate($category->getCustomLayoutUpdate());

            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($category->getPageLayout()) {
                $this->getLayout()->helper('page/layout')
                    ->applyTemplate($category->getPageLayout());
            }

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-'.$category->getUrlPath())
                    ->addBodyClass('category-'.$category->getUrlKey());
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        }
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }



}