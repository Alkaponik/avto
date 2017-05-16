<?php

/**
 * CSV import adapter
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Model_Import_Adapter_Csvp extends Mage_ImportExport_Model_Import_Adapter_Csv
{
    /**
     * Field delimiter.
     *
     * @var string
     */
    //protected $_delimiter = ';';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    //protected $_enclosure = '"';

    protected $_defaultColValues = array(
        '_type'                                     => 'simple',
        'status'                                    => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
        'visibility'                                => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        'is_in_stock'                               => 0,
        '_store'                                    => 1,
        '_attribute_set'                            => 'Default',
        'tax_class_id'                              => 2,
        //'short_description'                         => '',
        //'weight'                                    => 0
    );

    public function current()
    {
        return array_merge(parent::current(), $this->_defaultColValues);
    }
    
}
