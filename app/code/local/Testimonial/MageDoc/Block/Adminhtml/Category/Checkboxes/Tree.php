<?php

class Testimonial_MageDoc_Block_Adminhtml_Category_Checkboxes_Tree extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree
{

    protected function _prepareLayout()
    {
        $this->setTemplate('magedoc/category/checkboxes/tree.phtml');
    }
}
