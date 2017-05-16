<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml Catalog Category Attributes per Group Tab block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Block_Adminhtml_Category_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
{
    public function getCategory()
    {
        $category = Mage::registry('current_category');
        
        if($category->getTdStrId()){
            $category->setId($category->getTdStrId());
        }
        return $category;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
     */
    protected function _prepareForm() {
        $group      = $this->getGroup();
        $attributes = $this->getAttributes();

        $form = new MageDoc_System_Block_Adminhtml_Form_Extended();
        $form->setHtmlIdPrefix('group_' . $group->getId());
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('fieldset_group_' . $group->getId(), array(
            'legend'    => Mage::helper('catalog')->__($group->getAttributeGroupName()),
            'class'     => 'fieldset-wide',
        ));

        if ($this->getAddHiddenFields()) {
            if (!$this->getCategory()->getId()) {
                // path
                if ($this->getRequest()->getParam('parent')) {
                    $fieldset->addField('path', 'hidden', array(
                        'name'  => 'path',
                        'value' => $this->getRequest()->getParam('parent')
                    ));
                }
                else {
                    $fieldset->addField('path', 'hidden', array(
                        'name'  => 'path',
                        'value' => 1
                    ));
                }
            }
            else {
                $fieldset->addField('id', 'hidden', array(
                    'name'  => 'id',
                    'value' => $this->getCategory()->getId()
                ));
                $fieldset->addField('path', 'hidden', array(
                    'name'  => 'path',
                    'value' => $this->getCategory()->getPath()
                ));
            }
        }

        $this->_setFieldset($attributes, $fieldset);

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            if ($attribute->getAttributeCode() == 'url_key') {
                if ($this->getCategory()->getLevel() == 1) {
                    $fieldset->removeField('url_key');
                    $fieldset->addField('url_key', 'hidden', array(
                        'name'  => 'url_key',
                        'value' => $this->getCategory()->getUrlKey()
                    ));
                } else {
                    $form->getElement('url_key')->setRenderer(
                        $this->getLayout()->createBlock('adminhtml/catalog_form_renderer_attribute_urlkey')
                    );
                }
            }
        }

        if ($this->getCategory()->getLevel() == 1) {
            $fieldset->removeField('custom_use_parent_settings');
        } else {
            if ($this->getCategory()->getCustomUseParentSettings()) {
                foreach ($this->getCategory()->getDesignAttributes() as $attribute) {
                    if ($element = $form->getElement($attribute->getAttributeCode())) {
                        $element->setDisabled(true);
                    }
                }
            }
            if ($element = $form->getElement('custom_use_parent_settings')) {
                $element->setData('onchange', 'onCustomUseParentChanged(this)');
            }
        }

        if ($this->getCategory()->hasLockedAttributes()) {
            foreach ($this->getCategory()->getLockedAttributes() as $attribute) {
                if ($element = $form->getElement($attribute)) {
                    $element->setReadonly(true, true);
                }
            }
        }

        if (!$this->getCategory()->getId()){
            $this->getCategory()->setIncludeInMenu(1);
        }

        $form->addValues($this->getCategory()->getData());

        Mage::dispatchEvent('adminhtml_catalog_category_edit_prepare_form', array('form'=>$form));

        $form->setFieldNameSuffix('general');
        $this->setForm($form);

        Mage_Adminhtml_Block_Catalog_Form::_prepareForm();

        $this->__prepareForm();

        return $this;
    }

    public function __prepareForm()
    {
        //parent::_prepareForm();

        $group      = $this->getGroup();
        $form = $this->getForm();
        if ($group->getAttributeGroupName() == 'MageDoc'){
            $fieldset = $form->getElements()->searchById('fieldset_group_' . $group->getId());

            $fieldset->addField('search_tree_category_id', 'combobox', array(
                'label'     => Mage::helper('magedoc')->__('Linked Category'),
                'title'     => Mage::helper('magedoc')->__('Linked Category'),
                'name'      => 'search_tree[category_id]',
                'value'     => $this->getCategory()->getSearchTree()->getCategoryId(),
                'options'   => array(),
                'source_url'=> $this->getUrl('*/*/list'),
                'settings'  => array(
                    'isAjax'   => true
                )
            ));

            $element = $fieldset->addField('search_tree_is_enabled', 'select',
                array(
                    'name'      => 'search_tree[is_enabled]',
                    'required'  => false,
                    'label'     => Mage::helper('magedoc')->__('Is Enabled'),
                    'class'     => '',
                    'value'     => $this->getCategory()->getSearchTree()->getIsEnabled()
                )
            );
            $element->setValues(Mage::getSingleton('eav/entity_attribute_source_boolean')->getAllOptions(true, true));

            $element = $fieldset->addField('search_tree_is_import_enabled', 'select',
                array(
                    'name'      => 'search_tree[is_import_enabled]',
                    'required'  => false,
                    'label'     => Mage::helper('magedoc')->__('Is Import Enabled'),
                    'class'     => '',
                    'value'     => $this->getCategory()->getSearchTree()->getIsImportEnabled()
                )
            );
            $element->setValues(Mage::getSingleton('eav/entity_attribute_source_boolean')->getAllOptions(true, true));

            $fieldset->addField('search_tree_name', 'text',
                array(
                    'name'      => 'search_tree[name]',
                    'required'  => false,
                    'label'     => Mage::helper('magedoc')->__('Search Tree Name'),
                    'class'     => '',
                    'value'     => $this->getCategory()->getSearchTree()->getName()
                )
            );

            $form->addValues($this->getCategory()->getData());
        }

        Mage::dispatchEvent('magedoc_category_edit_prepare_form', array('form'=>$form));

        return $this;
    }
}
