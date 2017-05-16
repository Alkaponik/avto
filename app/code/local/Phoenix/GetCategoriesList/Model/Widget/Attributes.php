<?php
class Phoenix_GetCategoriesList_Model_Widget_Attributes {
	public function toOptionArray() {
		$result = array();
		
		$product_attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')
			->setFrontendInputTypeFilter('boolean')
			->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
			->getData();
			
		if (!is_array($product_attribute_collection)) {
			return $result;
		}
		
		foreach ($product_attribute_collection as $attr_crt) {
			$result[] = array(
				'value' => $attr_crt['attribute_code'], 
				'label' => $attr_crt['attribute_code']
			);
		}
		
		return $result;
	}
}