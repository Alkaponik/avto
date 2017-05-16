<?php

class Testimonial_MageDoc_Model_Retailer_Data_Update_Default
    extends Testimonial_MageDoc_Model_Retailer_Data_Update_Abstract
{
    
    protected function _getSourceParam($artId)
    {
        return false;
    }
    protected function _processProductResponse($response){}
    protected function _checkValidProductResponse($response){}
    protected function _processAuthResponse($response){}
}