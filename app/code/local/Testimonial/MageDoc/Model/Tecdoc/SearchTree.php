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
class Testimonial_MageDoc_Model_Tecdoc_SearchTree extends Testimonial_MageDoc_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_searchTree');
    }   
    
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve level
     *
     * @return int
     */
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData('level');
    }
        
}

