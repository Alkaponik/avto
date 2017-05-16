<?php
class Testimonial_Avtoto_Model_Autopricing_Process_Status extends Mage_Core_Model_Abstract
{
    const STATUS_RUNNING            = 'working';
    const STATUS_PENDING            = 'pending';
    const STATUS_REQUIRE_REINDEX    = 'require_reindex';
    const STATUS_FAILED             = 'failed';


    protected function _construct()
    {
        $this->_init('avtoto/autopricing_process_status');
    }

    public function getStatusesOptions()
    {
        return array(
            self::STATUS_PENDING            => Mage::helper('index')->__('Ready'),
            self::STATUS_RUNNING            => Mage::helper('index')->__('Processing'),
            self::STATUS_REQUIRE_REINDEX    => Mage::helper('index')->__('Reindex Required'),
            self::STATUS_FAILED             => Mage::helper('index')->__('Failed'),
        );
    }

    public function getUpdateRequiredOptions()
    {
        return array(
            0 => Mage::helper('index')->__('No'),
            1 => Mage::helper('index')->__('Yes'),
        );
    }

    public function getStatus()
    {
        if($this->getProcessCode() == 'retailer_price_updated') {
            $retailerCollection = Mage::getSingleton('magedoc/retailer')
                ->getCollection()
                ->addFieldToFilter('is_import_enabled', 1)
                ->addFieldToFilter('enabled', 1);

            $result = self::STATUS_PENDING;
            foreach($retailerCollection as $retailer) {
                if( $retailer->isPriceUpdateValid() ) {
                    $result =  self::STATUS_REQUIRE_REINDEX;
                    break;
                }
            }

            $currentStatus = parent::getStatus();
            if($currentStatus != $result) {
                $this->setEndedAt(date('Y-m-d H:i:s'))
                    ->setStatus($result)
                    ->save();

            }
            return $result;
        }

        return parent::getStatus();
    }



}
