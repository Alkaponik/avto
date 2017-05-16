<?php

class Testimonial_MageDoc_Model_Adminhtml_Url extends Mage_Adminhtml_Model_Url
{

    /**
     * Custom logic to retrieve Urls
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function getUrl($routePath=null, $routeParams=null)
    {
        $result = parent::getUrl($routePath, $routeParams);

        $_route = $this->getRouteName() ? $this->getRouteName() : '*';
        $_controller = $this->getControllerName() ? $this->getControllerName() : $this->getDefaultControllerName();
        $_action = $this->getActionName() ? $this->getActionName() : $this->getDefaultActionName();

        $routeParams = $this->getRouteParams();

        if ($_route == 'adminhtml' && in_array($_controller, array('sales_order', 'sales_order_invoice', 'sales_order_shipment', 'sales_order_creditmemo'))){
            $_route = 'magedoc';
        }

        return parent::getUrl("{$_route}/{$_controller}/{$_action}", $routeParams);
    }
}
