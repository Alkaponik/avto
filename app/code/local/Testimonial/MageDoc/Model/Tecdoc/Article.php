<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Articles
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Tecdoc_Article extends Testimonial_MageDoc_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_article');
    }   

    public function processItemGraphicsAfterLoad($item)
    {
        $imagePath = $item->getImagePath();
        if(strlen($imagePath)) {
            $imagePath = array_unique(explode(',', $imagePath));
            $imageSort = explode(',', $item->getImageSort());
            $filteredSort = array();
            $pathPrefix = Mage::helper('magedoc')->getImagePathPrefix();
            foreach ($imagePath as $key => $path) {
                if (isset($imageSort[$key])) {
                    $filteredSort[$key] = $imageSort[$key];
                }
                if ($pathPrefix){
                    $imagePath[$key] = $pathPrefix.$path;
                }
            }
            $item->setImagePath($imagePath);
            $item->setImageSort($filteredSort);
        }
        return $this;
    }
    
    
    
    protected function _afterLoad() {
        parent::_afterLoad();
        $this->processItemGraphicsAfterLoad($this);
    }
}

