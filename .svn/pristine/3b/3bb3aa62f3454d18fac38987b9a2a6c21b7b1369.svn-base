<?php

class Testimonial_FlatCatalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_contentsEntityMap;

    public function  __construct()
    {
        $this->_initContentsEntityMap();
    }

    protected function _initContentsEntityMap()
    {
        $this->_contentsEntityMap = array(
                1	=>	 $this->__('unverpackt'),
                2	=>	 $this->__('Ballon'),
                3	=>	 $this->__('Becher'),
                4	=>	 $this->__('Beutel'),
                5	=>	 $this->__('Dose'),
                6	=>	 $this->__('Eime'),
                7	=>	 $this->__('Etu'),
                8	=>	 $this->__('Faß'),
                9	=>	 $this->__('Flasche'),
                10	=>	 $this->__('Kanister'),
                11	=>	 $this->__('Kann'),
                12	=>	 $this->__('Kartusche'),
                13	=>	 $this->__('Kasten'),
                14	=>	 $this->__('Kiste'),
                15	=>	 $this->__('Korb'),
                16	=>	 $this->__('Sac'),
                17	=>	 $this->__('Schachtel'),
                18	=>	 $this->__('Schale'),
                19	=>	 $this->__('Tiegel'),
                20	=>	 $this->__('Tube'),
                21	=>	 $this->__('Versandrohr'),
                22	=>	 $this->__('Weithalsglas'),
                99	=>	 $this->__('Sonstige')
        );
        return $this;
    }

    public function getContentsEntity($packageType)
    {
        return $packageType
                && isset($this->_contentsEntityMap[$packageType])
                ? $this->_contentsEntityMap[$packageType]
                : '';
    }

    public function isUrlRewritesEnabled()
    {
        return false;
    }
}
