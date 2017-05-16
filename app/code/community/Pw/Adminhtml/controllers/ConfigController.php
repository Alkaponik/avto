<?php
class Pw_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Enter description here...
     *
     */
    protected function _construct()
    {
        $this->setFlag('index', 'no-preDispatch', true);
        return parent::_construct();
    }

    public function exportAction()
    {
        $websiteModel = Mage::app()->getWebsite($this->getRequest()->getParam('website'));

        if ($this->getRequest()->getParam('conditionName')) 
        {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } 
        else 
        {
            $conditionName = $websiteModel->getConfig('carriers/multipletablerates/condition_name');
        }
        
        $tableratesCollection = Mage::getResourceModel('multipletablerates_shipping/carrier_multipletablerates_collection');
        $tableratesCollection->setConditionFilter($conditionName);
        $tableratesCollection->setWebsiteFilter($websiteModel->getId());
        $tableratesCollection->load();

        $csv = '';

        $conditionName = Mage::getModel('multipletablerates_shipping/carrier_multipletablerates')->getCode('condition_name_short', $conditionName);

        $csvHeader = array(
        	'"'.Mage::helper('adminhtml')->__('Country').'"', 
        	'"'.Mage::helper('adminhtml')->__('Region/State').'"', 
        	'"'.Mage::helper('adminhtml')->__('Zip/Postal Code').'"', 
        	'"'.$conditionName.'"', 
        	'"'.Mage::helper('adminhtml')->__('Shipping Price').'"',
        	'"'.Mage::helper('adminhtml')->__('Method Code').'"',
        	'"'.Mage::helper('adminhtml')->__('Method Name').'"',
        	'"'.Mage::helper('adminhtml')->__('Method Description').'"',
        	'"'.Mage::helper('adminhtml')->__('Condition Type').'"'
        	);
        	
        $csv .= implode(',', $csvHeader)."\n";

        foreach ($tableratesCollection->getItems() as $item) 
        {
            if ($item->getData('dest_country') == '') 
            {
                $country = '*';
            } 
            else 
            {
                $country = $item->getData('dest_country');
            }
            
            if ($item->getData('dest_region') == '') 
            {
                $region = '*';
            } 
            else 
            {
                $region = $item->getData('dest_region');
            }
            
            if ($item->getData('dest_zip') == '') 
            {
                $zip = '*';
            } 
            else 
            {
                $zip = $item->getData('dest_zip');
            }

            $csvData = array(
            	$country, 
            	$region, 
            	$zip, 
            	$item->getData('condition_value'), 
            	$item->getData('price'),
            	$item->getData('method_code'),
            	$item->getData('method_name'),
            	$item->getData('method_description'),
            	$item->getData('condition_type')
            	);
            	
            foreach ($csvData as $cell) 
            {
                $cell = '"'.str_replace('"', '""', $cell).'"';
            }
            
            $csv .= implode(',', $csvData)."\n";
        }

        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=multipletablerates.csv");
        echo $csv;
        exit;
    }
}      