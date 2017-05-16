<?php
class MageDoc_DirectoryTire_Model_Parser
    extends Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Advanced
{
    const DIRECTORY = 'tire';
    const SUMMER = 'Летняя';
    const WINTER = 'Зимняя';
    const ALL_SEASONS = 'Всесезонная';
    const MAX_BUNCH_SIZE = 100;

    protected $__tireSizePattern
        = "(\d{1,3}[\s\/]\d{2}([\.\,]\d{1,2})?\s?Z?\s?[\/\-R]\s?\d{1,2}(\s?C(\s|$))?)";
    protected $_tireSizePattern
        = "((\d{2,3}[\s\/]\d{2}\s?(Z\s?)?[\/\-R]\s?\d{1,2}([\.\,]\d{1,2})?|\d{1,3}([\.\,]\d{1,2})?\s?R\d{1,2}([\.\,]\d{1,2})?)(\s?C(\s|$))?)";
    protected $_tireLoadingSpeedIndexPattern = "\s(\d{2,3}(\/\d{2,3})?|\d{4,6})\s?([b-y]|vr|zr)(-|\s|$)";

    protected $_defaultFieldFilters = array(
        array(
            'base_table_field'  => 'code',
            'path'              => '{{::getModel(@)}} {{::getSize(@)}}'
        ),
        array(
            'base_table_field'  => 'code_normalized',
            'path'              => '{{::getCodeNormalized(@)}}'
        ),
        array(
            'base_table_field'  => 'size',
            'path'              => '{{::getSize(@)}}'
        ),
    );

    public  function _construct()
    {
        $this->_init('directory_tire/parser');
    }

    public function getCodeNormalized($row)
    {
        $size = $this->getSize($row);
        $additionalMark = '';
        if( strtolower(substr( $size, -1 )) == 'c' ) {
            $additionalMark = 'c';
        }

        $model = $this->getModel($row);
        //var_dump($model); die;
        $model = strtolower(preg_replace("/[^A-z0-9]+/", '', $model));

        $size = preg_replace("/\D+/",'',$size) . $additionalMark;
        $size = strtolower(preg_replace("/[^A-z0-9]+/", "", $size));

        return  $model . '_' . $size;
    }

    public function getBrand( $tire ) {
        $brands = $this->getResource()->getBrands();
        foreach($brands as $brandId => $brand) {
            if( strpos( mb_strtolower($tire['name']), mb_strtolower($brand['brand_name'])) !== false ) {
                $tire['brand_id'] = ($brand['correct_brand_name_id'] == 0) ? $brandId : $brand['correct_brand_name_id'] ;
                $tire['brand_name'] = $brands[$tire['brand_id']]['brand_name'];
                $tire['supplier_id'] = $tire['brand_id'];
                $tire['manufacturer'] = $brands[$brandId]['brand_name'];
                break;
            }
        }

        return $tire['manufacturer'];
    }

    public function getModel($row, $field = 'code_raw')
    {

        $normalizedName = $this->_normalizeTireName($row['name'] );

        $normalizedName = preg_replace( "/{$this->_tireSizePattern}/i", '', $normalizedName);
        $normalizedName = preg_replace( "/{$this->_tireLoadingSpeedIndexPattern}/i", '', $normalizedName);
        return trim(str_ireplace(array($row['manufacturer']), '', $normalizedName));
    }

    protected function _normalizeTireName( $name )
    {
        $nameNormalizationRules = array (
            "/\s{2,}/" => " ",
            "/[\(\)]/" => " ",
            "/(\(шт(.*?)\)|(п\/ш)|(\(?шип\)?))|шина|(бес)?камерная/i" => "",
            "/(нкшз)/" => "",
        );
        $name = mb_strtolower($name, 'CP1251');
        foreach($nameNormalizationRules as $pattern=>$replaceTo) {
            if( is_array($pattern) ) {
                $name = str_replace($pattern, $replaceTo, $name);
            } else {
                $name = preg_replace( "{$pattern}", $replaceTo, $name );
            }
        }

        $name = $this->_deCyrillize($name);
        return trim($name);
    }

    public function getSize( $row )
    {
        $tireSize = array();
        $result = '';
        if(preg_match( "/{$this->_tireSizePattern}/i",  $row['name'] . $row['description'] , $tireSize )) {
            $result = preg_replace("/\s+/", "",$tireSize[0]);
            $result = $this->_deCyrillize($result);
        }

        $sizeParts = explode('/', $result);
        if (stripos($result, 'R') === false
            && count($sizeParts) == 3){
            $result = "{$sizeParts[0]}/{$sizeParts[1]}R{$sizeParts[2]}";
        }
        return $result;
    }

    protected function _deCyrillize( $name )
    {
        $cyril = array('К', 'Е', 'Н', 'Х', 'В', 'А', 'Р', 'О', 'С', 'М', 'Т', 'е', 'х', 'а', 'р', 'о', 'с');
        $latin = array('K', 'E', 'H', 'X', 'B', 'A', 'P', 'O', 'C', 'M', 'T', 'e', 'x', 'a', 'p', 'o', 'c');
        return str_replace( $cyril, $latin, $name);
    }

    protected function _getLoadingSpeedIndex( $row )
    {
        $normalizedName = $this->_normalizeTireName($row['name']);
        $normalizedName = preg_replace( "/{$this->_tireSizePattern}/i", '', $normalizedName);
        $loadingSpeedIndex = array();
        preg_match( "/{$this->_tireLoadingSpeedIndexPattern}/", $normalizedName, $loadingSpeedIndex );
        $loadingSpeedIndex = isset($loadingSpeedIndex[0]) ? trim($loadingSpeedIndex[0]) : '';
        return  trim($loadingSpeedIndex, ' -');;
    }

    public function getLoading($row)
    {
        $loadingSpeedIndex = $this->_getLoadingSpeedIndex($row);

        $speed = trim(mb_substr($loadingSpeedIndex, -1));
        $speedIndexExists = !preg_match("/\d/",$speed);

        if($speedIndexExists) {
            $loading = trim(mb_substr($loadingSpeedIndex, 0, -1));
        } else {
            $loading = $loadingSpeedIndex;
        }

        return $loading;
    }

    public function getSpeed($row)
    {
        $loadingSpeedIndex = $this->_getLoadingSpeedIndex($row);

        $speed = trim(mb_substr($loadingSpeedIndex, -1));
        $speedIndexExists = !preg_match("/\d/", $speed);

        if(!$speedIndexExists) {
            $speed = '';
        }

        return $speed;
    }

    public function getSeason($row)
    {
        $name = $row['name'] . ' ' . $row['description'];

        $name = mb_strtolower($name, 'UTF-8' );

        if (preg_match('/зимн|зима/', $name)) {
            $season  = static::WINTER;
        } elseif (preg_match('/летн|лето/', $name)) {
            $season = static::SUMMER;
        } elseif (preg_match('/грузов|всесезон|универс|в\/с/', $name)) {
            $season = static::ALL_SEASONS;
        } else {
            $season = null;
        }

        return $season;
    }

}