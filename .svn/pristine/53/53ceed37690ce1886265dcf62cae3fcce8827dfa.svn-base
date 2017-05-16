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

class Testimonial_MageDoc_Model_Grid_Type_Supply_Document
    extends Testimonial_MageDoc_Model_Grid_Type_Supply_Abstract
{
    public function isAppliableToGrid($type, $rewritingClassName)
    {
        return ($type == 'magedoc/adminhtml_supply_document_grid');
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
            'price' => array(
                'type'       => 'text',
                'required'   => true,
                'form_class' => 'validate-not-negative-number'
            ),
            'retailer_id' => array(
                'type'         => 'select',
                'required'     => true,
                'form_options' => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
            ),
            'supply_date' => array(
                'type'         => 'date',
            ),
            'receipt_reference' => array(
                'type'         => 'text',
            ),
            'return_reference' => array(
                'type'         => 'text',
            ),
        );

        return $fields;
    }
}
