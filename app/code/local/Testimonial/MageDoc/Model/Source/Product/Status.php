<?php

class Testimonial_MageDoc_Model_Source_Product_Status extends Mage_Catalog_Model_Product_Status
{
    public function toOptionArray()
    {
        $res = array();
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
                'value' => $index,
                'label' => $value
            );
        }
        return $res;
    }
}