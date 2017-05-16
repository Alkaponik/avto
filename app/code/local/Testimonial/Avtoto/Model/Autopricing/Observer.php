<?php
class Testimonial_Avtoto_Model_Autopricing_Observer
{
    public function updateStatusesOfPriceDependedProcesses()
    {
        $priceDependedProcesses = array (
            'retailer_prices_syncronized',
            'catalog_updated',
            'flatcatalog_final_price_update',
            'shop_price_updated',
        );
        $this->_updateStatusesOfProcesses( $priceDependedProcesses );
    }

    public function updateStatusesOfRetailerDependedProcesses()
    {
        $retailerDependedObservers =  array('retailers_syncronized');
        $this->_updateStatusesOfProcesses( $retailerDependedObservers );
    }

    protected function _updateStatusesOfProcesses( $processCodes )
    {
        foreach($processCodes as $processCode)
        {
            Mage::getModel('avtoto/autopricing_process_status')
                ->load($processCode, 'process_code')
                ->setProcessCode($processCode)
                ->setStatus(Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_REQUIRE_REINDEX)
                ->save();
        }
    }

}