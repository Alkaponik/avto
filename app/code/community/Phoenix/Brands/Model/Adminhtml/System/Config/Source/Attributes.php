<?php
class Phoenix_Brands_Model_Adminhtml_System_Config_Source_Attributes 
{
	public function toOptionArray() 
	{
		$result = array();
		
		$productAttributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
			->getData();
			
		if (!is_array($productAttributeCollection)) {
			return $result;
		}
		
		$result[] = array('value' => 0,	'label' => '');
		foreach ($productAttributeCollection as $attributeCrt) {
			$result[] = array(
				'value' => $attributeCrt['attribute_code'], 
				'label' => $attributeCrt['attribute_code']
			);
		}
		
		return $result;
	}
}