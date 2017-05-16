<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @product    Phoenix
 * @package    Phoenix_Multipletablerates
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_Multipletablerates_Block_Adminhtml_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{    
    protected function _getAdditionalElementTypes()
    {
        $types = array(
            'multipletablerates_export'        => Mage::getConfig()->getBlockClassName('multipletablerates_adminhtml/system_config_form_field_export')
        );
        return array_merge(parent::_getAdditionalElementTypes(), $types);
    }
}
