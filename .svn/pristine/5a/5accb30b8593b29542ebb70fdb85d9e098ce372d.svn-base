<?php

class Testimonial_MageDoc_Model_Indexer_Url extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'magedoc_url_match_result';

    /**
     * Index match: manufacturer save, model save
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Testimonial_MageDoc_Model_Manufacturer::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Testimonial_MageDoc_Model_Model::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('magedoc')->__('MageDoc URL Rewrites');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('magedoc')->__('Index manufacturer, model and type URL rewrites');
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();
        switch ($entity) {
            case Testimonial_MageDoc_Model_Manufacturer::ENTITY:
                $this->_registerEntityChangeEvent($event);
                break;

            case Testimonial_MageDoc_Model_Model::ENTITY:
                $this->_registerEntityChangeEvent($event, 'model');
                break;

            //case Mage_Core_Model_Store::ENTITY:
            //case Mage_Core_Model_Store_Group::ENTITY:
            case Mage_Core_Model_Config_Data::ENTITY:
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
        }
        return $this;
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['magedoc_url_reindex_all'])) {
            $this->reindexAll();
        }

        /* @var $urlModel Mage_Catalog_Model_Url */
        $urlModel = Mage::getSingleton('magedoc/url');

        // Force rewrites history saving
        $dataObject = $event->getDataObject();
        if ($dataObject instanceof Varien_Object && $dataObject->hasData('save_rewrites_history')) {
            $urlModel->setShouldSaveRewritesHistory($dataObject->getData('save_rewrites_history'));
        }

        if(isset($data['rewrite_manufacturer_ids'])) {
            foreach ($data['rewrite_manufacturer_ids'] as $manufacturerId) {
                $urlModel->refreshManufacturerRewrite($manufacturerId);
            }
        }
        if (isset($data['rewrite_model_ids'])) {
            //$urlModel->clearStoreInvalidRewrites(); // Maybe some categories were moved
            foreach ($data['rewrite_model_ids'] as $modelId) {
                $urlModel->refreshModelRewrite($modelId);
            }
        }
    }

    /**
     * Register event data during product save process
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEntityChangeEvent(Mage_Index_Model_Event $event, $entityType = 'manufacturer')
    {
        $entity = $event->getDataObject();
        $dataChange = $entity->dataHasChangedFor('url_key');

        if (!$entity->getExcludeUrlRewrite() && $dataChange) {
            $event->addNewData("rewrite_{$entityType}_ids", array($entity->getId()));
        }
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        /** @var $resourceModel Testimonial_MageDoc_Model_Mysql4_Url */
        $resourceModel = Mage::getResourceSingleton('magedoc/url');
        $resourceModel->beginTransaction();
        try {
            Mage::getSingleton('magedoc/url')->refreshRewrites();
            $resourceModel->commit();
        } catch (Exception $e) {
            Mage::logException($e);
            $resourceModel->rollBack();
            throw $e;
        }
    }
}