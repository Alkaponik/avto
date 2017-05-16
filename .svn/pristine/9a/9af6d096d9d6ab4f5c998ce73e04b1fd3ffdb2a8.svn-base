<?php
class Testimonial_MageDoc_Model_Source_Retailer_Data_Import_Session_Status
{
    protected $_pathToXml = 'default/magedoc/import_session/status';
    protected $_options = array();

    public function getAllOptions()
    {
        if( empty($this->_options) ) {
            foreach (Mage::getConfig()->getNode($this->_pathToXml)->children() as $type) {
                $labelPath = $this->_pathToXml . '/' . $type->getName() . '/label';
                $valuePath = $this->_pathToXml . '/' . $type->getName() . '/value';
                $value = (string) Mage::getConfig()->getNode($valuePath);
                $this->_options[$value] = Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath));
            }
        }
        return $this->_options;
    }

}