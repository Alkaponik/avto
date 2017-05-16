<?php

class Testimonial_MageDoc_Block_Adminhtml_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
    protected function _prepareLayout()
    {
        $category = $this->getCategory();
        $categoryId = (int) $category->getId(); // 0 when we create category, otherwise some value for editing category

        $this->setChild('tabs',
            $this->getLayout()->createBlock('magedoc/adminhtml_category_tabs', 'tabs')
        );

        // Save button
        if (!$category->isReadonly()) {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save Category'),
                    'onclick'   => "categorySubmit('" . $this->getSaveUrl() . "', true)",
                    'class' => 'save'
                ))
            );
        }

        // Delete button
        if (!in_array($categoryId, $this->getRootIds()) && $category->isDeleteable()) {
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Delete Category'),
                    'onclick'   => "categoryDelete('" . $this->getUrl('*/*/delete', array('_current' => true)) . "', true, {$categoryId})",
                    'class' => 'delete'
                ))
            );
        }

        // Reset button
        if (!$category->isReadonly()) {
            $resetPath = $categoryId ? '*/*/edit' : '*/*/add';
            $this->setChild('reset_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Reset'),
                    'onclick'   => "categoryReset('".$this->getUrl($resetPath, array('_current'=>true))."',true)"
                ))
            );
        }

        return Mage_Adminhtml_Block_Catalog_Category_Abstract::_prepareLayout();
    }
}
