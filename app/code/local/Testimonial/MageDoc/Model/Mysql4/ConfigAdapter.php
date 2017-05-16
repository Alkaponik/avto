<?php
class Testimonial_MageDoc_Model_Mysql4_ConfigAdapter extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magedoc/configadapter', 'id_config_adapter');
    }
}