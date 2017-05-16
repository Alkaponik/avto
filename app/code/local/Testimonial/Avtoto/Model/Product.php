<?php

class Testimonial_Avtoto_Model_Product extends Mage_Catalog_Model_Product
{
    /**
     * Saving product type related data and init index
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _afterSave()
    {
        $this->getLinkInstance()->saveProductRelations($this);
        $this->getTypeInstance(true)->save($this);

        /**
         * Product Options
         */
        $this->getOptionInstance()->setProduct($this)
            ->saveOptions();

        $result = Mage_Catalog_Model_Abstract::_afterSave();

        return $result;
    }
}
