<?php
class MageDoc_DirectoryCatalog_Model_Parser
    extends Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Advanced
{
    const DIRECTORY = 'catalog';

    protected $_brandsFound = array();

    public  function _construct()
    {
        $this->_init('directory_catalog/parser');
    }

    public function getBrand( $row ) {
        $brands = $this->getDirectoryModel()->getNormalizedSupplierOptionValues();
        //$supplierOptions = $this->getDirectoryModel()->getSupplierOptions();
        if (is_array($row) || $row instanceof Varien_Object){
            $brandName = $row['name'];
        } else {
            $brandName = $row;
        }
        $brandName = preg_replace('/\s+/', ' ', trim($brandName));

        foreach ($this->getAllNgrams(mb_strtolower($brandName, 'UTF-8')) as $key => $ngram) {
            if (isset($this->_brandsFound[$ngram])){
                return $this->_brandsFound[$ngram];
            }elseif (isset($brands[$ngram])){
                list($offset, $length) = explode('_', $key);
                $brandFound = implode(' ', array_slice(explode(' ', $brandName), $offset, $length));
                $this->_brandsFound[$ngram] = $brandFound;

                return $brandFound;
            }
        }

        return null;
    }

    public function getAllNgrams($string)
    {
        $string = preg_replace('/\s+/', ' ', $string);
        $words = explode(' ', $string);
        $ngrams = array();
        $count = count($words);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $count - $i; $j > 0; $j--) {
                $ngrams[$i . '_' . $j] = implode(' ', array_slice($words, $i, $j));
            }
        }
        return $ngrams;
    }
}