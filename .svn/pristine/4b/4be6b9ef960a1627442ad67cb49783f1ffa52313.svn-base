<?php

class MageDoc_OrderExport_ConnectorController extends Mage_Core_Controller_Front_Action
{
    /**
     * Run order export
     */
	public function exportOrderAction()
	{
        $mode = isset($_GET['mode']) ? $_GET['mode'] : null;
        echo chr(239) . chr(187) . chr(191);
        switch ($mode){
            case 'checkauth':
                echo "success\nsessionID\ntemp";
                die;
            case 'init':
                echo "zip=no";
                die;
            default:
            Mage::getModel('magedoc_orderexport/export_order_1c')->exportOrder();
                die;
        }
    }

    public function exportCatalogAction()
    {
        Mage::getModel('magedoc_orderexport/export_product_1c')
            ->setRetailerId($this->getRequest()->getParam('retailer_id'))
            ->exportCatalog();
    }
}