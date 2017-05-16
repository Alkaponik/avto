<?php

class Testimonial_MageDoc_Model_Source_Product_Visibility extends Mage_Catalog_Model_Product_Visibility
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