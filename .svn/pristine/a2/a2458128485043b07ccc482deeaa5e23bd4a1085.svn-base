<?php
class Phoenix_GetCategoriesList_Block_Widget_Categories extends Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser
{
	public function __construct()
    {
		parent::__construct();
		$this->setTemplate('getcategorieslist/widget/categories.phtml');
    }
	
    
	public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('*/catalog_category_widget/chooser', array('uniq_id' => $uniqId, 'use_massaction' => false));

        $chooser = $this->getLayout()->createBlock('getcategorieslist/widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId)
            ->setLabel(' ');

        if ($element->getValue()) {
            $value = explode('/', $element->getValue());
            $categoryId = isset($value[1]) ? $value[1] : false;
            if ($categoryId) {
                $label = Mage::getSingleton('catalog/category')->load($categoryId)->getName();
                $chooser->setLabel($label);
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }
}