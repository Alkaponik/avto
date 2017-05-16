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

require_once 'Zend/Console/Getopt/Exception.php';
require_once 'Zend/Console/Getopt.php';

class MultyGetopt extends Zend_Console_Getopt {

    protected function _parseSingleOption($flag, &$argv)
    {
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flag = strtolower($flag);
            }
            if (!isset($this->_ruleMap[$flag])) {
                throw new Zend_Console_Getopt_Exception(
                    "Option \"$flag\" is not recognized.",
                    $this->getUsageMessage());
            }
            $realFlag = $this->_ruleMap[$flag];
            switch ($this->_rules[$realFlag]['param']) {
                case 'required':
                    if (count($argv) > 0) {
                        $param = array_shift($argv);
                        $this->_checkParameterType($realFlag, $param);
                    } else {
                        throw new Zend_Console_Getopt_Exception(
                            "Option \"$flag\" requires a parameter.",
                            $this->getUsageMessage());
                    }
                    break;
                case 'optional':
                    if (count($argv) > 0 && substr($argv[0], 0, 1) != '-') {
                        $param = array_shift($argv);
                        $this->_checkParameterType($realFlag, $param);
                    } else {
                        $param = true;
                    }
                    break;
                default:
                    $param = true;
            }

            if(isset($this->_options[$realFlag])){
                if(!is_array($this->_options[$realFlag])) {
                    $tmp = $this->_options[$realFlag];
                    $this->_options[$realFlag]=array();
                    array_push($this->_options[$realFlag],$tmp);
                }
                array_push($this->_options[$realFlag],$param);
            } else {
                $this->_options[$realFlag] = $param;
            }


    }



}

?>
