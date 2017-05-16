<?php
class Testimonial_MageDoc_Model_Directory extends Mage_Core_Model_Abstract
{
    const DIRECTORIES_CONFIG_XML_PATH = 'global/magedoc/directory';
    protected $_directories = array();

    protected function _construct()
    {
        $this->_init('magedoc/directory');
    }

    public function getDirectory( $directory = null)
    {
        if (!$directory){
            $directory = Mage::helper('magedoc')->getDefaultDirectoryCode();
        }
        if(isset($this->_directories[$directory])) {
            return $this->_directories[$directory];
        }

        $config = Mage::getConfig()->getNode(static::DIRECTORIES_CONFIG_XML_PATH . '/' . $directory);
        if($config) {
            $config = $config->asArray();
            if(isset($config['model'])) {
                $this->_directories[$directory] = Mage::getSingleton($config['model']);
                if($this->_directories[$directory]) {
                    $this->_directories[$directory]->addData($config)->setCode($directory);
                }
                return $this->_directories[$directory];
            }
        }
        return false;
    }
}