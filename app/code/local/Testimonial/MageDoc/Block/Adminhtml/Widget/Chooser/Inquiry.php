<?php
/**
 * @todo: Remove. Class is not in use
 */
class Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser_Inquiry extends Testimonial_MageDoc_Block_Adminhtml_Widget_Chooser
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_vehicle;
    protected $_comboboxes = array();
    protected $_containerId;
    
    public function __construct()
    {
        $this->setTemplate('magedoc/widget/chooser/inquiry.phtml');
    }
    
    public function getVehicle()
    {
        if($this->getData('vehicle') === null){
            $this->setVehicle(Mage::getModel('magedoc/order_vehicle'));
        }

        return $this->getData('vehicle');
    }
    
    
    public function prepareCombos()
    {
        $vehicle = $this->getVehicle();
        
        $this->addCombobox('product', array(
            'name'          => 'product',
            'title'         => $this->__('Product'),
            'default_text'  => '',
            'options'       =>
                Mage::getSingleton('magedoc/adminhtml_system_config_source_tecdocCategory')
                        ->getOptionArray(),
            'is_default'    => true,
            'disabled'      => $this->getDisabled()
        ));

        $this->addCombobox('supplier', array(
            'name'          => 'supplier',
            'options'       => Mage::getModel('magedoc/source_supplier')->getOptionArray(),
            'title'         => $this->__('Date'),
            'default_text'  => '',
            'disabled'      => $this->getDisabled()
            
        ));
       
        $this->addCombobox('number', array(
            'name'          => 'model',
            'options'       => Mage::getModel('magedoc/source_model')->getOptionArray(),
            'title'         => $this->__('Model'),
            'default_text'  => '',
            'disabled'      => $this->getDisabled()
        ));
       
        return $this;
    }
        
    public function getCurrentValuesJson()
    {
        $values = array();
        $vehicle = $this->getVehicle();
        if($vehicle->getTypeId() !== null){
            $values = array('manufacturer' => $vehicle->getModMfaId(),
                    'date' => $vehicle->getProductionStartYear(),
                    'model' => $vehicle->getModId(),
                    'type' => $vehicle->getId()
                    );
        }
        
        return Mage::helper('core')->jsonEncode($values);
    }
    
    public function getCurrentChooserData()
    {
        $data = array();
        foreach($this->getComboboxes() as $combo){
            if($combo->getIsDefault()){
                $data['select_'.$combo->getName()] = $combo->getOptions();
            }
        }
        
        return $data;
    }
    
    public function getControlsJson()
    {
        return Mage::helper('core')->jsonEncode($this->getCurrentVehicleData());
    }
        
    
    public function getEditUrl()
    {
        return $this->getUrl('magedoc/adminhtml_order/request/');
    }
}
