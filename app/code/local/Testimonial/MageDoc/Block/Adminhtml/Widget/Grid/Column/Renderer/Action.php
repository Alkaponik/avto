<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp;';
        }

        if((sizeof($actions)==1 || $this->getColumn()->getMultipleLinks()) && !$this->getColumn()->getNoLink()) {
            $result = '';
            foreach ($actions as $action) {
                if ( is_array($action) ) {
                    $result .= $this->_toLinkHtml($action, $row) . '<br/>';
                }
            }
            return $result;
        }

        $out = '<select class="action-select" onchange="varienGridAction.execute(this);">'
            . '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action){
            $i++;
            if ( is_array($action) ) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }

    protected function _transformActionData(&$action, &$actionCaption, Varien_Object $row)
    {
        parent::_transformActionData($action, $actionCaption, $row);    
        $typeId = $this->getColumn()->getTypeId() !== null ? $this->getColumn()->getTypeId() : '';
        if($row->getData($this->getColumn()->getArtId()) !== null){
            $productId = '';
            $artId = $row->getData($this->getColumn()->getArtId());
        }else{
            $productId = $row->getId();
            $artId = '';
        }
        if (empty($action['onclick']) || !empty($action['popup'])) {
            $action['onclick'] = "MageDoc_Adminhtml_Product_Information_WindowJsObject.getProductData('"
                . $productId . "', '" . $artId . "', '" . $typeId .
                "'); var event = arguments[0] || window.event; Event.stop(event); return false;";
        }else{
            preg_match_all('/\{\{([\w_]+)\}\}/ism', $action['onclick'], $placeholders);
            if (!$placeholders) die;
            //print_r($placeholders);die;
            if ($placeholders){
                $placeholders = $placeholders[1];
            }
            foreach ($placeholders as $placeholder){
                $action['onclick'] = str_replace("{{{$placeholder}}}", $row->getData($placeholder), $action['onclick']);
            }
        }
        return $this;
    }
}