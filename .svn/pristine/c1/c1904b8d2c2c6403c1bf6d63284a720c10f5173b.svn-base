<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   BL
 * @package    BL_CustomGrid
 * @copyright  Copyright (c) 2012 Benoît Leulliette <benoit.leulliette@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Testimonial_MageDoc_Model_Grid_Type_Supply_Item
    extends Testimonial_MageDoc_Model_Grid_Type_Supply_Abstract
{
    public function isAppliableToGrid($type, $rewritingClassName)
    {
        return ($type == 'magedoc/adminhtml_supply_item_grid');
    }

    protected function _getBaseEditableFields($type)
    {
        $helper = Mage::helper('magedoc');

        $fields = array(
            'cost' => array(
                'type'      => 'text',
                'required'  => true,
                'form_class' => 'validate-not-negative-number',
            ),
        );

        return $fields;
    }

    protected function _loadEditedEntity($type, $config, $params)
    {
        if (isset($params['ids']['item_id'])) {
            $id = $params['ids']['item_id'];
            return Mage::getModel('sales/order_item')->load($id);
        }
        return null;
    }
}
