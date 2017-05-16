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
 * @category   Mage
 * @package    tools
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Translate {
    static private $opts; # object of MultyGetopt
    static private $csv; # object of Varien_File_Csv_multy
    static private $parseData; # parsering data file "__()";
    static private $CONFIG; # data from config.inc.php
    static private $allParseData=array();
    /**
     *Starting checking process
     *
     * @param   none
     * @return  none
     */
    static public function run($config)
    {
        self::$CONFIG = $config;
        self::$csv = new Varien_File_Csv_multy();
        try {
            self::$opts = new MultyGetopt(array(
                'path=s'     => 'Path to root directory',
                'validate-s' => 'Validates selected translation against the default (en_US)',
                'generate'   => 'Generates the default translation (en_US)',
                'update-s'   => 'Updates the selected translation with the changes (if any) from the default one (en_US)',
                'dups'       => 'Checks for duplicate keys in different modules (in the default translation en_US)',
                'sort-s'     => 'Sorting '.EXTENSION.' file(s) by keys',
                'key-s'      => 'Duplication key',
                'file-s'     => 'Make validation of this file(s)'
              ));
             self::$opts->setOption('dashDash',false);
             self::$opts->parse();

        } catch (Zend_Console_Getopt_Exception $e) {
            self::_error($e->getUsageMessage());
        }

        $path = self::$opts->getOption('path');
        $validate = self::$opts->getOption('validate');
        $generate = self::$opts->getOption('generate');
        $update = self::$opts->getOption('update');
        $dups = self::$opts->getOption('dups');
        $sort = self::$opts->getOption('sort');
        $file = self::$opts->getOption('file');
        $key_dupl = self::$opts->getOption('key');
        $dir_en = $path.self::$CONFIG['paths']['locale'].'en_US/';

        if($validate===null && $dups===null && $update===null && $generate===null && $sort===null){
            self::_error('type "php translate.php -h" for help.');
        }

        if(!is_dir($dir_en)){
            self::_error('Locale dir '.$dir_en.' is not found');
        }
        if($validate===true){
            self::_error("Please specify language of validation");
        }
        if($update===true){
            self::_error("Please specify language of updating");
        }
        if($sort===true){
            self::_error("Please specify language of sorting");
        }

        if($validate!==null && $validate!==false){
            $dir = $path.self::$CONFIG['paths']['locale'].$validate.'/';
            self::_callValidate($file, $dir, $dir_en);
            return;
        }
        if($generate!==null && $generate!==false){
            self::_callGenerate($file, $path, $dir_en);
            return;
        }
        if($update!==null && $update!==false){
            $dir = $path.self::$CONFIG['paths']['locale'].$update.'/';
            self::_callUpdate($file, $dir, $dir_en);
            return;
        }
        if($sort!==null && $sort!==false){
            $dir = $path.self::$CONFIG['paths']['locale'].$sort.'/';
            self::_callSort($file, $dir);
            return;
        }
        if($dups!==null && $dups!==false){
            if($key_dupl===null || $key_dupl===false || $key_dupl === true) $key_dupl=null;
            self::_callDups($key_dupl,$path);
            return;
        }


    }
    /**
     *Call validation process
     *
     * @param   string $file - files array
     * @param   string $dir - dir to comparing files
     * @param   string $dir_en - dir to default english files
     * @return  none
     */
    static protected function _callValidate($file, $dir, $dir_en)
    {
        if(!is_dir($dir)){
            self::_error('Specific dir '.$dir.' is not found');
        }
        if(!($file===null || $file === false || $file === true ) ){
            if(!is_array($file)){
                self::checkFiles($dir_en.$file.'.'.EXTENSION,$dir.$file.'.'.EXTENSION);
            } else {
                for($i=0;$i<count($file);$i++){
                    self::checkFiles($dir_en.$file[$i].'.'.EXTENSION,$dir.$file[$i].'.'.EXTENSION);
                }
            }
        } else {
            $dirCol = new Varien_Directory_Collection($dir,false);
            $dirCol->addFilter("extension",self::$CONFIG['allow_extensions']);
            $dirCol->useFilter(true);
            $files = $dirCol->filesName();
            foreach ($files as $file_in_dir){
                self::checkFiles($dir_en.$file_in_dir,$dir.$file_in_dir);
            }

        }
    }
    /**
     *Call generation process
     *
     * @param   string $file - files array
     * @param   string $path - root path
     * @param   string $dir_en - dir to default english files
     * @param   int $level - level of recursion
     * @return  none
     */
    static protected function _callGenerate($file,  $path, $dir_en, $level=0, $doSave=true)
    {
        static $files_name_changed = array();

        if(!($file===null || $file === false  || $file === true) ){
            if(!is_array($file)){
                if(isset(self::$CONFIG['translates'][$file])){
                    self::$parseData = array();
                    $dirs='';
                    $files = '';
                    foreach(self::$CONFIG['translates'][$file] as $item_name){
                        $path_to_item = $path.$item_name;
                        if(is_dir($path_to_item)) {
                            $files = array();
                            $dirColl = new Varien_Directory_Collection($path_to_item,true);
                            $dirColl->addFilter("extension",self::$CONFIG['allow_extensions']);
                            $dirColl->useFilter(true);
                            $files = $dirColl->filesPaths();
                            for($a=0;$a<count($files);$a++){
                                self::_parseFile($files[$a],self::$parseData,$file);
                            }
                        } else {
                            if(is_file($path_to_item)){
                                self::_parseFile($path_to_item,self::$parseData,$file);
                            } else {
                                self::_error("Could not found specific module for file ".$file." in ".self::$CONFIG['paths']['mage']);
                            }
                        }
                    }

                    /*
                    $dup = self::checkDuplicates(self::$parseData);
                    if(!file_exists($dir_en.$file.'.'.EXTENSION)){
                        fclose(fopen($dir_en.$file.'.'.EXTENSION, 'w'));
                    }
                    try{
                        $data_en = self::$csv -> getDataPairs($dir_en.$file.'.'.EXTENSION);
                    } catch (Exception $e){
                        self::_error($e->getMessage());
                    }
                    $parse_data_arr = array();
                    foreach (self::$parseData as $key => $val){
                        $parse_data_arr[$val['value']]=array('line'=>$val['line'].' - '.$val['file']);
                    }
                    // swap missing <-> redundant when comparing generated files
                    $res = self::checkArray($data_en,$parse_data_arr,'redundant','missing');
                    $res['duplicate'] = $dup;
                    self::_output($file,$res,$dir_en.$file);
                    $unique_array = array();
                    $csv_data = array();
                    foreach (self::$parseData as $val){
                        array_push($unique_array,$val['value']);
                    }
                    $unique_array = array_unique($unique_array);
                    natcasesort ($unique_array);
                    foreach ($unique_array as $val){
                        if(isset($data_en[$val]['value'])){
                            array_push($csv_data,array($val,$data_en[$val]['value']));
                        }
                        else
                            array_push($csv_data,array($val,$val))	;
                    }
                    self::$csv -> saveData($dir_en.$file.'.'.EXTENSION,$csv_data);
                    array_push($files_name_changed,$file);
                     */
                    self::$allParseData = array_merge(self::$allParseData, self::$parseData);
                    if ($doSave) self::_saveInBatchMode($dir_en);
                } else {
                    print "Skip ".$file." (not found configuration for this module in config.inc.php\n";
                }
            } else {
                for($a=0;$a<count($file);$a++){
                    self::_callGenerate($file[$a],$path,$dir_en,$level+1,false);
                }
                self::_saveInBatchMode($dir_en);
            }
        } else {
            foreach (self::$CONFIG['translates'] as $key=>$val){
                self::_callGenerate($key,$path,$dir_en,$level+1,false);
            }
            self::_saveInBatchMode($dir_en);
        }
        /*
        if(isset($files_name_changed) && $level==0){
            print "Created files:\n";
            foreach ($files_name_changed as $val){
                print "\t".$val.".".EXTENSION."\n";
            }
            print "Created diffs:\n";
            foreach ($files_name_changed as $val){
                print "\t".$val.".changes.".EXTENSION."\n";
            }

        }
         */
    }
    static protected function _saveInBatchMode($dir_en)
    {
        static $files_name_changed = array();
        $allUniqueArray = array();
        $allModules = array();
        foreach (self::$allParseData as $val){
            if (!isset($allUniqueArray[$val['mod_name']])) $allUniqueArray[$val['mod_name']] = array();
            array_push($allUniqueArray[$val['mod_name']],$val['value']);
            if (!isset($parseDataByModule[$val['mod_name']])) $parseDataByModule[$val['mod_name']] = array();
            array_push($parseDataByModule[$val['mod_name']],$val);
            $allModules[$val['mod_name']] = true;
        }
        foreach( array_keys($allModules) as $__module ) {
            $allUniqueArray[$__module] = array_unique($allUniqueArray[$__module]);
            natcasesort($allUniqueArray[$__module]);
            $parseData = $parseDataByModule[$__module];
            $dup = self::checkDuplicates($parseData);
            if(!file_exists($dir_en.$__module.'.'.EXTENSION)){
                fclose(fopen($dir_en.$__module.'.'.EXTENSION, 'w'));
            }
            try{
                $data_en = self::$csv -> getDataPairs($dir_en.$__module.'.'.EXTENSION);
            } catch (Exception $e){
                self::_error($e->getMessage());
            }
            $parse_data_arr = array();
            foreach ($parseData as $key => $val){
                $parse_data_arr[$val['value']]=array('line'=>$val['line'].' - '.$val['file']);
            }
            // swap missing <-> redundant when comparing generated files
            $res = self::checkArray($data_en,$parse_data_arr,'redundant','missing');
            $res['duplicate'] = $dup;
            self::_output($__module,$res,$dir_en.$__module);
        }
        foreach ( $allUniqueArray as $file2save => $unique_array ) {
            $csv_data = array();
            foreach ($unique_array as $val){
                if(isset($data_en[$val]['value'])){
                    array_push($csv_data,array($val,$data_en[$val]['value']));
                }
                else
                    array_push($csv_data,array($val,$val))	;
            }
            array_push($files_name_changed,$file2save);
            self::$csv -> saveData($dir_en.$file2save.'.'.EXTENSION,$csv_data);
        }
        if(isset($files_name_changed)){
            print "Created files:\n";
            foreach ($files_name_changed as $val){
                print "\t".$val.".".EXTENSION."\n";
            }
            print "Created diffs:\n";
            foreach ($files_name_changed as $val){
                print "\t".$val.".changes.".EXTENSION."\n";
            }

        }
    }
    /**
     *Call updating process
     *
     * @param   string $file - files array
     * @param   string $dir - dir to comparing files
     * @param   string $dir_en - dir to default english files
     * @return  none
     */
    static protected function _callUpdate($file,  $dir, $dir_en)
    {
        if(!is_dir($dir)){
            self::_error('Specific dir '.$dir.' is not found');
        }
        if(!($file===null || $file === false || $file === true ) ){
            if(!is_array($file)){
                $files_name_changed[] = $file;
                self::_checkFilesUpdate($dir_en.$file.'.'.EXTENSION,$dir.$file.'.'.EXTENSION);
            } else {
                for($i=0;$i<count($file);$i++){
                    $files_name_changed[] = $file[$i];
                    self::_checkFilesUpdate($dir_en.$file[$i].'.'.EXTENSION,$dir.$file[$i].'.'.EXTENSION);
                }
            }
        } else {
            $dirColl = new Varien_Directory_Collection($dir,true);
            $dirColl->addFilter("extension",self::$CONFIG['allow_extensions']);
            $dirColl->useFilter(true);
            $files = $dirColl->filesName();
            foreach ($files as $file_in_dir) {
                $files_name_changed[] = $file_in_dir;
                self::_checkFilesUpdate($dir_en.$file_in_dir,$dir.$file_in_dir);
            }
        }
        if(isset($files_name_changed)){
            print "Created diffs:\n";
            foreach ($files_name_changed as $val){
                print "\t".$val."\n";
            }
            print "Created files:\n";
            foreach ($files_name_changed as $val){
                print "\t".basename($val).".changes.".EXTENSION."\n";
            }

        }
    }
    /**
     *Call sorting process
     *
     * @param   string $file - files array
     * @param   string $dir - dir to comparing files
     * @return  none
     */
    static protected function _callSort($file,  $dir)
    {
        if(!is_dir($dir)){
            self::_error('Specific dir '.$dir.' is not found');
        }
        if(!($file===null || $file === false || $file === true ) ){
            if(!is_array($file)){
                self::sortFile($dir.$file.'.'.EXTENSION);
                $files_name_changed[] = $file;
            } else {
                for($i=0;$i<count($file);$i++){
                    $files_name_changed[] = $file[$i];
                    self::sortFile($dir.$file[$i].'.'.EXTENSION);
                }
            }
        } else {
            $dirColl = new Varien_Directory_Collection($dir,true);
            $dirColl->addFilter("extension",EXTENSION);
            $dirColl->useFilter(true);
            $files = $dirColl->filesName();
            foreach ($files as $file_in_dir) {
                $files_name_changed[] = $file_in_dir;
                self::sortFile($dir.$file_in_dir);
            }
        }
        if(isset($files_name_changed)){
            print "Updated files:\n";
            foreach ($files_name_changed as $val){
                print "\t".basename($val).'.'.EXTENSION."\n";
            }

        }
    }
    /**
     *Call duplicat checking process
     *
     * @param   string $key - key checking
     * @param   string $path - path to root
     * @return  none
     */
    static protected function _callDups($key,$path)
    {
            self::$parseData = array();
            $dirs='';
            $files = '';
            foreach (self::$CONFIG['translates'] as $mod_name=>$path_arr){
                foreach(self::$CONFIG['translates'][$mod_name] as $dir_name){
                    $dir = $path.$dir_name;
                    if(is_dir($dir)) {
                        $files = array();
                        $dirs = array();
                        $files = array();
                        $dirColl = new Varien_Directory_Collection($dir,true);
                        $dirColl->addFilter("extension",self::$CONFIG['allow_extensions']);
                        $dirColl->useFilter(true);
                        $files = $dirColl->filesPaths();
                        for($a=0;$a<count($files);$a++){
                            self::_parseFile($files[$a],self::$parseData,$mod_name);
                        }
                    } else {
                        self::_error("Could not found specific module ".$dir);
                    }
                }
            }
            $dup = self::checkDuplicates(self::$parseData,true);
            if($key===null){
                uksort($dup, 'strcasecmp');
                foreach ($dup as $key=>$val){
                    print '"'.$key.'":'."\n";
                    $out = $dup[$key]['line'];
                    $out = explode(',',$out);
                    for($a=0;$a<count($out);$a++){
                        print "\t".ltrim($out[$a]," ")."\n";
                    }
                    print "\n\n";
                }
            } else {
                print '"'.$key.'":'."\n";
                $out = $dup[$key]['line'];

                $out = explode(', ',$out);
                for($a=0;$a<count($out);$a++){
                    print "\t".ltrim($out[$a]," ")."\n";
                }
            }

    }
    /**
     *sort file
     *
     * @param   string $file - file to sort
     * @return  none
     */
    static public function sortFile($file){
        try {
            $data = self::$csv -> getDataPairs($file);
        } catch (Exception $e) {
           self::_error($e->getMessage());
        }
        $csv_data = array();
        foreach ($data as $key=>$val){
            $pre_data[$key]=$val['value'];
        }
        uksort($pre_data, 'strcasecmp');
        foreach ($pre_data as $key => $val){
            if(isset($data[$key]['value'])){
                array_push($csv_data,array($key,$data[$key]['value']));
            } else {
                array_push($csv_data,array($key,$val))	;
            }
        }
        self::$csv -> saveData($file,$csv_data);
    }
    /**
     *return array of duplicate parsering data
     *
     * @param   array $data - array of data
     * @return  array - duplicates array
     */
    static public function checkDuplicates($data)
    {
        $dupl = array();
        $check_arr = array();
        foreach ($data as $val){
            if(isset($val['mod_name'])){
                $mod_name = $val['mod_name'].' from ';
            } else {
                $mod_name = '';
            }
            if(isset($check_arr[$val['value']])){
                if(isset($dupl[$val['value']])){
                    $dupl[$val['value']]['line'].=', '.$mod_name.$val['line'].'-'.$val['file'];
                } else {
                    $dupl[$val['value']]['line']=$check_arr[$val['value']].', '.$mod_name.$val['line'].'-'.$val['file'];
                }
            } else {
                $check_arr[$val['value']] = $mod_name.$val['line'].'-'.$val['file'];
            }
        }
        return $dupl;
    }
    /**
     * Parsering xml file
     * @param   string $file - xml file to parse
     * @param   array $data_arr - array of data
     * @return  none
     */
    static public function parseXml($file,&$data_arr,$mod_name=null){
        $xml = new Varien_Simplexml_Config();
        $xml->loadFile($file,'SimpleXMLElement');
        $arr = $xml->getXpath("//*[@translate]");
        unset($xml);
        if(is_array($arr)){
            foreach ($arr as $val){
                if(is_a($val,"Varien_Simplexml_Element")){
                    $attr = $val->attributes();
                    $transl = $attr['translate'];
                    $transl = explode(' ', (string)$transl);
                    foreach ($transl as $v) {
                        $inc_arr['value']=(string)$val->$v;
                        $inc_arr['line']='';
                        $inc_arr['file']=$file;
                        $inc_arr['mod_name'] = $mod_name;
                        array_push($data_arr,$inc_arr);
                    }
                }
            }
        }
    }
    /**
     * Parsering file on "__()"
     * @param   string $file - file to parse
     * @param   array $data_arr - array of data
     * @param   $mod_name - name of module
     * @return  none
     */
    static public function parseTranslatingFiles($file,&$data_arr,$mod_name=null){
        global $CONFIG;
        $line_num = 0;
        $f = @fopen($file,"r");
        if(!$f){
            self::_error('file '.$file.' not found');
        }
        while (!feof($f)) {
                $line = fgets($f, 4096);
                $line_num++;
                $results = array();
                preg_match_all('/(Mage::helper\s*\(\s*[\'"]([^\'"]*)[\'"]\s*\)\s*->\s*)?__\(\s*([\'"])(.*?[^\\\])\3.*?[),]/',$line,$results,PREG_SET_ORDER);
                for($a=0;$a<count($results);$a++){
                    $inc_arr = array();
                    if(isset($results[$a][4])){
                        $inc_arr['value']=preg_replace('/(?<!\\\)\\\\\'/', "'", $results[$a][4]);
                        $inc_arr['line']=$line_num;
                        $inc_arr['file']=$file;
                        //if ( $results[$a][4] == 'Shipment #%s (%s)' ) {print_r($results);die();}
                        if(!empty($results[$a][2])&&isset($CONFIG['helpers'][$results[$a][2]])){$inc_arr['mod_name'] = $CONFIG['helpers'][$results[$a][2]];}
                        elseif(!empty($mod_name)){$inc_arr['mod_name'] = $mod_name;}
                        else {$inc_arr['mod_name'] = $file;}
                        array_push($data_arr,$inc_arr);
                    }
                }
            }
    }
    /**
     *Parsering files on keywords
     *
     * @param   string $file - file path
     * @param   array &$data_arr - array of data
     * @param   string $mod_name - module name of parsered file
     * @return  array $data_arr
     */
    static protected function _parseFile($file,&$data_arr,$mod_name=null)
    {
        if(Varien_File_Object::getExt($file)==='xml'){
            self::parseXml($file,&$data_arr,$mod_name);
        } else {
            self::parseTranslatingFiles($file,&$data_arr,$mod_name);
        }
        return $data_arr;
    }

    /**
     *Display error message
     *
     * @param   string $msg - message to display
     * @return  none
     */
    static protected function _error($msg)
    {
        echo "\n" . USAGE . "\n\n";
        echo "ERROR:\n\n".$msg."\n\n";
        exit();
    }

    /**
     *Compare arrays with pairs of CSV file's data and return array of lack of coincidences
     *
     * @param   array $arr_en - array of pairs of CSV default english file data
     * @param   array $arr - array of pairs of CSV comparing file data
     * @return  array $array - array of lack of coincidences
     */
    static public function checkArray($arr_en,$arr, $missing = 'missing', $redundant = 'redundant')
    {

        $err = array();
        $err[$missing] = array();
        $err[$redundant] = array();
        $err['duplicate'] = array();
        foreach ($arr_en as $key=>$val){
            if(!isset($arr[$key])) {
                $err[$missing][$key] = array();
                $err[$missing][$key]['line']=$arr_en[$key]['line'];
                $err[$missing][$key]['value']=$arr_en[$key]['value'];
            }
        }

        foreach ($arr as $key=>$val){
            if(!isset($arr_en[$key])) {
                $err[$redundant][$key] = array();
                $err[$redundant][$key]['line']=$val['line'];
                if(!isset($val['value'])){
                    $val['value'] = $key;
                }
                $err[$redundant][$key]['value']=$val['value'];
            }
        }

        foreach ($arr as $key=>$val){
            if(isset($val['duplicate'])){
                $err['duplicate'][$key]['line'] = $val['duplicate']['line'];
                $err['duplicate'][$key]['value'] = $val['duplicate']['value'];
            }



        }
        return $err;

    }

    /**
     *Getting informaton from csv files and calling checking and display fuunctions
     *
     * @param   string $file_en - default english file
     * @param   string $file - comparing file
     * @return  none
     */
    static public function checkFiles($file_en,$file)
    {
        try {
            $data_en = self::$csv -> getDataPairs($file_en);
            $data = self::$csv -> getDataPairs($file);
        } catch (Exception $e) {
           self::_error($e->getMessage());
        }
        self::_output(basename($file),self::checkArray($data_en,$data));
    }
    /**
     *Getting informaton from csv files for update
     *
     * @param   string $file_en - default english file
     * @param   string $file - comparing file
     * @return  none
     */
    static protected function _checkFilesUpdate($file_en,$file)
    {
        try {
            $data_en = self::$csv -> getDataPairs($file_en);
            $data = self::$csv -> getDataPairs($file);
        } catch (Exception $e) {
           self::_error($e->getMessage());
        }
        $diff_arr = self::checkArray($data_en,$data);
        $path_inf = pathinfo($file);

        self::_output(basename($file),$diff_arr,$path_inf['dirname']."/".basename($file,".".EXTENSION));
        $pre_data = array();
        $csv_data = array();
        foreach ($data_en as $key=>$val){
            $pre_data[$key]=$val['value'];
        }
        uksort($pre_data, 'strcasecmp');
        foreach ($pre_data as $key => $val){
            if(isset($data[$key]['value'])){
                array_push($csv_data,array($key,$data[$key]['value']));
            } else {
                array_push($csv_data,array($key,$val))	;
            }
        }
        $path_inf = pathinfo($file);
        self::$csv -> saveData($file,$csv_data);
    }
    /**
     *Display compared information for pair of files
     *
     * @param   string $file_name - compared file name
     * @param   array $arr - array of lack of coincidences
     * @return  none
     */
    static protected function _output($file_name,$arr,$out_file_name=null)
    {

        $out ='';
        $out.=$file_name.":\n";
        $tmp_arr = $arr['missing'];
        $arr['missing']=array();
        foreach ($tmp_arr as $key=>$val){
            $arr['missing'][$key] = array();
            $arr['missing'][$key]['value'] = $val['value'];
            $arr['missing'][$key]['line'] = $val['line'];
            $arr['missing'][$key]['state'] = 'missing';
        }
        $tmp_arr = $arr['redundant'];
        $arr['redundant']=array();
        foreach ($tmp_arr as $key=>$val){
            $arr['redundant'][$key] = array();
            $arr['redundant'][$key]['value'] = $val['value'];
            $arr['redundant'][$key]['line'] = $val['line'];
            $arr['redundant'][$key]['state'] = 'redundant';
        }
        $count_miss = count($arr['missing']);
        $count_redu = count($arr['redundant']);
        $count_dupl = count($arr['duplicate']);

        if($count_redu>0 || $count_miss>0){
            $comb_arr = array_merge($arr['missing'],$arr['redundant']);
            uksort($comb_arr, 'strcasecmp');
            foreach ($comb_arr as $key=>$val)
            switch ($val['state']){
                case 'missing':
                    $out.="\t".'"'.$key.'" => missing'."\n";
                    break;
                case 'redundant':
                    $out.="\t".'"'.$key.'" => redundant ('.$val['line'].")\n";
                    break;
            }

        }

        if($count_dupl>0){
            uksort($arr['duplicate'], 'strcasecmp');
            foreach ($arr['duplicate'] as $key=>$val){
                $out.= "\t".'"'.$key.'" => duplicate ('.$val['line'].")\n";
            }
        }
        if($count_miss>0 || $count_redu>0 || $count_dupl>0){
            if(!$out_file_name){
                echo $out;
            } else {
                $csv_data = array();
                if(isset($comb_arr)){
                    foreach ($comb_arr as $key=>$val){
                        if(!isset($val['value']))$val['value']=$key;
                        switch ($val['state']){
                            case 'missing':
                                array_push($csv_data,array($key,$val['value'],'missing'));
                            break;
                            case 'redundant':
                                array_push($csv_data,array($key,$val['value'],'redundant', $val['line']));
                            break;
                        }
                    }
                }
                foreach ($arr['duplicate'] as $key=>$val){
                    if(!isset($val['value']))$val['value']=$key;
                    array_push($csv_data,array($key,$val['value'],'duplicate', $val['line']));
                }
                self::$csv -> saveData($out_file_name.'.changes.'.EXTENSION,$csv_data);
                }
            }
    }

}
?>
