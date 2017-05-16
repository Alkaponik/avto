<?php
class Testimonial_MageDoc_Adminhtml_Product_InformationController 
        extends Mage_Adminhtml_Controller_Action
{
    public function requestAction()
    {
        $data = array();
        $product = Mage::getModel('catalog/product');
        if($typeId = $this->getRequest()->getPost('type_id')){
            Mage::register('current_magedoc_type_ids', $typeId);
        }
        if($productId = $this->getRequest()->getPost('product_id')){
            $product->load($productId);
        }elseif($artId = $this->getRequest()->getPost('art_id')){
            $article = Mage::getModel('magedoc/tecdoc_article')->load($artId);
            $product->setTdArtId($artId)
                ->setSupplier($article->getArtSupId());
        }else{
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
            return;
        }
        Mage::register('product', $product);
        $priceBlock = $this->getLayout()->createBlock('magedoc/adminhtml_retailer_price');

        $data['general'] = $this->getLayout()->createBlock('magedoc/adminhtml_product_information')->toHtml();
        $data['general'] .= $priceBlock->toHtml();
        if ($product->getTdArtId()) {
            $data['general'] .= $this->getLayout()->createBlock('magedoc/adminhtml_product_criteria')->toHtml();
            $data['similar'] =
                $this->getLayout()->createBlock('magedoc/adminhtml_artLookup')->toHtml();
            $data['used_in_cars'] =
                $this->getLayout()->createBlock('magedoc/adminhtml_linkArt')->toHtml();
            $data['images'] =
                $this->getLayout()->createBlock('magedoc/adminhtml_product_image')->toHtml();
        }
        $data['retialer_prices'] = $priceBlock->getPricesArray();
        $data['art_id'] = $product->getTdArtId();
//        print_r($data['retialer_prices']); die;

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }
    
    
    public function updatePriceAction()
    {        
        if (!$retailerId = $this->getRequest()->getPost('retailer_id', false)){
            $this->getResponse()->setBody('false');
            return;
        }
        if(!$artId = $this->getRequest()->getPost('art_id', false)){
            $this->getResponse()->setBody('false');
            return;
        }

        $retailer = Mage::helper('magedoc/price')->getRetailerById($retailerId);

        if($retailer->getUpdateModel()->requestProductData($artId)){        
            $product = Mage::getModel('magedoc/import_retailer_data')
                ->loadByAttributeSet(array(
                    'td_art_id'     => $artId,
                    'retailer_id'   => $retailerId,
                ));

            if($product->getId()){
                $this->getResponse()->setBody(json_encode(array($retailerId => $product->getData())));
            }
        }else{
            $this->getResponse()->setBody('false');
        }
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }
}