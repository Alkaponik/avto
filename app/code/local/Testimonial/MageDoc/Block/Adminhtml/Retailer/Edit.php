<?php

class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'magedoc';
        $this->_controller = 'adminhtml_retailer';

        $this->_updateButton('save', 'label',  Mage::helper('magedoc')->__('Save Item'));
        $this->_updateButton('save', 'class',  'save tab_button_toggler form_section config source price_upload config_supply import_settings');

        $this->_updateButton('delete', 'label',  Mage::helper('magedoc')->__('Delete Retailer'));
        $this->_updateButton('delete', 'class',  'delete tab_button_toggler form_section config source  price_upload config_supply import_settings');
        $this->_updateButton('reset', 'class',  'reset tab_button_toggler form_section config source price_upload config_supply import_settings');


        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit(\'' . $this->getSaveAndContinueUrl() . '\')',
            'class'     => 'save tab_button_toggler form_section config source price_upload config_supply import_settings',
        ), -100);

        $this->_addButton('save_supplier_map',
            array(
                 'label'     => Mage::helper('adminhtml')->__('Save supplier map'),
                 'onclick'   => 'saveAndContinueEdit(\'' . $this->getSaveSupplierMapUrl() . '\')',
                 'class'     => 'tab_button_toggler supplier_map',
            ),
            -100);
        $retailer = Mage::registry('retailer');

        if($retailer && $retailer->hasActiveSession() &&
            !Mage::helper('magedoc_system/lock')->isLocked('retailer_import')) {
            $this->_addButton('process_price',
                array(
                     'label'     => Mage::helper('adminhtml')->__('Process price'),
                     'onclick'   => 'window.location.href = \''.$this->getUrl('*/*/processprice/',  array('_current'=>true)).'\'',
                     'class'     => 'tab_button_toggler price_upload',
                ),
                -100);
        }

        $this->_addButton('suggest',
            array(
                 'label'     => Mage::helper('adminhtml')->__($this->_isSuggestAction() ? 'Hide suggestions' : 'Suggest'),
                 'onclick'   => 'saveAndContinueEdit(\'' . $this->getSuggestSupplierUrl( ) . '\')',
                 'class'     => 'tab_button_toggler supplier_map ' . ( $this->_isSuggestAction() ? 'cancel' : '' ),
            ),
            -100);

        if( $retailer->hasActiveSession()
            && !Mage::helper('magedoc_system/lock')->isLocked('retailer_import')
            && $retailer->getActiveSession()->getStatusId()
                == Testimonial_MageDoc_Model_Retailer_Data_Import_Session::SESSION_STATUS_PROCESSING ) {
            $this->_addButton('insert_into_import_retailer',
                array(
                    'label'     => Mage::helper('adminhtml')->__('Import price'),
                    'onclick'   => 'window.location.href = \''.$this->getUrl('*/*/insertintoprice/',  array('_current'=>true)).'\'',
                    'class'     => 'tab_button_toggler tmp_grid',
                ),
                -100);

            if( $this->isNotImportedRetailerBrands($retailer) ){
                $this->_addButton('add_new_brands_to_supplier_map',
                    array(
                      'label'     => Mage::helper('adminhtml')->__('Import brands'),
                      'onclick'   => 'window.location.href = \''.$this->getUrl('*/*/importbrands/',  array('_current'=>true)).'\'',
                      'class'     => 'tab_button_toggler price_upload',
                    ),
                -100);
            }

            $this->_addButton('update_preview',
                array(
                    'label'     => Mage::helper('adminhtml')->__('Update preview'),
                    'onclick'   => 'window.location.href = \''.$this->getUrl('*/*/updatepreview/',  array('_current'=>true)).'\'',
                    'class'     => 'tab_button_toggler tmp_grid',
                ),
                -100
            );
        }


        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('retailer_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'retailer_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'retailer_content');
                }
            }

            var productTemplateSyntax = /(^|.|\\r|\\n)({{(\\w+)}})/;
            function saveAndContinueEdit(urlTemplate) {
               var template = new Template(urlTemplate, productTemplateSyntax);
               var url = template.evaluate({tab_id:retailer_tabsJsTabs.activeTab.id});
               editForm.submit(url);
            }

            function saveSupplierMap(urlTemplate) {
               var template = new Template(urlTemplate, productTemplateSyntax);
               var url = template.evaluate({tab_id:retailer_tabsJsTabs.activeTab.id});
               editForm.submit(url);
            }
        ";
    }

    public function getHeaderText()
    {
        if($retailer = Mage::registry('retailer')) {
            if($retailer->getId() === null){
                return Mage::helper('magedoc')->__("New retailer");
            }
            return Mage::helper('magedoc')->__("Edit '%s' retailer", $this->htmlEscape($retailer->getName()));
        }
    }

    public function isNotImportedRetailerBrands($retailer)
    {
        if($retailer->hasActiveSession()) {
            $activeSession = $retailer->getActiveSession();
            if( $activeSession->getValidRecords() ) {
                return $activeSession->getNewBrands() > $activeSession->getImportedBrands();
            }
        }

        return false;
    }

    public function getSaveAndContinueUrl(array $params = array())
    {
        $params = array_merge(
            array(
                 'back' => 'edit',
            ),
            $params
        );
        return $this->getButtonUrl('*/*/save', $params);
    }

    public function getSaveSupplierMapUrl( array $params = array() )
    {
        $params = array_merge(
            array(
                'retailer_id' => Mage::registry('retailer')->getId(),
                'back' => 'edit',
            ),
            $params
        );
        if ($this->getRequest()->getParam('directory')) {
            $params['directory'] = $this->getRequest()->getParam('directory');
        }
        return $this->getButtonUrl('*/adminhtml_supplier_map/save', $params);
    }

    public function getSuggestSupplierUrl( array $params = array() )
    {
        $defaultParams = array('suggest' => 1);
        if( $this->_isSuggestAction() ) {
            $defaultParams = array(
                '_current' => false,
                'id' => Mage::registry('retailer')->getId(),
            );
        }

        $params = array_merge($defaultParams, $params);

        return $this->getButtonUrl('*/*/*', $params);
    }

    protected function _isSuggestAction()
    {
        $suggest = $this->getRequest()->getParam('suggest');
        return !empty($suggest);
    }

    public function getButtonUrl( $url, $params )
    {
        $params = array_merge(
            array(
                 '_current'   => true,
                 'tab'        => '{{tab_id}}',
                 'active_tab' => null,
            ),
            $params
        );
        return $this->getUrl($url, $params);
    }
}