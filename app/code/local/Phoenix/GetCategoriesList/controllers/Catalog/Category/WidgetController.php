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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog category widgets controller for CMS WYSIWYG
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

require_once 'Mage/Adminhtml/controllers/Catalog/Category/WidgetController.php';

class Phoenix_GetCategoriesList_Catalog_Category_WidgetController extends Mage_Adminhtml_Catalog_Category_WidgetController
{
	protected $_isCallForThisModule;
	
	protected function _construct() {
		$this->_setIsCallForThisModule();
	}
	
	protected function _getCategoryTreeBlock()
    {
    	if (!$this->_getIsCallForThisModule()) {
    		return parent::_getCategoryTreeBlock();
    	}
    	
    	$selected_categories = $this->getRequest()->getParam('selected', array());
    	if (!empty($selected_categories) && !is_array($selected_categories)) {
    		if (strpos($selected_categories, ',')!==false) {
    			$selected_categories = explode(',', $selected_categories);
    		}
    		else {
    			$selected_categories = array($selected_categories);
    		}
    	}
    	
		return $this->getLayout()->createBlock('getcategorieslist/widget/categories.phtml', '', array(
            'id' => $this->getRequest()->getParam('uniq_id'),
            'use_massaction' =>true
        ))->setSelectedCategories($selected_categories);
    }
    
	protected function _getIsCallForThisModule() {
    	return $this->_isCallForThisModule;
    }
    
	protected function _setIsCallForThisModule() {
		if (preg_match('/^.*?\/catalog_category_widget\/.*?\/uniq_id\/options_fieldset[0-9a-z]{32}_categories[0-9a-z]{32}\/.*$/', $this->getRequest()->getPathInfo())) {
			$this->_isCallForThisModule = true;
		}
		else {
			$this->_isCallForThisModule = false;
		}
    }
}
