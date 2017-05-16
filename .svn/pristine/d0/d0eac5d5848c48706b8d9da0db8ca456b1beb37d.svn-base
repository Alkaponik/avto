<?php

class Testimonial_System_Block_Adminhtml_Widget_Grid_Column_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    protected $_templateRegexp = '/\{\{(.*?)\}\}/';

    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function __render(Varien_Object $row)
    {
        if (Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract::_getValue($row) === null
            && !$this->_hasActionValues($row)){
            return '&nbsp;';
        }
        return parent::render($row);
        
    }

    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if (Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract::_getValue($row) === null
            && !$this->_hasActionValues($row)){
            return '&nbsp;';
        }

        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp;';
        }

        if(sizeof($actions)==1 && !$this->getColumn()->getNoLink() || $this->getColumn()->getForceLinks()) {
            $links = array();
            foreach ($actions as $action) {
                if ( is_array($action)
                        && $this->_getActionValue($row, $action)) {
                    //return $this->_toLinkHtml($action, $row);
                    $links[] = $this->_toLinkHtml($action, $row);
                }
            }
            return implode('<br/>', $links);
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

    protected function _hasActionValues($row)
    {
        if (($actions = $this->getColumn()->getActions())
            && is_array($actions)){
            foreach ($actions as $action){
                if ($this->_getActionValue($row, $action)){
                    return true;
                }
            }
        }
        return false;
    }

    protected function _getActionValue(Varien_Object $row, $action)
    {
        if ($getter = (isset($action['getter']) ? $action['getter'] : null)) {
            if (is_string($getter)) {
                return $row->$getter();
            } elseif (is_callable($getter)) {
                return call_user_func($getter, $row);
            }
            return '';
        }
        return $row->getData($this->getColumn()->getIndex());
    }

    /**
     * Render single action as link html
     *
     * @param array $action
     * @param Varien_Object $row
     * @return string
     */
    protected function _toLinkHtml($action, Varien_Object $row)
    {
        $actionAttributes = new Varien_Object();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        if(isset($action['confirm'])) {
            $action['onclick'] = 'return window.confirm(\''
                               . addslashes($this->htmlEscape($action['confirm']))
                               . '\')';
            unset($action['confirm']);
        }elseif (isset($action['prompt'])){
            $action['onclick'] = 'javascript:var comment = window.prompt(\''
                               . addslashes($this->htmlEscape($action['prompt']))
                               . '\', this.title.replace(\'%s\', this.up(\'tr\').down(\'td.comment\').innerHTML.replace(/^\s+|\s+$/g, \'\')));
                                 if (comment !== null) window.location = \''.$action['href'].'\' + \'comment\\\\\'+comment;
                               return false;';
            unset($action['prompt']);
        }

        $actionAttributes->setData($action);
        if ($actionAttributes->hasData('href') || $actionAttributes->hasData('onclick')){
            return '<a ' . $actionAttributes->serialize() . '>' . $actionCaption . '</a>';
        }else {
            return $actionCaption;
        }
    }

    /**
     * Prepares action data for html render
     *
     * @param array $action
     * @param string $actionCaption
     * @param Varien_Object $row
     * @return Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
     */
    protected function _transformActionData(&$action, &$actionCaption, Varien_Object $row)
    {
        $helper = Mage::helper('testimonial_system');
        foreach ( $action as $attribute => $value ) {
            if(isset($action[$attribute])
                && !is_array($action[$attribute])
                && !is_object($action[$attribute])) {
                $this->getColumn()->setFormat($action[$attribute]);
                $action[$attribute] = Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text::render($row);
            } else {
                $this->getColumn()->setFormat(null);
            }

            switch ($attribute) {
                case 'caption':
                    $actionCaption = $helper->processTemplate($action['caption'], $row);
                    unset($action['caption']);
                    break;

                case 'url':
                    if(is_array($action['url'])) {
                        $params = array();
                        if (isset($action['field'])){
                            if (isset($action['value_index'])){
                                $value = $row->getData($action['value_index']);
                                $params[$action['field']] = $value;
                            }else{
                                $params[$action['field']] = $this->_getActionValue($row, $action);
                            }
                        }
                        if(isset($action['url']['params'])) {
                            $params = array_merge($action['url']['params'], $params);
                        }
                        foreach ($params as $key => $value){
                            $params[$key] = $helper->processTemplate($value, $row);
                        }
                        if (!isset($action['value_index']) || !empty($value)){
                            $action['href'] = $helper->processTemplate($this->getUrl($action['url']['base'], $params), $row);
                        }
                        unset($action['field']);
                    } else {
                        $action['href'] = $helper->processTemplate($action['url'], $row);
                    }
                    unset($action['url']);
                    break;

                case 'popup':
                    $action['onclick'] =
                        'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,scrollbars=1\');return false;';
                    break;
                case 'getter':
                    unset($action['getter']);
                    break;
                default:
                    if (preg_match($this->_templateRegexp, $action[$attribute])){
                        $action[$attribute] = $helper->processTemplate($action[$attribute], $row);
                    }
            }
        }
        return $this;
    }
}
