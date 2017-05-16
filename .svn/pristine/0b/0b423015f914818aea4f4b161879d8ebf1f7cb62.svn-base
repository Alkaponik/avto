<?php
class Testimonial_MageDoc_Block_Adminhtml_Directory_Switcher extends Mage_Adminhtml_Block_Template
{
    const DEFAULT_DIRECTORY = 'tecdoc';

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/directory/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    /**
     * Get directoriew
     *
     * @return array
     */
    public function getDirectories()
    {
        return Mage::getSingleton('magedoc/source_directory')->getOptionArray();
    }

    public function getSwitchUrl()
    {
        $url = $this->getData('switch_url');
        if (is_null($url)) {
            $url = '*/*/*';
        }
        $params =  array('_current' => true, 'directory' => '{directory}');
        if($this->getIsIntoTab()) {
            $params = array_merge($params, array('tab' => '{tab}'));
        }
        return $this->getUrl($url, $params);
    }

    public function getSelectedDirectory()
    {
        return $this->getRequest()->getParam('directory') ? : static::DEFAULT_DIRECTORY;
    }
}