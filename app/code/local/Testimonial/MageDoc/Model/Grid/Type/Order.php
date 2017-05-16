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

class Testimonial_MageDoc_Model_Grid_Type_Order
    extends BL_CustomGrid_Model_Grid_Type_Order
{
    public function isAppliableToGrid($type, $rewritingClassName)
    {
        return ($type == 'magedoc/adminhtml_order_grid');
    }

    public function checkUserEditPermissions($type, $model, $block=null, $params=array())
    {
        if (parent::checkUserEditPermissions($type, $model, $block, $params)) {
            return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/edit');
        }
        return false;
    }

    protected function _getBaseEditableFields($type)
    {
        $helper = Mage::helper('magedoc');

        $fields = array(
            'last_status_history_comment' => array(
                'type'     => 'text',
                'required' => true,
            ),
        );

        return $fields;
    }

    protected function _getEntityRowIdentifiersKeys($type)
    {
        return array('entity_id');
    }

    protected function _loadEditedEntity($type, $config, $params)
    {
        if (isset($params['ids']['entity_id'])) {
            return Mage::getModel('magedoc/order')->load($params['ids']['entity_id']);
        }
        return null;
    }

    protected function _getLoadedEntityName($type, $config, $params, $entity)
    {
        return $entity->getIncrementId();
    }

    /**
     * Save edited entity
     *
     * @param string $type Grid block type
     * @param array $config Edited field config
     * @param array $params Edit parameters
     * @param mixed $entity Edited entity
     * @param mixed $value Edited field value
     * @return bool
     */
    protected function _saveEditedFieldValue($type, $config, $params, $entity, $value)
    {
        if ($config['id'] == 'last_status_history_comment'){
            $comment = $entity->addStatusHistoryComment($value);
            $entity->addRelatedObject($comment);
        }
        $entity->save();

        return true;
    }
}
