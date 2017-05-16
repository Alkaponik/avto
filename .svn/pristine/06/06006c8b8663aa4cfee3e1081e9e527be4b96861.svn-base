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

abstract class Testimonial_MageDoc_Model_Grid_Type_Supply_Abstract
    extends BL_CustomGrid_Model_Grid_Type_Abstract
{
    protected $_parentItemIdFieldName = 'order_item_id';
    
    public function checkUserEditPermissions($type, $model, $block=null, $params=array())
    {
        if (parent::checkUserEditPermissions($type, $model, $block, $params)) {
            return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/edit');
        }
        return false;
    }

    protected function _getEntityRowIdentifiersKeys($type)
    {
        return array('item_id');
    }

    protected function _loadEditedEntity($type, $config, $params)
    {
        if (isset($params['ids']['item_id'])) {
            $id = $params['ids']['item_id'];
            if (strpos($id, 'inquiry_') === 0){
                $modelName =  'magedoc/order_inquiry';
                $this->_parentItemIdFieldName = 'order_inquiry_id';
                $id = substr($id, strlen('inquiry_'));
            } else {
                $modelName = 'sales/order_item';
            }

            return Mage::getModel($modelName)->load($id);
        }
        return null;
    }

    protected function _getLoadedEntityName($type, $config, $params, $entity)
    {
        return $entity->getName();
    }

    protected function _checkEntityEditableField($type, $config, $params, $entity)
    {
        if (($config['id'] == 'price'
            || $config['id'] == 'cost')
            && !in_array($entity->getOrder()->getSupplyStatus(),
                array(
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::PENDING,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::CANCELED,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::MODIFIED,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::ARRANGED,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::RESERVED,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLING,
                    Testimonial_MageDoc_Model_Source_Order_Supply_Status::ASSEMBLED,
                ))){
            return false;
        }
        return true;
    }

    protected function _applyEditedFieldValue($type, $config, $params, $entity, $value)
    {
        $hlp = Mage::helper('magedoc');
        if ($config['id'] == 'price'
            || $config['id'] == 'cost') {
            $value = str_replace(' ', '', str_replace(',', '.', $value));
            if ($value <= 0) {
                Mage::throwException(Mage::helper('magedoc')->__('Value save failed: %s should be greater than zero', $config['id']));
            }
            $order = $entity->getOrder();
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()){
                $baseValue = Mage::helper('directory')->currencyConvert(
                    $value,
                    $order->getOrderCurrencyCode(),
                    $order->getBaseCurrencyCode());
            } else {
                $baseValue = $value;
            }
            $delta = $value - $entity->getData($config['id']);
            $baseDelta = $baseValue - $entity->getData('base_'.$config['id']);
            $marginSign = $config['id'] == 'price'
                ? 1
                : -1;
            $entity->setData('base_'.$config['id'], $baseValue);
            $order->setMargin($order->getMargin() + $marginSign * $delta * $entity->getQtyOrdered());
            $order->setBaseMargin($order->getBaseMargin() + $marginSign * $baseDelta * $entity->getQtyOrdered());
            if ($config['id'] == 'cost'){
                if ($entity->getQtyInvoiced() > 0){
                    $order->setBaseTotalInvoicedCost($order->getBaseTotalInvoicedCost() + $baseDelta * $entity->getQtyInvoiced());
                }
                $order->setBaseCost($order->getBaseCost() + $baseDelta * $entity->getQtyOrdered());
                $order->setCost($order->getCost() + $delta * $entity->getQtyOrdered());
            }
        }

        return parent::_applyEditedFieldValue($type, $config, $params, $entity, $value);
    }

    protected function _getSavedFieldValueForRender($type, $config, $params, $entity)
    {
        $value = parent::_getSavedFieldValueForRender($type, $config, $params, $entity);
        if (in_array($config['id'], array('cost', 'price'))){
            $value = Mage::app()->getStore($entity->getStoreId())->convertPrice($value, true, false);
        }
        return $value;
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
        $hlp = Mage::helper('magedoc');
        if (in_array($config['id'], array('cost', 'price'))){
            $order = $entity->getOrder();
            $message = $hlp->__('Item %s %s changed from %s to %s',
                $entity->getName(),
                $hlp->__($config['id']),
                $entity->getOrigData($config['id']),
                $entity->getData($config['id'])
            );
            $comment = $order->addStatusHistoryComment($message);
            $order->setLastStatusHistoryComment($message);
            $order->addRelatedObject($comment);

            $transaction = Mage::getModel('core/resource_transaction');
            $transaction->addObject($order);
            $baseValue = $entity->getData('base_'.$config['id']);
            //Mage::helper('magedoc')->saveStaticAttributes($order, array('margin', 'base_margin', 'base_total_invoiced_cost'));
            $itemsCollectionModel = $entity instanceof Testimonial_MageDoc_Model_Order_Inquiry
                ? 'magedoc/order_invoice_inquiry_collection'
                : 'sales/order_invoice_item_collection';
            $itemsCollection = Mage::getResourceModel($itemsCollectionModel)
                ->addFieldToFilter($this->_parentItemIdFieldName, $entity->getId());
            foreach ($itemsCollection as $item){
                $item->setData($config['id'], $value);
                $item->setData('base_'.$config['id'], $baseValue);
                if ($config['id'] == 'price'){
                    $item->setData($config['id'].'_incl_tax', $value);
                    $item->setData('base_'.$config['id'].'_incl_tax', $baseValue);
                }
                $transaction->addObject($item);
            }

            $transaction->addObject($entity);
            $transaction->save();
        } else {
            $entity->save();
        }

        return true;
    }
}
