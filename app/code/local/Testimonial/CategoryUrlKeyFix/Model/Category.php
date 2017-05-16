<?php

class Testimonial_CategoryUrlKeyFix_Model_Category extends Mage_Catalog_Model_Category
{
    public function formatUrlKey($str)
    {
        $trans = array(
            'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 
            'ё'=>'yo','ж'=>'zh','з'=>'z', 'и'=>'i', 'й'=>'i', 'к'=>'k', 'л'=>'l', 
            'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 
            'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'c', 'ч'=>'ch','ш'=>'sh','щ'=>'sch',
            'ы'=>'y', 'э'=>'e', 'ю'=>'ju','я'=>'ja','А'=>'A', 'Б'=>'B', 'В'=>'V',
            'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ё'=>'Yo','Ж'=>'Zh','З'=>'Z', 'И'=>'I',
            'Й'=>'J', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 
            'Р'=>'R', 'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Х'=>'H', 'Ц'=>'C',
            'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch','Ы'=>'I','Э'=>'E', 'Ю'=>'Ju','Я'=>'Ja',
            'ь'=>'',  'Ь'=>'',  'ъ'=>'',  'Ъ'=>'');
        $str = strtr($str, $trans);
        
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
    }
    
}
