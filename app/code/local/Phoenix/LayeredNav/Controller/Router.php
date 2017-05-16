<?php

class Phoenix_LayeredNav_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    const PARAM_REGEXP = "/^((?:\s+)?_+)(.*)/";
    const PARAM_REGEXP_TEMPLATE = "/^{{alias}}([^_].*)/";
    const ALIAS_PLACEHOLDER = '{{alias}}';

    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {

        /* @var $front Mage_Core_Controller_Varien_Front */
        $hlp = Mage::helper('phoenix_layerednav');
        $front = $observer->getEvent()->getFront();

        $request = $front->getRequest();

        $path = explode('/', trim($request->getPathInfo(), '/'));
        $moduleName     = isset($path[0]) ? $path[0] : 'core';

        if (!$hlp->isSeoUrlEnabled()
                || @class_exists('Maged_Controller')
                || $moduleName == (string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName')
                || $moduleName = 'catalog') {
            return;
        }
        $identifier = trim($request->getPathInfo(), '/');
        $sections = explode('/', $identifier);
        //print_r($sections);die;
        $sections = array_map('html_entity_decode', array_map('urldecode', $sections));
        $params = array();
        $filters = array();
        $paramName = null;
        foreach ($sections as $key => $value){
            if ($param = $this->_getParamFromString($value)){
                $paramName = $param['alias'];
                $paramValue = $param['value'];
                $filters[$paramName] = $value;
                unset($sections[$key]);
                if ($filter = Mage::helper('phoenix_layerednav')->getFilter($paramName,$paramValue)){
                    $params = array_merge($params,$filter);
                }
            }
        }
        $newIdentifier = implode('/',$sections);

        if ($identifier != $newIdentifier){
            Mage::register('orig_path_info', ltrim($request->getOriginalPathInfo(),'/'));
            Mage::register('orig_path_info_filters',implode('/',$filters));
            $request->setRequestUri(str_replace($identifier, $newIdentifier, $request->getRequestUri()));
            $request->setParams($params);
        }
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        return false;
    }

    /**
     * @todo use Helper::getFilterAlias() instead
     */

    protected function _getParamFromString($section)
    {
        $param = null;
        $map = Mage::helper('phoenix_layerednav')->getRequestVarAliases();
        if (strpos($section, '_') === false
                || !isset($map['request_var'])
                || !isset($map['alias'])){
            return $param;
        }

        foreach ($map['alias'] as $key => $alias){
            $regexp = str_replace(self::ALIAS_PLACEHOLDER, $alias, self::PARAM_REGEXP_TEMPLATE);
            if (preg_match($regexp, $section, $matches)){
                break;
            }
        }
        if (!empty($matches)){
            $param = array(
                'alias' => $alias,
                'request_var' => $map['request_var'][$key],
                'value' => $matches[1]
            );
        }
        return $param;
    }
}
