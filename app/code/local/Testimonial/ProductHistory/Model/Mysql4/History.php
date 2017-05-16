<?
class Testimonial_ProductHistory_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract{
    protected function _construct()
    {
        $this->_init('producthistory/history', 'producthistory_id');
    }   
}
