<?php

class Testimonial_Avtoto_Model_Resource_Product extends Mage_Catalog_Model_Resource_Product
{
    protected function _getDefaultAttributes()
    {
        return array('productID', 'categoryID', 'name', 'description', 'customers_rating', 'cost', 'Price', 'retailer_id', 'retailer_price', 'in_stock', 'customer_votes', 'items_sold', 'enabled', 'brief_description', 'list_price', 'product_code', 'sort_order', 'default_picture', 'date_added', 'date_modified', 'viewed_times', 'eproduct_filename', 'eproduct_available_days', 'eproduct_download_times', 'weight', 'meta_description', 'meta_keywords', 'free_shipping', 'min_order_amount', 'shipping_freight', 'classID', 'count_mod_pr', 'clear_description', 'avto_links_deskr', 'avtomarks', 'product_code_normalized', 'ART_ID');
    }
}