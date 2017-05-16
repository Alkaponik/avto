<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Config_Source_Map
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{

    const FIELD_LIST_CONFIG_PATH = 'global/magedoc/retailer_data_import_base_table/fields';

    /**
     * Form element instance
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_element;

    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('magedoc/retailer/import/adapter/edit/config/sourcemap.phtml');
    }

    public function getValues()
    {
        $values = array();
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

        return $values;
    }

    protected function _sortMapItems($a, $b)
    {
        if($a['path'] == $b['path']) {
            return 0;
        }

        return $a['path'] > $b['path'] ? 1 : -1;
    }

    public function getBaseTableFieldList()
    {
        $fieldList = array();
        $fields = Mage::getConfig()
            ->getNode( static::FIELD_LIST_CONFIG_PATH )->children();
        $fields = array_keys( (array) $fields );
        $fieldList['Base table Fields'] = array_combine( $fields, $fields );
        $fieldList = array_merge($fieldList, $this->getDirectoriesExtraFields());
        return  $fieldList;
    }

    public function getDirectoriesExtraFields()
    {
        $directories = Mage::getConfig()
            ->getNode( Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH )->asArray();
        $fieldList = array();

        foreach($directories as $code => $directory) {
            if(!empty($directory['extra_fields'])) {
                $fields = explode(',' ,$directory['extra_fields'] );
                $fieldList[$directory['name']] = array_combine( $fields, $fields );
            }
        }
        return $fieldList;
    }

    protected function _beforeToHtml()
    {

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                           'label' => Mage::helper('catalog')->__('Add Field'),
                           'onclick' => 'return tierPriceControl' . $this->getElement()->getId() . '.addItem(this)',
                           'class' => 'add'
                      ));
        $button->setName('add_field_button');
        $this->setChild('add_button', $button);

        return parent::_beforeToHtml();
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        return $data;
        usort($data, array($this, '_sortMapItems'));
        return $data;
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set form element instance
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group_Abstract
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}