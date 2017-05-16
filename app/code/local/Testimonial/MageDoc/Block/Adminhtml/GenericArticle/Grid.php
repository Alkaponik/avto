<?php
class Testimonial_MageDoc_Block_Adminhtml_GenericArticle_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const DEFAULT_DIRECTORY = 'tecdoc';
    const FORM_ELEMENTS_PREFIX = 'mapItem';
    const SUGGEST_ACTION_NAME = 'suggest';

    protected $_collection;
    protected $_collectionModelName = 'magedoc/retailer_genericArticle_map_collection';

    public function __construct()
    {
        parent::__construct();
        $this->setId(static::FORM_ELEMENTS_PREFIX);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        if (!isset($this->_collection)) {
            $directory = $this->getRequest()->getParam('directory');
            $directory = $directory ? : static::DEFAULT_DIRECTORY;
            $this->_collection = Mage::getResourceModel($this->_collectionModelName)
                ->addFieldToFilter('main_table.directory_code', $directory);

            $tdResource = Mage::getResourceSingleton('magedoc/tecdoc_article');
            if($this->_isSuggestAction()) {
                $this->_collection->getSelect()
                    ->joinInner(
                        array('ird' => $tdResource->getTable('magedoc/import_retailer_data')),
                        'ird.name = main_table.name',
                        ''
                    )->joinInner(
                        array('dol' => $tdResource->getTable('magedoc/directory_offer_link')),
                        'dol.data_id = ird.data_id AND dol.directory_code = "tecdoc"',
                        ''
                    )->joinInner(
                        array('linkArtGa' => $tdResource->getTable('magedoc/tecdoc_linkArtGA')),
                        'linkArtGa.LAG_ART_ID = dol.directory_entity_id',
                        ''
                    )->joinInner(
                        array('genericArticle' => $tdResource->getTable('magedoc/tecdoc_genericArticle')),
                        'linkArtGa.LAG_GA_ID = genericArticle.GA_ID',
                        ''
                    )
                    ->where('CHAR_LENGTH(main_table.name) > 3')
                    ->group(array('main_table.ga_map_id'));
                $this->_collection->addFieldToFilter('main_table.generic_article_id', array('null' => true));

                $tdResource->joinDesignation($this->getCollection()->getSelect(), 'genericArticle', 'GA_DES_ID',
                    array(
                        'GROUP_CONCAT(DISTINCT td_desText.TEX_TEXT SEPARATOR \'~\') as generic_article_suggested',
                        'GROUP_CONCAT(DISTINCT genericArticle.GA_ID) as generic_article_id_suggested'
                        ));
            }
            $this->_collection->addFilterToMap('generic_article_id', 'IFNULL(main_table.generic_article_id, 0)');
        }

        parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('ga_map_id',
            array(
                 'header'    => Mage::helper('magedoc')->__('ID'),
                 'align'     =>'right',
                 'width'     => '50px',
                 'index'     => 'ga_map_id',
            )
        );

        $this->addColumn('name',
            array(
                 'header'    => Mage::helper('magedoc')->__('Name'),
                 'align'     =>'right',
                 'width'     => '80px',
                 'index'     => 'name',
                 'filter_index' => 'main_table.name'
            )
        );

        $this->addColumn('name_normalzied',
            array(
                 'header'    => Mage::helper('magedoc')->__('Name normalized'),
                 'align'     =>'right',
                 'width'     => '80px',
                 'index'     => 'name_normalzied',
            )
        );

        $this->addColumn('retailer_id',
            array(
                 'header'    => Mage::helper('magedoc')->__('Retailer'),
                 'type'      => 'options',
                 'index'     => 'retailer_id',
                 'width'     => '100px',
                 'filter_index' => 'main_table.retailer_id',
                 'options'   => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
                 'internal_options' => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
                 'totals_label' => '',
                 'filter_condition_callback' =>  array($this, '_retailerIdFilterCallback')
            )
        );


        if($this->_isSuggestAction()) {
            $this->addColumn('generic_article_suggested',
                array(
                    'header'    => Mage::helper('magedoc')->__('Suggested generic articles'),
                    'align'     =>'right',
                    'width'     => '80px',
                    'index'     => 'generic_article_suggested',
                    'column_css_class' => 'generic_article_suggested',
                    'frame_callback' => array($this, 'wrapSuggestedGenericArticles'),
                )
            );
        }

        $genericArticleOptions = Mage::getModel('magedoc/source_genericArticle')->getOptionArray();

        $this->addColumn('generic_article_id',
            array(
                'type'      => 'options',
                'header'    => Mage::helper('magedoc')->__('Generic Article'),
                'index'     => 'generic_article_id',
                'width'     => '100px',
                'element_style'=> 'min-width:100px;width:100%;',
                'options'   => array_merge(
                    array('' => '', 0 => $this->__('Not linked to directory')),
                    $genericArticleOptions),
                'internal_options' => $genericArticleOptions,
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_combobox',
                'filter'    => 'magedoc/adminhtml_widget_grid_column_filter_combobox',
                'column_css_class' => 'generic_article_id_container',
                'settings'  => array(
                    'delayOptionsInitialization' => true,
                    'withEmpty'                  => true
                ),
            )
        );

        $this->addColumn('title',
            array(
                 'header'    => Mage::helper('magedoc')->__('Title'),
                 'align'     =>'right',
                 'width'     => '80px',
                 'index'     => 'title',
            )
        );

        $this->addColumn('frequency',
            array(
                 'header'    => Mage::helper('magedoc')->__('Frequency'),
                 'align'     =>'right',
                 'width'     => '80px',
                 'index'     => 'frequency',
            )
        );

        $this->addColumn('status',
            array(
                 'header'    => Mage::helper('magedoc')->__('Status'),
                 'align'     =>'right',
                 'width'     => '80px',
                 'index'     => 'status',
            )
        );


        return parent::_prepareColumns();
    }

    protected function _retailerIdFilterCallback()
    {
        $condition = $this->getColumn('retailer_id')->getFilter()->getCondition();

        if($condition && $condition['eq'] === "0") {
            if (!$this->_isSuggestAction()){
                $this->getCollection()->getSelect()
                    ->joinLeft(
                        array(
                             'ird' =>
                             Mage::getResourceSingleton('magedoc/tecdoc_article')
                                 ->getTable('magedoc/import_retailer_data')
                        ),
                        'ird.name = main_table.name',
                        '')
                    ->group('main_table.ga_map_id');
            }
            $countExpr = new Zend_Db_Expr('count(*)');
            $this->getCollection()->getSelect()->columns(array('cc' => $countExpr));
            $this->getCollection()->addFilterToMap('cc', $countExpr);
            $this->addColumn('statistics',
                array(
                     'header'    => Mage::helper('magedoc')->__('Statistics'),
                     'align'     =>'right',
                     'width'     => '80px',
                     'index'     => 'cc',
                )
            );
        }
    }

    protected function _prepareGrid()
    {
        parent::_prepareGrid();
        $this->_prepareJavascript();
        return $this;
    }

    protected function _prepareJavascript()
    {
        $javaScript = <<<JS
            $$('.suggested-generic-article').each(function(element) {
                element.on('click', function(event) {
                    var comboContainer =  element.up('tr').down('td.generic_article_id_container .combo-container');
                    var combobox =   eval(comboContainer.id + '_combobox');
                    combobox.input.value = element.innerHTML;
                    combobox.filter(element.innerHTML);
                    var genericArticleId = $(element).readAttribute('data-generic_article_id');
                    if (combobox.select.options.length == 0 && genericArticleId){
                        combobox.addOptionToSelect(element.innerHTML, genericArticleId, true);
                    }
                    Event.stop(event);
                    /*
                    combobox.getRequestData(null,null,null,null,null,
                        function(combobox, text, value) {
                             text.value = value;
                             combobox.filter(value);
                        }.curry(combobox, comboContainer.down('input[type=text]'), element.innerHTML));*/
                });
             });
JS;

        $genericArticles  = Mage::getModel('magedoc/source_genericarticle')->getOptionArray();
        $genericArticles = json_encode($genericArticles);
        $javaScript .= <<<JS
            var genricArticleOptions = $genericArticles;
            for (var i = 0; i < window.{$this->getId()}_generic_article_idComboboxStorage.length; i++) {
                window.{$this->getId()}_generic_article_idComboboxStorage[i].data = genricArticleOptions;
            }
JS;
        $this->setAdditionalJavaScript($javaScript);
    }

    protected function _isSuggestAction()
    {
        return $this->getRequest()->getActionName() == static::SUGGEST_ACTION_NAME;
    }

    public function wrapSuggestedGenericArticles($value, $row, $column, $isExport)
    {
        $values = explode('~', $value);
        $genericArticleIds = explode(',', $row->getGenericArticleIdSuggested());
        $options = array();
        foreach ($values as $key => $value ){
            $genericArticleId = isset($genericArticleIds[$key])
                ? $genericArticleIds[$key]
                : null;
            $options []= "<span class=\"suggested-generic-article\" data-generic_article_id=\"{$genericArticleId}\">{$value}</span>";
        }
        return implode(', ', $options);
    }
}