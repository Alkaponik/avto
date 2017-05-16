<?php

class Testimonial_MageDoc_Model_Source_Type extends Testimonial_MageDoc_Model_Source_Abstract
{   
    protected $_modelId;
    protected $_typeIds;
    protected $_urlModel;

    public function setModelId($modelId)
    {
        $this->_modelId = $modelId;
        
        return $this;
    }
    
    public function getModelId()
    {
        return $this->_modelId;
    }

    public function setTypeIds($typeIds)
    {
        $this->_typeIds = $typeIds;

        return $this;
    }

    public function getTypeIds()
    {
        return $this->_typeIds;
    }

    public function getCollectionArray()
    {
        if (empty($this->_collectionArray)) {
            /* @var $modelTypesCollection Testimonial_MageDoc_Model_Mysql4_Tecdoc_Type_Collection */
            $modelTypesCollection = Mage::getResourceModel('magedoc/tecdoc_type_collection')
                ->addTypeDesignation()
                ->joinTypeEngine();
            if ($this->getTypeIds()){
                $modelTypesCollection->addTypeFilter($this->getTypeIds());
                $modelTypesCollection->joinModels();
            }  else {
                $modelTypesCollection->addModelFilter($this->getModelId());
            }
            $modelTypesCollection->renderAll();

            while($type = $modelTypesCollection->fetchItem()){
                $model = Mage::getSingleton('magedoc/model')->factory($type->getTypModId());
                $veng=number_format(round($type->getTypCcm()/1000,1),1);
                $engineLabel = $veng.' '.$type->getTypFuelDesText().' '.$type->getTypHpFrom().' '.Mage::helper('magedoc')->__('h.p.');
                $shortEngineLabel = $veng. ' '. $type->getTypHpFrom() .' '.Mage::helper('magedoc')->__('h.p.');
                if ($type->getEngCode()){
                    $engineLabel .= ' ('.$type->getEngCode().')';
                    $shortEngineLabel .= ' ('.$type->getEngCode().')';
                }
                $item = array(
                    'label'     => $engineLabel,
                    'label_short' => $shortEngineLabel,
                    'value'     => $type->getTypId(),
                    'model_id'  => $type->getTypModId(),
                    'model'     => $type->getModelName(),
                    'url'       => $model->getUrl(array('_query' => array('TYP_ID' => $type->getId())))//$this->_getTypeUrl($type)
                );
                $this->_collectionArray[] = $item;
            }
        }
        
        return $this->_collectionArray;
    }

    public function getModelTypes($addUrls = false)
    {
        $source = $this->getCollectionArray();
        $modelTypes = array();
        foreach($source as $type){
            if (!isset($modelTypes[$type['model_id']])){
                $modelTypes[$type['model_id']] = array(
                    'label' => $type['model'],
                    'types' => array(
                        0 => $type
                    )
                );
            } else {
                $modelTypes[$type['model_id']]['types'][] = $type;
            }
        }

        return $modelTypes;
    }

    protected function _getTypeUrl($type)
    {
        if ($type->getModel()->getUrlPath()){
            return $this->getUrlModel()->getUrl(
                '',
                array(
                    '_direct' => $type->getModel()->getUrlPath(),
                    '_query'  => array('TYP_ID' => $type->getTypId()),
                    '_store_to_url' => null
                ));
        }
        $query = http_build_query(array(
            'show_aux_page' => 58,
            'MOD_ID'        => $type->getTypModId(),
            'MFA_ID'        => $type->getModMfaId(),
            'MFA_BRAND'     => $type->getMfaBrand(),
            'MOD_CDS'       => $type->getModelName(),
            'TYP_ID'        => $type->getTypId(),
        ));
        return $this->getUrlModel()->getUrl(
            '',
            array(
                '_direct' => 'index.php?'.$query,
            ));
    }

    public function getUrlModel()
    {
        if (!isset($this->_urlModel)){
            $this->_urlModel = Mage::getModel('core/url');
        }
        return $this->_urlModel;
    }
}
