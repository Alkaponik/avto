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
 php -f split.php -- --input <file> --locale <locale_NAME>

*/


define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(dirname(dirname(__FILE__))));

define('MESSAGE_TYPE_NOTICE', '0');
define('MESSAGE_TYPE_WARNING', '1');
define('MESSAGE_TYPE_ERROR', '2');

define('LOCALE_PATH', BP . DS . 'app' . DS . 'locale' . DS . '%s' . DS);

include(BP . DS . 'lib' . DS . 'Varien' . DS . 'File' . DS . 'Csv.php');

class Split
{
    /**
     * Pattern of the locale path
     *
     * @var string
     */
    private $_localePath = LOCALE_PATH;

    /**
     * Result input file name
     *
     * @var string
     */
    private $_inputFileName = null;

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
     * Split init
     *
     * @param array $argv
     */
    public function __construct($argv)
    {
        $inputFileName = null;
        $localeName = null;

        foreach ($argv as $k=>$arg) {
            switch($arg) {
                case '--input':
                    $inputFileName = @$argv[$k+1];
                    break;

                case '--locale':
                    $localeName = @$argv[$k+1];
                    break;
            }
        }

        if (!$inputFileName || !$localeName) {
            $this->_addMessage(MESSAGE_TYPE_ERROR, "Use this script as follows:\n\tcombine.php --output <file> --locale <locale_NAME>");
            $this->_error = true;
            return;
        }

        if (!file_exists($inputFileName)){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("File '%s' doesn't exists", $inputFileName));
            $this->_error = true;
            return;
        }

        if (!is_readable($inputFileName)){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("File '%s' isn't readable", $inputFileName));
            $this->_error = true;
            return;
        }

        if (!is_dir(sprintf($this->_localePath, $localeName))){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("Locale '%s' was not found", $localeName));
            $this->_error = true;
            return;
        }

        if (!is_writable(sprintf($this->_localePath, $localeName))){
            $this->_addMessage(MESSAGE_TYPE_ERROR, sprintf("Locale '%s' is not writeable", $localeName));
            $this->_error = true;
            return;
        }


        $this->_inputFileName = $inputFileName;
        $this->_localeName = $localeName;
    }


    /**
     * Split process
     *
     */
    public function run()
    {
        if ($this->_error) {
            return false;
        }

        $csv = new Varien_File_Csv();
        $inputData = $csv->getData($this->_inputFileName);
        $output = array();
        $files = array();

        foreach ($inputData as $row){
            $output[$row[0]][] = array_slice($row, 1);
        }

        foreach ($output as $file=>$data){
            $outputFileName = sprintf($this->_localePath, $this->_localeName) . "{$file}.csv";
            $csv->saveData($outputFileName, $data);
        }

        $this->_addMessage(MESSAGE_TYPE_NOTICE, 'Translation splitted successfully');
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

$split = new Split($argv);
$split->run();
echo $split->renderMessages();
echo "\n\n";
