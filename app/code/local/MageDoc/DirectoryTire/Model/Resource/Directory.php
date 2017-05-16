<?php
class MageDoc_DirectoryTire_Model_Resource_Directory extends Testimonial_MageDoc_Model_Mysql4_Directory_Abstract
{
    const DIRECTORY_CODE = 'tire';

    public function getSupplierOptions( $conditions = null )
    {
        return parent::getSupplierOptions(array('correct_brand_name_id = ?'=> 0));
    }
}