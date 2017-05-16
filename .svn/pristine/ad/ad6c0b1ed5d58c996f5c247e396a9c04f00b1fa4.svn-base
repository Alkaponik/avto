<?php

class Ak_NovaPoshta_Model_Source_Tracking_Stage extends Ak_NovaPoshta_Model_Source_Tracking_Abstract
{
    function __construct()
    {
        $this->_pathToXml = 'default/carriers/novaposhta/tracking/stage';
    }

    const STAGE_ON_SENDER_TRANSIT              = 0;
    const STAGE_ON_SENDER_WAREHOUSE_NOT_LOADED = 1;
    const STAGE_IN_TRANSIT                     = 2;
    const STAGE_ON_DESTINATION_TERMINAL        = 3;
    const STAGE_ON_DESTINATION_TRANSIT         = 4;
    const STAGE_ON_DESTINATION_WAREHOUSE       = 5;
    const STAGE_REGISTERED_NOT_LOADED          = 6;
    const STAGE_AGENT_PICKUP                   = 7;

    public function getStage()
    {
        return $this->getAllOptions();
    }
}