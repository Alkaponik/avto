<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import_Session extends Mage_Core_Model_Abstract
{
    const SESSION_STATUS_PENDING = 1;
    const SESSION_STATUS_PROCESSING = 2;
    const SESSION_STATUS_COMPLETE = 3;
    const SESSION_STATUS_CANCELED = 4;
    const SESSION_STATUS_FAILED = 5;

    protected $_import = null;
    protected $_retailer = null;
    protected $_errors = array();

    /** @var  Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Session_Source_Collection|null  */
    protected $_sourceCollection = null;

    protected function _afterSave()
    {
        foreach ($this->getSources() as $source){
            $source->setSessionId($this->getId())->save();
        }
        return parent::_afterSave();
    }

    protected function _beforeSave()
    {
        if(!empty($this->getErrorMessages())){
            $this->setMessages($this->getErrorMessages());
        }
        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        if (empty($this->_errors) && !empty($this->getMessages())) {
            $this->_errors = $this->getMessages();
        }
        return parent::_afterLoad();
    }

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_session');
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer
     */

    public function getRetailer()
    {
        if(is_null($this->_retailer)){
            $this->_retailer = Mage::getModel('magedoc/retailer')->load( $this->getRetailerId() );
        }

        return $this->_retailer;
    }

    public function setRetailer( $retailer )
    {
        $this->_retailer = $retailer;
        $this->setRetailerId($retailer->getId());

        return $this;
    }

    public function isActive()
    {
        return in_array($this->getStatusId(), $this->getActiveStatuses());
    }

    public function isFailed()
    {
        return $this->getStatusId() == self::SESSION_STATUS_FAILED;
    }

    public function getActiveStatuses()
    {
        return array( self::SESSION_STATUS_PENDING, self::SESSION_STATUS_PROCESSING );
    }

    public function setStatus( $status )
    {
        $this->setStatusId( $status );
        $this->setUpdatedAt( Varien_Date::now() );

        return $this;
    }

    public function getSources()
    {
        if(is_null($this->_sourceCollection)) {
            $this->_sourceCollection = Mage::getResourceModel('magedoc/retailer_data_import_session_source_collection')
                ->addFieldToFilter('session_id', $this->getId())
                ->addOrder('source_id', Varien_Data_Collection_Db::SORT_ORDER_ASC)
                ->join(
                    array ('import_config' => 'magedoc/retailer_data_import_adapter_config'),
                    'import_config.config_id = main_table.config_id',
                    'name'
            );
        }

        return $this->_sourceCollection;
    }

    public function addSource( $source )
    {
        $source->setSession( $this );
        $this->getSources()->addItem($source);

        $this->_hasDataChanges = true;

        return $this;
    }

    public function getLastSource()
    {
        return $this->getSources()->getLastItem();
    }

    public function prepare()
    {
        $this->addData(
            array(
                'total_records' => 0,
                'valid_records' => 0,
                'records_with_old_brands' => 0,
                'old_brands'      => 0,
                'total_brands'    => 0,
                'new_brands'      => 0,
                'records_linked_to_directory' => 0,
            )
        )
        ->setStatus( Testimonial_MageDoc_Model_Retailer_Data_Import_Session::SESSION_STATUS_PROCESSING )
        ->save();

        return $this;
    }

    public function cancel()
    {
        $this->setStatus( self::SESSION_STATUS_CANCELED )
            ->save();

        return $this;
    }

    public function fail()
    {
        $this->setStatus( self::SESSION_STATUS_FAILED )
            ->save();

        return $this;
    }

    public function complete()
    {
        $this->setStatus( self::SESSION_STATUS_COMPLETE )
            ->save();

        return $this;
    }

    public function addError($errorCode, $errorRowNum, $colName = null, $data = array())
    {
        $this->_errors []= implode(', ', $data);
        $this->_hasDataChanges = true;
        return $this;
    }
    /**
     * Returns error information grouped by error types and translated (if possible).
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->_errors;
    }

    public function getSourcesForPreview( $directoryCode )
    {
        $result = array();
        $sources = $this->getSources();
        foreach($sources as $key => $source) {
            if($source->getConfig()->getUpdateByKey() === '0' &&
                $source->getParser()->getDirectoryCode() == $directoryCode
            ) {
                $result[$key] = $source;
            }
        }

        return $result;
    }

    public function getMetricChangePercent($metricCode)
    {
        $session = $this;
        $previousSession = $session->getRetailer()->getPreviousSession();
        return $previousSession->getDataUsingMethod($metricCode)
            ? round(($session->getDataUsingMethod($metricCode) - $previousSession->getDataUsingMethod($metricCode)) * 100
                / $previousSession->getDataUsingMethod($metricCode), 2)
            : 0;
    }

    public function getInvalidRecordsCount()
    {
        return $this->getTotalRecords() - $this->getValidRecords();
    }

    public function getInvalidRecordsPercent()
    {
        return ($this->getTotalRecords())
            ? round($this->getInvalidRecordsCount() * 100 / $this->getTotalRecords(), 2)
            : 0 ;
    }

    public function getValidRecordsPercent()
    {
        return $this->getTotalRecords()
            ? round($this->getValidRecords() * 100 / $this->getTotalRecords(), 2)
            : 0;
    }

    public function getlinkedToSupplierPercent()
    {
        return $this->getTotalRecords()
            ? round($this->getRecordsWithOldBrands() * 100 / $this->getTotalRecords(), 2)
            : 0;
    }

    public function getlinkedToDirectoryPercent()
    {
        return ($this->getTotalRecords())
            ? round($this->getRecordsLinkedToDirectory() * 100 / $this->getTotalRecords(), 2)
            : 0;
    }

    public function getNewBrandPercent()
    {
        return $this->getOldBrands()
            ? round($this->getNewBrands() * 100 / $this->getOldBrands(), 2)
            : 100;
    }
}