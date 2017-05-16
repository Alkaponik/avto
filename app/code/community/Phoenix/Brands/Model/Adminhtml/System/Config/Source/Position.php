<?php
class Phoenix_Brands_Model_Adminhtml_System_Config_Source_Position
{
	public function toOptionArray() 
	{
		return array(
		    array('value' => '', 'label' => 'No change'),
		    array('value' => 'left_before', 'label' => 'Move to left top'),
		    array('value' => 'left_after', 'label' => 'Move to left bottom'),
		    array('value' => 'right_before', 'label' => 'Move to right top'),
		    array('value' => 'right_after', 'label' => 'Move to right bottom'),
		    array('value' => 'remove', 'label' => 'Do not show')
		);
	}
}