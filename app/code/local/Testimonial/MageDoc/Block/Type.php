<?php

class Testimonial_MageDoc_Block_Type extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock('magedoc/breadcrumbs');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $type = $this->getVehicleType();
            if ($title = $type->getMetaTitle()) {
                $headBlock->setTitle($title);
            }
            if ($description = $type->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
            if ($keywords = $type->getMetaKeywords()) {
                $headBlock->setKeywords($keywords);
            }
            if ($this->helper('catalog/category')->canUseCanonicalTag()) {
                $headBlock->addLinkRel('canonical', $type->getUrl());
            }
        }

        return $this;
    }

    public function getVehicleType()
    {
        return $this->hasData('vehicle_type')
            ? $this->getData('vehicle_type')
            : Mage::registry('magedoc_type');
    }
}