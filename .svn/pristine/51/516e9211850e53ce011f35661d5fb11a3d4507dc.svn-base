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
 * @package    Phoenix_Supercheckout
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_Multipletablerates_Model_Source_Transport_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const SHIPPING_TRANSPORT_TYPES_PATH = 'global/phoenix_multipletablerates/shipping/transport/types';
    const TRANSPORT_TYPE_FREE = 1;
    const TRANSPORT_TYPE_PARCEL = 2;
    const TRANSPORT_TYPE_EXPRESS = 3;
    const TRANSPORT_TYPE_CONVEYANCE = 4;

    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach (Mage::getConfig()->getNode(self::SHIPPING_TRANSPORT_TYPES_PATH)->children() as $type) {
                $labelPath = self::SHIPPING_TRANSPORT_TYPES_PATH . '/' . $type->getName() . '/label';
                $valuePath = self::SHIPPING_TRANSPORT_TYPES_PATH . '/' . $type->getName() . '/value';
                $value = (string) Mage::getConfig()->getNode($valuePath);
                $this->_options[$value] = array(
                    'label' => Mage::helper('phoenix_multipletablerates')->__((string) Mage::getConfig()->getNode($labelPath)),
                    'value' => $value
                );
            }
            ksort($this->_options);
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value'=>'',
                'label'=>Mage::helper('core')->__('-- Please Select --'))
            );
        }
        return $options;
    }

    public function toOptionArray()
    {
        $types = array();

        foreach (Mage::getConfig()->getNode(self::SHIPPING_TRANSPORT_TYPES_PATH)->children() as $type) {
            $labelPath = self::SHIPPING_TRANSPORT_TYPES_PATH . '/' . $type->getName() . '/label';
            $valuePath = self::SHIPPING_TRANSPORT_TYPES_PATH . '/' . $type->getName() . '/value';
            $value = (string) Mage::getConfig()->getNode($valuePath);
            $types[$value] = array(
                'label' => Mage::helper('phoenix_multipletablerates')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $value
            );
        }
        ksort($types);

        return $types;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attribute = $this->getAttribute();
        switch ($attribute->getBackendType()) {
            case 'static':
                $describe = $attribute->_getResource()
                    ->describeTable($attribute->getBackend()->getTable());
                if (!isset($describe[$attribute->getAttributeCode()])) {
                    break;
                }
                $prop = $describe[$attribute->getAttributeCode()];
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => $prop['DATA_TYPE'] . ($prop['LENGTH'] ? "({$prop['LENGTH']})" : ""),
                    'unsigned'  => $prop['UNSIGNED'] ? true: false,
                    'is_null'   => $prop['NULLABLE'],
                    'default'   => $prop['DEFAULT'],
                    'extra'     => null
                );
                break;
            case 'datetime':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'datetime',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'decimal':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'decimal(12,4)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'int':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'int',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'text':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'text',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
            case 'varchar':
                $columns[$attribute->getAttributeCode()] = array(
                    'type'      => 'varchar(255)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
                break;
        }
        return $columns;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select
     */
    public function getFlatUpdateSelect($store) {
        if (is_null($store)) {            
            return $this->getAttribute();
        }

        if ($this->getAttribute()->getBackendType() == 'static') {
            return null;
        }

        return $this->getAttribute()->getResource()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
    
}
