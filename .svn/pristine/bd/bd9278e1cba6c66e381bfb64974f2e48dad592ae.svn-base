<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('retailer_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('magedoc')->__('Retailer Information'));
    }

    protected function _beforeToHtml()
    {

        $this->addTab(
            'form_section',
            array(
                'label'   => Mage::helper('magedoc')->__('Retailer Information'),
                'title'   => Mage::helper('magedoc')->__('Retailer Information'),
                'content' => $this->getLayout()->createBlock(
                    'magedoc/adminhtml_retailer_edit_tab_form'
                )->toHtml(),
            )
        );
        $this->addTab(
            'config_supply',
            array(
                 'label'   => Mage::helper('magedoc')->__('Supply Config'),
                 'title'   => Mage::helper('magedoc')->__('Supply Config'),
                 'content' => $this->getLayout()->createBlock(
                     'magedoc/adminhtml_retailer_edit_tab_config_supply_form'
                 )->toHtml(),
            )
        );

        $this->addTab(
            'config',
            array(
                'label'   => Mage::helper('magedoc')->__('Import Adapter Config'),
                'title'   => Mage::helper('magedoc')->__('Import Adapter Config'),
                'content' => $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_config')->initForm()
                ->toHtml(),
            )
        );

        $this->addTab(
            'source',
            array(
                'label'   => Mage::helper('magedoc')->__('Import Source Config'),
                'title'   => Mage::helper('magedoc')->__('Import Source Config'),
                'content' => $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_source')->initForm()
                ->toHtml(),
            )
        );

        $this->addTab(
            'import_settings',
            array(
                'label'   => Mage::helper('magedoc')->__('Import Settings'),
                'title'   => Mage::helper('magedoc')->__('Import Settings'),
                'content' => $this->_getImportSettingsContent(),
            )
        );

        $this->addTab(
            'price_upload',
            array(
                'label'   => Mage::helper('magedoc')->__('Price Upload'),
                'title'   => Mage::helper('magedoc')->__('Price Upload'),
                'content' => $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_price_upload_form')->toHtml(),
            )
        );

        $suggest = $this->getRequest()->getParam('suggest');
        $supplierMapTabParams = array(
            '_current' => true,
        );
        if( !empty($suggest) ) {
            $supplierMapTabParams['suggest'] = 1;
        }
        $this->addTab(
            'supplier_map',
            array(
                'label'   => Mage::helper('magedoc')->__('Suppliers Map'),
                'title'   => Mage::helper('magedoc')->__('Suppliers Map'),
                'is_ajax' => true,
                'class'   => 'ajax',
                'url'     => $this->getUrl('*/*/suppliermappreview', $supplierMapTabParams),
            )
        );

        $this->addTab(
            'tmp_grid',
            array(
                'label'   => Mage::helper('magedoc')->__('Import Preview'),
                'title'   => Mage::helper('magedoc')->__('Import Preview'),
                'is_ajax' => true,
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/*/tmppreview', array('_current' => true)),
            )
        );

        $data = $this->getRequest()->getParams();
        if(isset($data['tab'])) {
            $this->_setActiveTab($data['tab']);
        }

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $result = parent::_toHtml();
        $activeTabName = substr($this->getActiveTabId(), mb_strlen($this->getId()) + 1);
        $result .= <<<HTML
            <script type='text/javascript'>
                document.observe('dom:loaded',
                    function()
                    {
                        new TabButtonToggler('$activeTabName');
                    }
                );
            </script>
HTML;
        return $result;
    }

    protected function _getImportSettingsContent()
    {
        $retailer = Mage::registry('retailer');
        $content = '';
        if (!$retailer->getImportSourceCollection()->getSize()){
            $content .= $this->getLayout()->createBlock('core/messages')
                ->addWarning('Setup Import Source configurations first before setting import schedule')
                ->toHtml();
        } if (!$retailer->getImportConfigCollection()->getSize()){
            $content .= $this->getLayout()->createBlock('core/messages')
                ->addWarning('Setup Import Adapter configurations first')
                ->toHtml();
        }

        $content .= $this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tab_settings_form')
            ->toHtml();
        return $content;
    }
}