<?php
class Phoenix_GetCategoriesList_Block_Widget_Chooser extends Mage_Widget_Block_Adminhtml_Widget_Chooser
{
	protected function _toHtml()
    {
        $element   = $this->getElement();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset  = $element->getForm()->getElement($this->getFieldsetId());
        $chooserId = $this->getUniqId();
        $config    = $this->getConfig();

        // add chooser element to fieldset
        $chooser = $fieldset->addField('chooser' . $element->getId(), 'label', array(
            'label'       => $config->getLabel() ? $config->getLabel() : '',
            'value_class' => '',
        ));
        $hiddenHtml = '';
        if ($this->getHiddenEnabled()) {
            $hidden = new Varien_Data_Form_Element_Hidden($element->getData());
            $hidden->setId("{$chooserId}value")->setForm($element->getForm());
            $hiddenHtml = $hidden->getElementHtml();
            $element->setValue('');
        }

        $buttons = $config->getButtons();
        $chooseButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setId($chooserId . 'control')
            ->setClass('btn-chooser')
            ->setLabel($buttons['open'])
            ->setOnclick($chooserId.'.choose()');
        $chooser->setData('after_element_html', $hiddenHtml . $chooseButton->toHtml());

        // render label and chooser scripts
        $configJson = Mage::helper('core')->jsonEncode($config->getData());
        return '
            <script type="text/javascript">
           		WysiwygWidget.chooser.prototype.choose = function(event) {
			        // Show or hide chooser content if it was already loaded
			        var responseContainerId = this.getResponseContainerId();
			        
			        categories_checked = document.getElementById("'.$this->getFieldsetId().'_catsselected").getAttribute("value").split(",");
			        var parameters_for_eval = "{element_value: this.getElementValue(), element_label: this.getElementLabelText()";
			        if (categories_checked.length>0) {
			        	parameters_for_eval += \', "selected[]": [\' + categories_checked.join(",")+ "]";
				    }
			        parameters_for_eval += "};";
			        eval("var parameters = "+parameters_for_eval);
			
			        // Otherwise load content from server
			        new Ajax.Request(this.chooserUrl,
			            {
			                parameters: parameters,
			                onSuccess: function(transport) {
			                    try {
			                        widgetTools.onAjaxSuccess(transport);
			                        this.dialogContent = widgetTools.getDivHtml(responseContainerId, transport.responseText);
			                        this.openDialogWindow(this.dialogContent);
			                    } catch(e) {
			                        alert(e.message);
			                    }
			                }.bind(this)
			            }
			        );
			    }
                '.$chooserId.' = new WysiwygWidget.chooser("'.$chooserId.'", "'.$this->getSourceUrl().'", '.$configJson.');
            </script>
            <label class="widget-option-label" id="'.$chooserId . 'label">'.($this->getLabel() ? $this->getLabel() : Mage::helper('widget')->__('Not Selected')).'</label>
        ';
    }
}