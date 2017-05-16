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
 * @package     Testimonial_FlatCatalogSearch
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * CatalogSearch fulltext indexer model
 *
 * @category    Mage
 * @package     Testimonial_FlatCatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_FlatCatalogSearch_Model_Indexer_Fulltext extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'flatcatalogsearch_fulltext_match_result';

    /**
     * List of searchable attributes
     *
     * @var null|array
     */
    protected $_searchableAttributes = null;

    /**
     * Retrieve resource instance
     *
     * @return Testimonial_FlatCatalogSearch_Model_Resource_Indexer_Fulltext
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('flatcatalogsearch/indexer_fulltext');
    }

    /**
     * Indexer must be match entities
     *
     * @var array
     */
    protected $_matchedEntities = array(    );

    /**
     * Related Configuration Settings for match
     *
     * @var array
     */
    protected $_relatedConfigSettings = array(
        Testimonial_FlatCatalogSearch_Model_Fulltext::XML_PATH_CATALOG_SEARCH_TYPE
    );

    /**
     * Retrieve Fulltext Search instance
     *
     * @return Testimonial_FlatCatalogSearch_Model_Fulltext
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('flatcatalogsearch/fulltext');
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('flatcatalogsearch')->__('Flat Catalog Search Index');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('flatcatalogsearch')->__('Rebuild Flat Catalog product fulltext search index');
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {

    }

    /**
     * Rebuild all index data
     *
     */
    public function reindexAll()
    {
        $resourceModel = $this->_getIndexer()->getResource();
        $resourceModel->beginTransaction();
        try {
            $this->_getIndexer()->rebuildIndex();
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
}
