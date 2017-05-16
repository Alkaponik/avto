<?php

class Testimonial_MageDoc_Model_Source_Type_Category
{
    protected $_collection;
    protected $_typeId;
    
    public function toOptionArray($addEmpty = true)
    {                
        if (!isset($this->_collection)){
            $this->_collection = Mage::getResourceModel('magedoc/tecdoc_searchTree_collection');
            $this->_collection->joinDesignation($this->_collection, 'main_table', 'STR_DES_ID', 'category_name')
                ->getSelect()
                ->where('main_table.STR_LEVEL > 0')
                ->order('main_table.STR_SORT');

            /**
             * @todo: Improve search tree load performance (temporarily disable articles join)
             */
            $this->_collection->getSelect()
                ->join(array('searchTree' => $this->_collection->getTable('magedoc/searchTree')),
                    'searchTree.str_id = main_table.STR_ID AND searchTree.path LIKE \'1/10001/%\'',
                    ''
                )->where('main_table.STR_LEVEL < 4');

            if(false && $this->getTypeId() !== null){
                $this->_collection->getSelect()->where("EXISTS(
                    SELECT * FROM " . Mage::getResourceSingleton('magedoc/tecdoc_linkGAStr')->getTable('magedoc/tecdoc_linkGAStr') . " as LINK_GA_STR
                        INNER JOIN " . Mage::getResourceSingleton('magedoc/tecdoc_linkGAStr')->getTable('magedoc/tecdoc_linkLaTyp') . " as  LINK_LA_TYP ON LAT_TYP_ID = {$this->getTypeId()}
                                AND LAT_GA_ID = LGS_GA_ID
                        INNER JOIN " . Mage::getResourceSingleton('magedoc/tecdoc_linkArt')->getTable('magedoc/tecdoc_linkArt') . " as LINK_ART ON LA_ID = LAT_LA_ID
                    WHERE
                        LGS_STR_ID = main_table.STR_ID
                    LIMIT 1)");
            }

        }
        $options = array();
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }
        foreach ($this->_collection as $category) {
                $options[] = array(
                   'label' => str_repeat('-', $category->getStrLevel()-1).$category->getCategoryName(),
                   'value' => $category->getId()
                );
        }
        
        return $options;
    }
    
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;

        return $this;
    }
    
    public function getTypeId()
    {
        if(!isset($this->_typeId)){
            return null;
        }
        return $this->_typeId;
    }
    
    public function getOptionArray()
    {
        $options = $this->toOptionArray(false);
        $optionArray = array();
        foreach ($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}