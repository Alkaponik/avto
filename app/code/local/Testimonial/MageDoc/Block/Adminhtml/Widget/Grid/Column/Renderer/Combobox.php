<?php
class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Combobox
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function _getComboboxHtml($row)
    {
        $columnComboboxStorageJSName = $this->getColumn()->getGrid()->getId() . '_' .
                $this->getColumn()->getId() . 'ComboboxStorage';
        $html = "<div class=\"combo-container {$this->getClass()}\" id=\"{$this->getContainerId($row)}\">
            <input type=\"text\" class=\"combo-input form-combo-input\" autocomplete=\"off\" value=\"{$this->getTextValue($row)}\" name=\"{$this->getInputName($row)}\" {$this->getDisabled()} />
            <select class=\"combo-select\" size=\"{$this->getSelectSize()}\" name=\"{$this->getSelectName($row)}\" {$this->getDisabled()} tabindex=\"-1\">" .
            $this->_getValuesHtml( $row )
            . "</select>
            </div>
            <script type=\"text/javascript\">
                {$this->getContainerId($row)}_combobox = new Combobox('{$this->getContainerId($row)}', '{$this->getColumn()->getSourceUrl()}', '', {}, '{$this->_getValue($row)}', " . json_encode($this->getSettings(), JSON_FORCE_OBJECT). ");
                if(typeof $columnComboboxStorageJSName === 'undefined') {
                    $columnComboboxStorageJSName = [];
                }

                $columnComboboxStorageJSName.push({$this->getContainerId($row)}_combobox);
            </script>";
        return $html;
    }

    protected function _getValuesHtml( $row )
    {
        $html = '';
        if($this->_getValue($row)) {
            $html .= "<option value=\"{$this->_getValue($row)}\" selected=\"selected\">{$this->getTextValue($row)}</option>";
        }
        return $html;
    }

    public function render( Varien_Object $row )
    {
        return $this->_getComboboxHtml($row);
    }

    public function getContainerId($row)
    {
        return str_replace(array('[',']'), '_',$this->getName($row) );
    }


    public function getSettings()
    {
        return is_array($this->getColumn()->getSettings()) ? $this->getColumn()->getSettings() : array();
    }

    public function getSelectSize()
    {
        if($this->getData('select_size') === null){
            $this->setSelectSize(Mage::helper('magedoc')->getDefaultComboboxSelectSize());
        }
        return $this->getData('select_size');
    }

    public function getDisabled()
    {
        if(!$this->hasData('disabled')){
            $this->setDisabled('');
        }
        return $this->getData('disabled') == 'disabled'
            ? 'disabled="disabled"' : '';
    }

    public function getValue(  )
    {
        return 1;
    }

    public function getInputName($row)
    {
        return "text_{$this->getName($row)}";
    }

    public function getSelectName($row)
    {
        return $this->getName($row);
    }

    public function getName($row)
    {
        if($this->getColumn()->getName() === null){
            $name = "{$this->getColumn()->getGrid()->getId()}[{$row->getId()}][{$this->getColumn()->getIndex()}]";
            return $name;
        }
        return $this->getColumn()->getName();
    }

    public function getTextValue( $row )
    {
        if ($this->getColumn()->getTextIndex()){
            return isset($row[$this->getColumn()->getTextIndex()])
                ? $row[$this->getColumn()->getTextIndex()]
                : '';
        }
        $options = $this->getColumn()->getInternalOptions();
        return isset($options[$this->_getValue($row)]) ? $options[$this->_getValue($row)] : '';
    }

    public function getClass()
    {
        return $this->getColumn()->getInlineCss() ? $this->getColumn()->getInlineCss() : '';
    }
}