<?php
class Testimonial_Avtoto_Model_Autopricing_Process
{
    const AUTOPRICING_PROCESS_STATUSES_CONFIG_PATH = 'global/autopricing/process';
    protected $_processes = null;

    public function getProcesses()
    {
        if(is_null($this->_processes)) {
            $this->_processes = Mage::getConfig()->getNode( static::AUTOPRICING_PROCESS_STATUSES_CONFIG_PATH )->asArray();
        }

        return $this->_processes;
    }

    public function _runProcess( $processCode )
    {
        $processArray = $this->getProcesses();
        if(!isset($processArray[$processCode])) {
            Mage::throwException(Mage::helper('avtoto')->__('Wrong process code'));
        }
        $process = $processArray[$processCode];
        if(isset($process['model']) && $process['model']) {

            $model = Mage::getModel($process['model']);
            call_user_func( array($model, $process['method']) );

        }
        return $this;
    }

    public function runProcess( $processCode )
    {
        $statusCollection =
            Mage::getSingleton('avtoto/autopricing_process_status')
                ->getCollection();

        $statusCollection->addFieldToFilter('process_code', $processCode);
        if(!$status = $statusCollection->fetchItem()) {
            $status = Mage::getModel('avtoto/autopricing_process_status');
            $status->setData('process_code', $processCode);
        }

        $status->addData(
            array(
                 'status'       => Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_RUNNING,
                 'started_at'   => date('Y-m-d H:i:s')
            )
        );
        $status->save();

        try{
            Mage::getSingleton('avtoto/autopricing_process')->_runProcess( $status->getProcessCode() );
            $status->addData(
                array(
                    'status'     => Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_PENDING,
                    'ended_at'   => date('Y-m-d H:i:s')
                )
            );
        } catch( Exception $e ) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('avtoto')
                        ->__(
                            'Error on process run (process code: %s). Message: %s',
                            $status->getProcessCode(),
                            $e->getMessage()
                        )
                );
            $status->setStatus(Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_FAILED);
        }

        $status->save();
    }

}