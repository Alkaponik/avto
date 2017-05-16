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
 * Fulltext Collection
 *
 * @category    Mage
 * @package     Testimonial_FlatCatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_FlatCatalogSearch_Model_Resource_Fulltext_Collection extends Testimonial_FlatCatalog_Model_Resource_Product_Collection
{
    /**
     * Retrieve query model object
     *
     * @return Testimonial_FlatCatalogSearch_Model_Query
     */
    protected function _getQuery()
    {
        return Mage::helper('flatcatalogsearch')->getQuery();
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return Testimonial_FlatCatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function addSearchFilter($query)
    {
        Mage::getSingleton('flatcatalogsearch/fulltext')->prepareResult();
        $isGroupMode = false;
        $groupColumns = array(
            'final_price' => 'final_price',
            'name' => new Zend_Db_Expr("CONCAT(main_table.name, ' ', IFNULL(s.SUP_BRAND, main_table.manufacturer), ' ', main_table.code)"));
        if ($isGroupMode){
            foreach ($groupColumns as $name => $expression){
                $expression = $expression instanceof Zend_Db_Expr
                    ? $expression
                    : "main_table.{$expression}";
                $groupColumns[$name] = new Zend_Db_Expr("GROUP_CONCAT({$expression} SEPARATOR \";\")");
            }
        }

        $tdResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $this->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array_merge(
                array(
                    'data_id',
                    'manufacturer' => new Zend_Db_Expr('IFNULL(s.SUP_BRAND, main_table.manufacturer)')
                ),$groupColumns)
            )
            ->joinInner(
                array('search_result' => $this->getTable('flatcatalogsearch/result')),
                $this->getConnection()->quoteInto(
                    'search_result.product_id=main_table.data_id AND search_result.query_id=?',
                    $this->_getQuery()->getId()
                ),
                array('relevance' => 'relevance')
            )
            ->joinInner(
                array('r' => $this->getTable('magedoc/retailer')),
                'main_table.retailer_id = r.retailer_id AND r.enabled = 1 AND r.show_on_frontend = 1',
                array('stock_status'))
            ->joinLeft(
                array('s' => $tdResource->getTable('magedoc/tecdoc_supplier')),
                'main_table.supplier_id = s.SUP_ID',
                '')
            ->joinLeft(
                array('dol' => $this->getTable('magedoc/directory_offer_link')),
                'main_table.data_id = dol.data_id AND dol.directory_code = "'.MageDoc_DirectoryCatalog_Model_Directory::CODE.'"',
                '')
            ->where('main_table.final_price IS NOT NULL AND main_table.name IS NOT NULL AND main_table.qty > 0  AND main_table.product_id IS NULL');
        if ($isGroupMode){
            $this->getSelect()
                ->group(array('code_normalized', 'IFNULL(s.SUP_BRAND, main_table.manufacturer)'));
        }

        $this->addFilterToMap('manufacturer', 'dol.supplier_id');
        $this->addFilterToMap('generic_article', 'dol.generic_article_id');

        return $this;
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return Testimonial_FlatCatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        if ($attribute == 'relevance') {
            $this->getSelect()->order("relevance {$dir}");
        } else {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Stub method for campatibility with other search engines
     *
     * @return Testimonial_FlatCatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    public function getSetIds()
    {
        return array();
    }

    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        return $this;
    }
}
