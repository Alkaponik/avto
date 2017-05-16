<?php
class Testimonial_Avtoto_Model_Resource_Autopricing_Process_Status_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('avtoto/autopricing_process_status');
    }

    public function setItems( $items )
    {
        $this->_items = $items;
        return $this;
    }
}