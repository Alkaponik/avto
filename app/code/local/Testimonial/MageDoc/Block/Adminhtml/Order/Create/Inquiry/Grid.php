<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Inquiry_Grid extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_moveToCustomerStorage = true;
    protected $_inquiries;
    protected $_form;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/order/create/inquiry/grid.phtml');
        $this->setId('magedoc_order_create_inquiry_grid');
    }
    
    
    public function getForm()
    {
        if(!isset($this->_form)){
            $this->_form = new Testimonial_MageDoc_Block_Adminhtml_Form_Extended();
        }
        return $this->_form;
    }
    
    protected function _prepareGrid()
    {
        foreach($this->getInquiries() as $inquiry){
            $this->addRow($inquiry);
        }
        $this->addRow();
        
        return $this;
    }
    
    public function addRow($inquiry = null)
    {
        $options = array();
        if(!is_null($inquiry)){
            $vehicleId = $inquiry->getQuoteVehicleId();
            $dataId = $inquiry->getId();
            $disabled = '';
            $fieldsetId = 'row_' . $dataId;
        }else{
            $inquiry = Mage::getModel('magedoc/order_inquiry');
            if($this->getVehicle() !== null){
                if($this->getVehicle()->getId() !== null){
                    $vehicleId = $this->getVehicle()->getId();   
                }
            }else{
                $vehicleId = '#{_vehicle_id}';   
            }
            $dataId = 'template';
            $fieldsetId = 'row-template';
            $disabled = 'disabled';
            $options = array('style' => 'display:none;');
        }
        
        $fieldset = $this->getForm()->addFieldset($fieldsetId, $options);
        $fieldset->setRenderer(Mage::app()->getLayout()
           ->createBlock('magedoc/adminhtml_widget_form_renderer_fieldset_row'))
                ->setArtId($inquiry->getArticleId())
                ->setTypeId($this->getTypeId());
        $this->_initFormElementTypes($fieldset);

        $this->addComboboxes($fieldset, $inquiry);
        $fieldset->addField('code_' . $dataId, 'text', array($disabled => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][code]",
                'value'     => $inquiry->getCode() === null
                                    ? '' : $inquiry->getCode(),
                'style'     => 'width:70px'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('sku_' . $dataId, 'hidden', array($disabled => true,
            'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][sku]",
            'value'     => $inquiry->getSku() === null
                ? '' : $inquiry->getSku()))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('article_id_' . $dataId, 'hidden', array($disabled => true,
            'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][article_id]",
            'value'     => $inquiry->getArticleId() === null
                ? '' : $inquiry->getArticleId()))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('product_id_' . $dataId, 'hidden', array($disabled => true,
            'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][product_id]",
            'value'     => $inquiry->getProductId() === null
                ? '' : $inquiry->getProductId()))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('cost_' . $dataId, 'text', array(
                $disabled   => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][cost]",
                'value'     => $inquiry->getCost() === null
                                        ? '0.0000' : $inquiry->getCost(),
                'class'     => 'validate-number right-align-text',
                'style'     => 'width:60px'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('price_' . $dataId, 'text', array(
                $disabled   => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][price]",
                'value'     => $inquiry->getPrice() === null 
                                    ? '0.0000' : $inquiry->getPrice(),
                'class'     => 'validate-number right-align-text',
                'style'     => 'width:60px'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('qty_' . $dataId, 'text', array(
                $disabled   => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][qty]",
                'value'     => $inquiry->getQty() === null 
                                    ? '0' : $inquiry->getQty(),
                'class'     => 'validate-greater-than-zero right-align-text'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
       $fieldset->addField('retailer_' . $dataId, 'select', array(
                $disabled   => true, 
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][retailer_id]",
                'value'     => $inquiry->getRetailerId() === null 
                                    ? 0 : $inquiry->getRetailerId(),
                'values'    => Mage::getSingleton('magedoc/source_retailer')
                            ->getOptionArray(),
                'style'     => 'width:100px'
                )
            )
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('subtotal_' . $dataId, 'text', array(
                $disabled   => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][row_total]",
                'value'     =>  $inquiry->getRowTotal() === null ? 
                        ($inquiry->getPrice() * $inquiry->getQty()) 
                        : $inquiry->getRowTotal(),
                'class'     => 'right-align-text',
                'readonly'  => 'readonly'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('discount_' . $dataId, 'text', array(
                $disabled   => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][discount_percent]",
                'value'     => $inquiry->getDiscountPercent() === null 
                                    ? '0' : $inquiry->getDiscountPercent(),
                'class'     => 'validate-number right-align-text'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));
        $fieldset->addField('row_total_' . $dataId, 'text', array(
                $disabled   => true,
                'name'      => "inquiry[{$vehicleId}][inquiries][{$dataId}][row_total_with_discount]",
                'value'     => $inquiry->getRowTotalWithDiscount() === null 
                                    ? '0.0000' : $inquiry->getRowTotalWithDiscount(),
                'class'     => 'right-align-text',
                'readonly'  => 'readonly'))
            ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_form_renderer_element'));

       return $this;
    }
    
    public function addComboboxes($fieldset, $inquiry)
    {
        $categorySourceModel = Mage::getModel('magedoc/source_type_category');
        if($this->getTypeId() !== null){
            $categorySourceModel->setTypeId($this->getTypeId());
            $categoryOptions = $categorySourceModel->getOptionArray();
        }else{
            $categoryOptions = array();
        }
        $supplierOptions = array();
        $articleOptions = array();
        
        if($inquiry->getId() === null){
            $inquiry = Mage::getModel('magedoc/order_inquiry');
            if($this->getVehicle() !== null){
                if($this->getVehicle()->getId() !== null){
                    $vehicleId = $this->getVehicle()->getId();   
                }
            }else{
                $vehicleId = '#{_vehicle_id}';   
            }
            $dataId = 'template';
            $disabled = 'disabled';
        }else{            
            if($inquiry->getCategoryId() !== null){
                $supplierOptions = Mage::getModel('magedoc/source_type_supplier')
                                        ->setTypeId($this->getTypeId())
                                        ->setStrId($inquiry->getCategoryId())
                                        ->getOptionArray();
                if($inquiry->getSupplierId() !== null){
                    $articleOptions = Mage::getModel('magedoc/source_type_article')
                                        ->setTypeId($this->getTypeId())
                                        ->setStrId($inquiry->getCategoryId())
                                        ->setSupplierId($inquiry->getSupplierId())
                                        ->getOptionArray();
                }
            }
            $vehicleId = $inquiry->getQuoteVehicleId();
            $dataId = $inquiry->getId();
            $disabled = '';
            
        }
        
        $field = $fieldset->addField('category_' . $dataId, 'combobox', array( 
            'container_id'  => 'category',
            'name'          => "category",
            'default_text'  => $inquiry->getCategory(),
            'value'         => $inquiry->getCategoryId(),
            'values'        => $categoryOptions,
            'disabled'      => $disabled,
            'input_name'    => 
                "inquiry[{$vehicleId}][inquiries][{$dataId}][category]",
            'select_name'   => 
                "inquiry[{$vehicleId}][inquiries][{$dataId}][category_id]"
        ));
        $field->getRenderer()->setTemplate('magedoc/widget/combobox.phtml');
        $field = $fieldset->addField('supplier_' . $dataId, 'combobox', array(                
            'container_id'  => 'supplier',
            'name'          => "supplier",
            'default_text'  => $inquiry->getSupplier(),
            'value'         => $inquiry->getSupplierId(),
            'values'       => $supplierOptions,            
            'disabled'      =>  $disabled,
            'input_name'    => 
                "inquiry[{$vehicleId}][inquiries][{$dataId}][supplier]",
            'select_name'   => 
                "inquiry[{$vehicleId}][inquiries][{$dataId}][supplier_id]"
        ));
        $field->getRenderer()->setTemplate('magedoc/widget/combobox.phtml');
        $field = $fieldset->addField('article_' . $dataId, 'combobox', array(
            'container_id'  => 'article',
            'name'          => "article",
            'default_text'  => $inquiry->getName(),
            'value'         => $inquiry->getArticleId(),
            'values'       => $articleOptions,            
            'disabled'      =>  $disabled,
            'class'         => 'expand',
            'input_name'    => 
                "inquiry[{$vehicleId}][inquiries][{$dataId}][name]",
            'select_name'   => 
                "inquiry[{$vehicleId}][inquiries][{$dataId}][article_id]"
        ));
        $field->getRenderer()->setTemplate('magedoc/widget/combobox.phtml');        
        return $this;
    }
    
    
    
    protected function _initFormElementTypes($fieldset)
    {        
        $fieldset->addType('combobox', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Combobox');        
    }

    
    
    protected function _beforeToHtml() 
    {
        $this->_prepareGrid();
        parent::_beforeToHtml();
    }

    
    
    public function getInquiries()
    {
        if($this->getVehicle()){
            return $this->getVehicle()->getAllInquiries();
        }
        return array();
        
    }
        
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if($element->getVehicle() !== null){
            $this->setVehicle($element->getVehicle());
            if($element->getVehicle()->getTypeId() !== null){
                $this->setTypeId($element->getVehicle()->getTypeId());
                
            }
        }

        return $this->toHtml();
    }
}
