<?php
class  Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Config
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/retailer/import/adapter/edit/config.phtml');
    }

    public function isReadonly()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label'  => Mage::helper('customer')->__('Delete Address'),
                               'name'   => 'delete_address',
                               'element_name' => 'delete_address',
                               'disabled' => $this->isReadonly(),
                               'class'  => 'delete' . ($this->isReadonly() ? ' disabled' : '')
                          ))
        );
        $this->setChild('add_config_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label'  => Mage::helper('customer')->__('Add New Config'),
                               'id'     => 'add_config_button',
                               'name'   => 'add_config_button',
                               'element_name' => 'add_config_button',
                               'disabled' => $this->isReadonly(),
                               'class'  => 'add'  . ($this->isReadonly() ? ' disabled' : ''),
                               'onclick'=> 'customerConfigs.addNewConfig()'
                          ))
        );
        $this->setChild('cancel_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label'  => Mage::helper('customer')->__('Cancel'),
                               'id'     => 'cancel_add_address'.$this->getTemplatePrefix(),
                               'name'   => 'cancel_address',
                               'element_name' => 'cancel_address',
                               'class'  => 'cancel delete-address'  . ($this->isReadonly() ? ' disabled' : ''),
                               'disabled' => $this->isReadonly(),
                               'onclick'=> 'customerConfigs.cancelAdd(this)',
                          ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Initialize form object
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
     */
    public function initForm()
    {
        $retailer = Mage::registry('retailer');
        /** @var  $configCollection Testimonial_MageDoc_Model_Mysql4_Retailer_Import_Adapter_Config_Collection */
        $configCollection = $retailer->getImportConfigCollection();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'config_fieldset',
            array(
                'legend' => Mage::helper('customer')->__("Edit Retailer Import Adapter Config")
            )
        );

        $fieldset->addField(
            'name', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Config name'),
                'name'  => 'name',
                'class' => 'required-entry',
                'required'  => true,
            )
        );

        $fieldset->addField(
            'adapter_model', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Source adapter'),
                'name'  => 'adapter_model',
                'class' => 'required-entry',
                'required'  => true,
                'values'    =>  Mage::getModel('magedoc/source_import_model_adapter')->getOptionArray()
            )
        );
        $fieldset->addField(
            'parser_model', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Parser model'),
                'name'  => 'parser_model',
                'class' => 'required-entry',
                'required'  => true,
                'values'    =>  Mage::getModel('magedoc/source_import_model_parser')->getOptionArray()
            )
        );

        $fieldset->addField('price_encoding', 'select',
            array(
                'label'     => Mage::helper('magedoc')->__('Price encoding'),
                'name'      => 'price_encoding',
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => array('UTF-8'=>'Unicode (utf-8)', 'cp1251'=>'ANSI (windows-1251)')
            )
        );

        $fieldset->addField(
            'source_adapter_config', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Source adapter options'),
                'name'  => 'source_adapter_config',
            )
        );

        $fieldset->addField(
            'source_fields_map', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Source fields map'),
                'name'  => 'source_fields_map',
                'required'    => true,
                'class' => 'required-entry',
                'cols_titles' => array(
                    Mage::helper('magedoc')->__('Base table field'),
                    Mage::helper('magedoc')->__('Price field'),
                    Mage::helper('magedoc')->__('Action'),
                )
            )
        );

        $fieldset->addField(
            'source_fields_filters', 'text',
            array(
                 'label' => Mage::helper('magedoc')->__('Source field filters'),
                 'name'  => 'source_fields_filters',
                 'cols_titles' => array(
                     Mage::helper('magedoc')->__('Base table field'),
                     Mage::helper('magedoc')->__('Field filter'),
                     Mage::helper('magedoc')->__('Action'),
                 )
            )
        );

        $fieldset->addField(
            'update_by_key', 'select',
            array(
                 'label' => Mage::helper('magedoc')->__('Update By Key'),
                 'name'  => 'update_by_key',
                 'values'    =>  Mage::getSingleton('magedoc/source_import_update_key')->getOptionArray()
            )
        );

        $fieldset->addField(
            'starting_record', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Starting record'),
                'name'  => 'starting_record',
                'required'    => true,
                'class' => 'required-entry validate-greater-than-zero',
            )
        );

        $fieldset->addField(
            'default_qty', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Default quantity'),
                'name'  => 'default_qty',
            )
        );

        $fieldset->addField(
            'discount_percent', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Discount percent'),
                'name'  => 'discount_percent',
                //'class' => 'required-entry',
            )
        );

        $fieldset->addField(
            'vat_percent', 'text',
            array(
                 'label' => Mage::helper('magedoc')->__('VAT percent'),
                 'name'  => 'vat_percent',
                 //'class' => 'required-entry',
            )
        );

        $fieldset->addField(
            'code_delimiter', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Code delimiter'),
                'name'  => 'code_delimiter',
            )
        );

        $fieldset->addField(
            'code_part_count', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Code part count'),
                'name'  => 'code_part_count',
            )
        );

        $fieldset->addField(
            'code_before', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Code before'),
                'name'  => 'code_before',
            )
        );

        $fieldset->addField(
            'code_after', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Code after'),
                'name'  => 'code_after',
            )
        );

        $form->getElement('source_adapter_config')->setRenderer(
            $this->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_sourceAdapterOptions')
                ->setForm($form)
        );

        $form->getElement('source_fields_map')->setRenderer(
            $this->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_sourceAdapterMap')->setForm($form)
        );

        $form->getElement('source_fields_filters')->setRenderer(
            $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_config_source_map')
        );

        $this->assign('configCollection', $configCollection);

        $form->setValues(array('starting_record' => 1));

        $this->setForm($form);

        return $this;
    }


    public function getRegionsUrl()
    {
        return $this->getUrl('*/json/countryRegion');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('cancel_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_config_button');
    }

    public function getTemplatePrefix()
    {
        return '_template_';
    }

    /**
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_file'),
            'image'     => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_image'),
            'boolean'   => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_boolean'),
        );
    }

    /**
     * Return JSON object with countries associated to possible websites
     *
     * @return string
     */
    public function getDefaultCountriesJson() {
        $websites = Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(false, true);
        $result = array();
        foreach ($websites as $website) {
            $result[$website['value']] = Mage::app()->getWebsite($website['value'])->getConfig(
                Mage_Core_Helper_Data::XML_PATH_DEFAULT_COUNTRY
            );
        }

        return Mage::helper('core')->jsonEncode($result);
    }

    /**
     * Add specified values to name prefix element values
     *
     * @param string|int|array $values
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
     */
    public function addValuesToNamePrefixElement($values)
    {
        if ($this->getForm() && $this->getForm()->getElement('prefix')) {
            $this->getForm()->getElement('prefix')->addElementValues($values);
        }
        return $this;
    }

    /**
     * Add specified values to name suffix element values
     *
     * @param string|int|array $values
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
     */
    public function addValuesToNameSuffixElement($values)
    {
        if ($this->getForm() && $this->getForm()->getElement('suffix')) {
            $this->getForm()->getElement('suffix')->addElementValues($values);
        }
        return $this;
    }

}