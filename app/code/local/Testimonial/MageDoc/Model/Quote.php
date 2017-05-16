<?php

class Testimonial_MageDoc_Model_Quote extends Mage_Sales_Model_Quote
{
    protected $_vehicles = null;
    protected $_inquiries = null;

    protected function _construct()
    {
        $this->_init('sales/quote');
    }
    
    public function addVehicle(Varien_Object $vehicle)
    {
        if ($vehicle instanceof Testimonial_MageDoc_Model_Order_Vehicle){
            $oldVehicleData = $vehicle->getData();
            $vehicle = Mage::getModel('magedoc/quote_vehicle');
            $vehicle->setData($oldVehicleData)
                    ->unsVehicleId();
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $vehicle->setStoreId($this->getStore()->getId());
        }
        else {
            $vehicle->setStoreId(Mage::app()->getStore()->getId());
        }
        
        $vehicle->setQuote($this);
        $this->getVehiclesCollection()->addItem($vehicle);
        
        return $vehicle;

    }

    public function getVehiclesCollection($useCache = true)
    {
        if (is_null($this->_vehicles)) {
            $this->_vehicles = Mage::getModel('magedoc/quote_vehicle')->getCollection();
            $this->_vehicles->setQuote($this);
        }
        return $this->_vehicles;
    }
    
    public function getAllVehicles()
    {
        $vehicles = array();
        foreach ($this->getVehiclesCollection() as $vehicle) {
            if (!$vehicle->isDeleted()) {
                $vehicles[] = $vehicle;
            }
        }
        return $vehicles;
    }

    public function getInquiriesCollection($useCache = true)
    {
        if (is_null($this->_inquiries)) {
            
            $this->_inquiries = Mage::getModel('magedoc/quote_inquiry')->getCollection();
            $this->_inquiries->setQuote($this);
        }
        return $this->_inquiries;
    }
    
    /**
     * Retrieve quote items array
     *
     * @return array
     */
    public function getAllInquiries()
    {
        $inquiries = array();
        /*foreach ($this->getInquiriesCollection() as $inquiry) {
            if (!$inquiry->isDeleted()) {
                $inquiries[] =  $inquiry;
            }
        }*/
        foreach ($this->getAllVehicles() as $vehicle) {
            $inquiries = array_merge($inquiries, $vehicle->getAllInquiries());
        }
        return $inquiries;
    }
    
    /**
     * Init mapping array of short fields to
     * its full names
     *
     * @return Varien_Object
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        $this->getVehiclesCollection()->save();

        return $this;
    }


    /**
     * Adding catalog product object data to quote
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item
     */
    protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        if ($buyRequest = $product->getCustomOption('info_buyRequest')){
            $buyRequest = unserialize($buyRequest->getValue());
        }
        if (is_array($buyRequest)){
            if (isset($buyRequest['retailer_id'])){
                $product->setRetailerId($buyRequest['retailer_id']);
            }
            if (isset($buyRequest['cost'])){
                $product->setCustomCost($buyRequest['cost']);
            }
            if (isset($buyRequest['price'])){
                $product->setCustomPrice($buyRequest['price']);
            }
        }

        $newItem = false;
        $item = $this->getItemByProduct($product);
        if (!$item ||
            $product->hasRetailerId() && $product->getRetailerId() != $item->getRetailerId()) {
            $item = Mage::getModel('sales/quote_item');
            $item->setQuote($this);
            if (Mage::app()->getStore()->isAdmin()) {
                $item->setStoreId($this->getStore()->getId());
            }
            else {
                $item->setStoreId(Mage::app()->getStore()->getId());
            }
            $newItem = true;
        }

        $item->setRetailerId($product->getRetailerId());
        $item->setCustomCost($product->getCustomCost());
        $item->setCustomPrice($product->getCustomPrice() != $product->getPrice() ? $product->getCustomPrice() : null);
        $item->setOriginalCustomPrice($product->getCustomPrice() != $product->getPrice() ? $product->getCustomPrice() : null);

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions())
            ->setProduct($product);

        // Add only item that is not in quote already (there can be other new or already saved item
        if ($newItem) {
            $this->addItem($item);
        }

        return $item;
    }

    /**
     * Updates quote item with new configuration
     *
     * $params sets how current item configuration must be taken into account and additional options.
     * It's passed to Mage_Catalog_Helper_Product->addParamsToBuyRequest() to compose resulting buyRequest.
     *
     * Basically it can hold
     * - 'current_config', Varien_Object or array - current buyRequest that configures product in this item,
     *   used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file options (file inputs), so they won't
     *   intersect with other submitted options
     *
     * For more options see Mage_Catalog_Helper_Product->addParamsToBuyRequest()
     *
     * @param int $itemId
     * @param Varien_Object $buyRequest
     * @param null|array|Varien_Object $params
     * @return Mage_Sales_Model_Quote_Item
     *
     * @see Mage_Catalog_Helper_Product::addParamsToBuyRequest()
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = $this->getItemById($itemId);
        if (!$item) {
            Mage::throwException(Mage::helper('sales')->__('Wrong quote item id to update configuration.'));
        }
        $productId = $item->getProduct()->getId();

        //We need to create new clear product instance with same $productId
        //to set new option values from $buyRequest
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStore()->getId())
            ->load($productId);

        if (!$params) {
            $params = new Varien_Object();
        } else if (is_array($params)) {
            $params = new Varien_Object($params);
        }
        $params->setCurrentConfig($item->getBuyRequest());
        $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest($buyRequest, $params);

        $buyRequest->setResetCount(true);
        $resultItem = $this->addProduct($product, $buyRequest);
        if (is_string($resultItem)) {
            Mage::throwException($resultItem);
        }

        if ($resultItem->getParentItem()) {
            $resultItem = $resultItem->getParentItem();
        }

        if ($resultItem->getId() != $itemId) {
            /*
             * Product configuration didn't stick to original quote item
             * It either has same configuration as some other quote item's product or completely new configuration
             */
            $this->removeItem($itemId);
            $items = $this->getAllItems();
            foreach ($items as $item) {
                if (($item->getProductId() == $productId) && ($item->getId() != $resultItem->getId())) {
                    if ($resultItem->compare($item)) {
                        // Product configuration is same as in other quote item
                        $resultItem->setQty($resultItem->getQty() + $item->getQty());
                        $this->removeItem($item->getId());
                        break;
                    }
                }
            }
        } else {
            $resultItem->setQty($buyRequest->getQty());
        }

        return $resultItem;
    }

    public function setGmtShippingDate($date)
    {
        $date = Mage::getModel('core/date')->gmtDate($date);
        $this->setShippingDate($date);
    }
}
