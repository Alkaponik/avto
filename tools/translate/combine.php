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

/*

Usage:
 php -f combine.php -- --output <file> --locale <locale_NAME>

*/

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(dirname(dirname(__FILE__))));

define('MESSAGE_TYPE_NOTICE', '0');
define('MESSAGE_TYPE_WARNING', '1');
define('MESSAGE_TYPE_ERROR', '2');

define('LOCALE_PATH', BP . DS . 'app' . DS . 'locale' . DS . '%s' . DS);

include(BP . DS . 'lib' . DS . 'Varien' . DS . 'File' . DS . 'Csv.php');

class Combine
{
    /**
     * File name patterns needed to be processed
     *
     * @var array
     */
    private $_namePatterns = array('#^(Mage_\w+)\.csv$#', '#^(translate).csv$#');

    /**
     * Pattern of the locale path
     *
     * @var string
     */
    private $_localePath = LOCALE_PATH;

    /**
     * Result output file name
     *
     * @var string
     */
    private $_outputFileName = null;

    /**
     * Locale name
     *
     * @var string
     */
    private $_localeName = null;

    /**
     * Messages array
     *
     * @var array
     */
    private $_messages = array();

    /**
     * Variable that indicates errors occured
     *
     * @var bool
     */
    private $_error = false;

    /**
     * Combine init
     *
     * @param array $argv
     */
    public function __construct($argv)
    {
        $outputFileName = null;
        $localeName = null;

        foreach ($argv as $k=>$arg) {
            switch($arg) {
                case '--output':
                    $outputFileName = @$argv[$k+1];
                    break;

                case '--locale':
                    $localeName = @$argv[$k+1];
                    break;
            }
        }

        if (!$outputFileName || !$localeName) {
            $this->_addMessage(MESSAGE_TYPE_ERROR, "Use this script as follows:\n\tcombine.php --output <file> --locale <locale_NAME>");
            $this->_error = true;
            return;
        }

        if (file_exists($outputFileName) && !is_writable($outputFileName)){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("File '%s' exists and isn't writeable", $outputFileName));
            $this->_error = true;
            return;
        }

        if (!is_dir(sprintf($this->_localePath, $localeName))){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("Locale '%s' was not found", $localeName));
            $this->_error = true;
            return;
        }

        if (!is_readable(sprintf($this->_localePath, $localeName))){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("Locale '%s' is not readable", $localeName));
            $this->_error = true;
            return;
        }


        $this->_outputFileName = $outputFileName;
        $this->_localeName = $localeName;
    }


    /**
     * Browses the given directory and returns full file names
     * which matches internal name patterns
     *
     * @return array
     * @param string $path
     */
    private function _getFilesToProcess($path)
    {
        $result = array();
        $prefix = (substr($path, -1) == DS ? $path : $path . DS);

        $directory = dir($path);
        while (false !== ($file = $directory->read())) {
            foreach ($this->_namePatterns as $pattern){
                if (preg_match($pattern, $file, $matches)){
                    $alias = $matches[1];
                    $result[$alias] = $prefix . $file;
                }
            }
        }

        return $result;
    }

    /**
     * Combine process
     *
     */
    public function run()
    {
        if ($this->_error) {
            return false;
        }
        $resultData = array();

        $files = $this->_getFilesToProcess(sprintf($this->_localePath, $this->_localeName));
        $csv = new Varien_File_Csv();

        foreach ($files as $alias=>$file){
            $data = $csv->getData($file);
            for ($i = 0; $i < count($data); $i++){
                $data[$i] = array_merge(array($alias), $data[$i]);
            }
            $resultData = array_merge($resultData, $data);
        }
        $csv->saveData($this->_outputFileName, $resultData);

        $this->_addMessage(MESSAGE_TYPE_NOTICE, 'Translation combined successfully');
    }

    /**
     * Parses internal messages and returns them as a string
     *
     * @return string
     */
    public function renderMessages()
    {
        $result = array();

        foreach ($this->_messages as $message){
            $type = $message['type'];
            $text = $message['text'];

            switch($type){
                case MESSAGE_TYPE_ERROR:
                    $type = 'Error';
                    break;

                case MESSAGE_TYPE_WARNING:
                    $type = 'Warning';
                    break;

                case MESSAGE_TYPE_NOTICE:
                default:
                    $type = 'Notice';
                    break;
            }

            $result[] = sprintf('%s: %s', $type, $text);
        }

        return implode('\n', $result);
    }

    private function _addMessage($type, $message)
    {
        $this->_messages[] = array('type'=>$type, 'text'=>$message);
    }
}

$combine = new Combine($argv);
$combine->run();
echo $combine->renderMessages();
echo "\n\n";
