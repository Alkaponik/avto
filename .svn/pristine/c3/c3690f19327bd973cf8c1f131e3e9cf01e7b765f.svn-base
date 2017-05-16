<?php

class Testimonial_MageDoc_Block_Model extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock('magedoc/breadcrumbs');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $manufacturer = $this->getModel();
            if ($title = $manufacturer->getMetaTitle()) {
                $headBlock->setTitle($title);
            }
            if ($description = $manufacturer->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
            if ($keywords = $manufacturer->getMetaKeywords()) {
                $headBlock->setKeywords($keywords);
            }
            if ($this->helper('catalog/category')->canUseCanonicalTag()) {
                $headBlock->addLinkRel('canonical', $manufacturer->getUrl());
            }
        }

        return $this;
    }

    public function getModel()
    {
        return $this->hasModel()
            ? $this->getData('model')
            : Mage::registry('model');
    }

    public function getTypeCollection()
    {

    }
}