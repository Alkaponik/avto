<?php

class Testimonial_MageDoc_Block_Breadcrumbs extends Mage_Core_Block_Template
{
    /**
     * Retrieve HTML title value separator (with space)
     *
     * @param mixed $store
     * @return string
     */
    public function getTitleSeparator($store = null)
    {
        $separator = (string)Mage::getStoreConfig('catalog/seo/title_separator', $store);
        return ' ' . $separator . ' ';
    }

    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Breadcrumbs
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('catalog')->__('Home'),
                'title'=>Mage::helper('catalog')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            $title = array();
            $path  = array();

            if ($type = Mage::registry('magedoc_type')){
                 $path['type'] = array(
                     'label' => $type->getTitle(),
                 );
                $model = $type->getModel();
            }
            if (isset($model) || ($model = Mage::registry('model'))){
                $path['model'] = array(
                    'label' => $model->getTitle(),
                    'link'  => $type ? $model->getUrl() : ''
                );
                $manufacturer = $model->getManufacturer();
            }
            if (isset($manufacturer) || ($manufacturer = Mage::registry('manufacturer'))){
                $path['manufacturer'] = array(
                    'label' => $manufacturer->getTitle(),
                    'link'  => $model ? $manufacturer->getUrl() : ''
                );
            }
            $path = array_reverse($path);

            foreach ($path as $name => $breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                $title[] = $breadcrumb['label'];
            }

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle(join($this->getTitleSeparator(), array_reverse($title)));
            }
        }
        return parent::_prepareLayout();
    }
}
