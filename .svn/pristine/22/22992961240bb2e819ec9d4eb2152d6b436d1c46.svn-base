<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Catalog'.DS.'ProductController.php';


class Testimonial_MageDoc_Adminhtml_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    const MAX_QTY_VALUE = 99999999.9999;

    protected $_publicActions = array('edit');
    
    protected $_articleCollection;

    protected function _getArticleCollection()
    {
        if(!isset($this->_articleCollection)){
            $this->_articleCollection = Mage::getResourceModel('magedoc/tecdoc_article_collection')
                    ->joinProductsWithCategory()
                    ->joinAttribute('name');
        }
        return $this->_articleCollection;
    }
   
    protected function _initProduct()
    {
        $this->_title($this->__('MageDoc'))
             ->_title($this->__('Manage Products'));

        $artId  = (int) $this->getRequest()->getParam('id');
        $model = Mage::getModel('catalog/product');
        
        $product = $model->loadByAttribute('td_art_id', $artId);
        $storeId = $this->getRequest()->getParam('store', 0);
        $store = Mage::app()->getStore($storeId);
        
        if (!$product) {
            $product = $model;
            $product->setTypeId('spare');
            $product->setAttributeSetId('4');

            $collection = $this->_getArticleCollection();
            $collection->addFieldToFilter('main_table.ART_ID', $artId);

            $article = $collection->fetchItem();

            $layoutModel = Mage::getModel('core/layout');
            /** @var $linkArtShort Testimonial_Magedoc_Block_LinkArt */
            $linkArtShort = $layoutModel->createBlock('magedoc/linkArt')
                ->setTemplate('magedoc/product/usedincars_short.phtml')
                ->setArea('frontend');

            $data = array(
                "product_id"        => $article->getData('entity_id'),
                "sku"               => $article->getData('supplier').'-'.str_replace(' ', '', $article->getData('art_article_nr')),
                "_attribute_set"    => "Default",
                "_type"             => "spare",
                "name"              => $article->getData('name'),
                "description"       => $article->getData('name'),
                "meta_title"        => $article->getData('name'),
                "meta_keyword"      => $article->getData('name'),
                "meta_description"  => $article->getData('name'),
                'store_id'          => $storeId,
                "status"            => "1",
                "visibility"        => "4",
                "weight"            => "0",
                "is_in_stock"       => "1",
                'website_id'        => $store->getWebsiteId(),
                "manage_stock"      => "0",
                "_store"            => $storeId,
                "tax_class_id"      => "2",
                '_category'         => $article->getData('category_id'),
                'supplier_name'     => $article->getData('supplier_title'),
                'supplier'          => $article->getData('art_sup_id'),
                'td_sup_id'         => $article->getData('art_sup_id'),
                'td_art_id'         => $article->getData('art_id')
            );
            $product->addData($data);
            $product->setShortDescription($linkArtShort->setProduct($product)->toHtml());
            $data = $product->getData();
            $product->addData(Mage::helper('magedoc')->getEntityDefaultValues('product', $data, $storeId));
        }
        
        $product->setStoreId($storeId);

      
        if ($this->getRequest()->getParam('popup')
            && $requiredAttributes = $this->getRequest()->getParam('required')) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $product;
    }

    
    public function newAction()
    {
        $product = $this->_initProduct();

        $this->_title($this->__('Add Product'));

        Mage::dispatchEvent('magedoc/adminhtml_product_new_action', array('product' => $product));
        
        if ($this->getRequest()->getParam('popup')) {
            
            $this->loadLayout('popup');
            
        } else {
            
            $_additionalLayoutPart = '';
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                && !($product->getTypeInstance()->getUsedProductAttributeIds()))
            {
                $_additionalLayoutPart = '_new';
            }
            $this->loadLayout(array(
                'default',
                strtolower($this->getFullActionName()),
                'magedoc/adminhtml_product_'.$product->getTypeId() . $_additionalLayoutPart
            ));
            $this->_setActiveMenu('magedoc/products');
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->renderLayout();
    }

    public function editAction()
    {
        $productId  = (int) $this->getRequest()->getParam('id');
        $product = $this->_initProduct();
        
        if ($productId && !$product) {
            Mage::getSingleton('core/session')->setArtId($productId);
            $this->_redirect('*/*/new/');
            return;
        }

        $this->_title($product->getName());
        Mage::dispatchEvent('catalog_product_edit_action', array('product' => $product));

        $_additionalLayoutPart = '';
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            && !($product->getTypeInstance()->getUsedProductAttributeIds())){
                $_additionalLayoutPart = '_new';
        }

        $this->loadLayout();

        $this->_setActiveMenu('magedoc/products');

        if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current'=>true, 'active_tab'=>null, 'tab' => null, 'store'=>null))
                );
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/products');
    }
}