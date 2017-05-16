<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Form_Session_Stat
    extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magedoc/retailer/import/data/session/stat.phtml');
    }

    public function getSessionToDisplay()
    {
        /** @var Testimonial_MageDoc_Model_Retailer $retailer */
        $retailer = Mage::registry('retailer');
        if($retailer->hasActiveSession()) {
            return $retailer->getActiveSession();
        }

        return $retailer->getLastFailedSession();
    }

    protected function _getPriceFilesList()
    {
        $sources = $this->getSessionToDisplay()->getSources();
        $fileList = array();
        foreach($sources as $item) {
            $sourcePathArray = explode( DS, $item['source_path']);
            $fileList[] = end($sourcePathArray)." ({$item->getName()})";
        }
        return implode('<br>' , $fileList);
    }

    protected function _toHtml()
    {
        if (!$this->getSessionToDisplay()){
            return '';
        }
        return parent::_toHtml();
    }
}