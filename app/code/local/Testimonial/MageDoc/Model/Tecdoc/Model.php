<?php


class Testimonial_MageDoc_Model_Tecdoc_Model extends Testimonial_MageDoc_Model_Abstract
{
    protected static $_models = array();
    protected $_types;

    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_model');
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    public function getFullName()
    {
        return $this->getMfaBrand() . ' ' . $this->getModCdsText();
    }

    /**
     * Get product url model
     *
     * @return Testimonial_MageDoc_Model_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === null) {
            $this->_urlModel = Mage::getSingleton('magedoc/url');
        }
        return $this->_urlModel;
    }

    public function getUrl($useSid = null)
    {
        return $this->getUrlModel()->getModelUrl($this, $useSid);
    }

    public function getManufacturer()
    {
        return Mage::getSingleton('magedoc/tecdoc_manufacturer')->factory($this->getModMfaId());
    }

    public function factory($modelId)
    {
        if (!isset(self::$_models[$modelId])){
            self::$_models[$modelId] = new self();
            self::$_models[$modelId]
                ->isPartialLoad(false)
                ->load($modelId);
        }
        return self::$_models[$modelId];
    }

    public function getProductionPeriod()
    {
        return Mage::helper('magedoc')->getProductionPeriod($this, 'mod');
    }

    public function getTypeCollection()
    {
        if (!isset($this->_types)){
            $this->_types = Mage::getResourceModel('magedoc/tecdoc_type_collection')
                ->joinTypeDesignation()
                ->addModelFilter($this->getId());
        }
        return $this->_types;
    }
}
