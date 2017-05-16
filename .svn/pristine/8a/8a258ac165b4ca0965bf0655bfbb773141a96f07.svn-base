<?php
class  Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Source
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/retailer/import/adapter/edit/source.phtml');
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
        $this->setChild('add_source_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label'  => Mage::helper('customer')->__('Add New Source'),
                               'id'     => 'add_source_button',
                               'name'   => 'add_source_button',
                               'element_name' => 'add_source_button',
                               'disabled' => $this->isReadonly(),
                               'class'  => 'add'  . ($this->isReadonly() ? ' disabled' : ''),
                               'onclick'=> 'customerSources.addNewSource()'
                          ))
        );
        $this->setChild('cancel_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label'  => Mage::helper('customer')->__('Cancel'),
                               'id'     => 'cancel_add_source'.$this->getTemplatePrefix(),
                               'name'   => 'cancel_source',
                               'element_name' => 'cancel_source',
                               'class'  => 'cancel delete-address'  . ($this->isReadonly() ? ' disabled' : ''),
                               'disabled' => $this->isReadonly(),
                               'onclick'=> 'customerSources.cancelAdd(this)',
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
        /** @var  $sourceCollection Testimonial_MageDoc_Model_Mysql4_Retailer_Import_Source_Config_Collection */
        $sourceCollection = $retailer->getImportSourceCollection();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'source_fieldset',
            array(
                'legend' => Mage::helper('customer')->__("Edit Retailer Import Source Config")
            )
        );

        $fieldset->addField(
            'name', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Source name'),
                'name'  => 'name',
                'class' => 'required-entry',
                'required'  => true,
                'container_id' => 'test',
            )
        );

        $fieldset->addField(
            'source_type', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Source type'),
                'name'  => 'source_type',
                'class' => 'required-entry',
                'required'  => true,
                'values'    =>  array(
                    array('value'=>'','label'=>''),
                    array('value'=>'email','label'=>'Email'),
                    array('value'=>'file','label'=>'File System'),
                    array('value'=>'ftp','label'=>'Ftp'),
                ),
            )
        );

        //email

        $fieldset->addField(
            'email_protocol', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Protocol'),
                'name'  => 'email[email_protocol]',
                'class' => 'email',
                'values' => array(
                    array('value'=>'imap','label'=>'Imap'),
                    array('value'=>'pop3','label'=>'Pop3'),
                ),
            )
        );

        $fieldset->addField(
            'server_settings', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Host'),
                'name'  => 'email[server_settings]',
                'class' => 'email',
            )
        );

        $fieldset->addField(
            'email_port', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Port'),
                'name'  => 'email[email_port]',
                'class' => 'validate-number email',
            )
        );

        $fieldset->addField(
            'email_ssl', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('SSL'),
                'name'  => 'email[email_ssl]',
                'class' => 'email',
                'values' => array(
                    array('value'=>'0','label'=>''),
                    array('value'=>'SSL','label'=>'SSL'),
                    array('value'=>'TLS','label'=>'TLS'),
                ),
            )
        );

        $fieldset->addField(
            'email_user_name', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('User Name'),
                'name'  => 'email[email_user_name]',
                'class' => 'email',
            )
        );

        $fieldset->addField(
            'email_password', 'password',
            array(
                'label' => Mage::helper('magedoc')->__('Password'),
                'name'  => 'email[email_password]',
                'class' => 'email',
            )
        );

        $fieldset->addField(
            'from', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('From'),
                'name'  => 'email[from]',
                'class' => 'email',
            )
        );

        /*$fieldset->addField(
            'to', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('To'),
                'name'  => 'email[to]',
                'class' => 'email',
            )
        );*/

        $fieldset->addField(
            'folder', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Folder'),
                'name'  => 'email[folder]',
                'class' => 'email',
            )
        );

        $fieldset->addField(
            'subject', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Subject Contains'),
                'name'  => 'email[subject]',
                'class' => 'email',
            )
        );

        $fieldset->addField(
            'attachment_name_exp', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('File Name Expression'),
                'name'  => 'email[attachment_name_exp]',
                'class' => 'email validate-regexp',
            )
        );

        $fieldset->addType('checkbox', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Checkbox');
        $fieldset->addField(
            'unseen', 'checkbox',
            array(
                'label' => Mage::helper('magedoc')->__('Skip already opened messages'),
                'name'  => 'email[unseen]',
                'checked' => 'checked',
                'class' => 'email',
            )
        );

        /*$fieldset->addField(
            'content_contains', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Content_Contains'),
                'name'  => 'email[content_contains]',
                'class' => 'email',
            )
        );*/

        /*$fieldset->addField(
            'attachment_file_name', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Attachment File Name Expression'),
                'name'  => 'email[attachment_file_name]',
                'class' => 'email',
            )
        );*/

        //file
        $fieldset->addField(
            'path', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('File'),
                'name'  => 'file[path]',
                'class' => 'file',
            )
        );

        //ftp
        $fieldset->addField(
            'host', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Host'),
                'name'  => 'ftp[host]',
                'class' => 'ftp',
            )
        );

        $fieldset->addField(
            'ftp_port', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('Port'),
                'name'  => 'ftp[ftp_port]',
                'class' => 'validate-number ftp',
            )
        );

        $fieldset->addField(
            'ftp_protocol', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Protocol'),
                'name'  => 'ftp[ftp_protocol]',
                'class' => 'ftp',
                'values' => array(
                    array('value'=>'ftp','label'=>'FTP'),
                    array('value'=>'sftp','label'=>'SFTP'),
                ),
            )
        );

        $fieldset->addField(
            'logon_type', 'select',
            array(
                'label' => Mage::helper('magedoc')->__('Logon Type'),
                'name'  => 'ftp[logon_type]',
                'class' => 'ftp',
                'values' => array(
                    array('value'=>'anonymous','label'=>'Anonymous'),
                    //array('value'=>'password','label'=>'Ask For Password'),
                    //array('value'=>'interactive','label'=>'Interactive'),
                    array('value'=>'account','label'=>'Account'),
                ),
            )
        );

        $fieldset->addField(
            'ftp_user_name', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('User Name'),
                'name'  => 'ftp[ftp_user_name]',
                'class' => 'ftp',
            )
        );

        $fieldset->addField(
            'ftp_password', 'password',
            array(
                'label' => Mage::helper('magedoc')->__('Password'),
                'name'  => 'ftp[ftp_password]',
                'class' => 'ftp',
            )
        );

        $fieldset->addField(
            'file_name_expression', 'text',
            array(
                'label' => Mage::helper('magedoc')->__('File Name Expression'),
                'name'  => 'ftp[attachment_file_name]',
                'class' => 'ftp',
            )
        );

        $this->assign('sourceCollection', $sourceCollection);

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
        return $this->getChildHtml('add_source_button');
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