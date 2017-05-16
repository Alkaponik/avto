<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Get daqshboard management url
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit/section/oro_dashboard');
    }

    /**
     * ACL validation before html generation
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('system/config/oro_dashboard')) {
            return parent::_toHtml();
        }
        return '';
    }
}
