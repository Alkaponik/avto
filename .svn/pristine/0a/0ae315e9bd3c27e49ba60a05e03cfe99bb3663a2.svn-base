<?php

class Testimonial_System_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function processTemplate($expression, $dataObject, $callbackObject = null)
    {
        preg_match_all('/\{\{(.*?)\}\}/', $expression, $expressions);
        if (!empty($expressions)){
            for ($index = 0, $num = count($expressions[1]); $index < $num; $index++){
                $expression = str_replace("{{" . $expressions[1][$index] . "}}", $this->executeExpression($dataObject, $expressions[1][$index], $callbackObject), $expression);
            }
        }
        return $expression;
    }

    public function executeExpression($dataObject, $node, $callbackObject = null)
    {
        /* Parsing internal XPATH format which defines the data */
        $actions = explode('/',$node);
        $data = $dataObject;
        if (is_array($data)){
            $data = new Varien_Object($data);
        }
        foreach ($actions as $action) {
            switch(true) {
                /* dataObject attribute handling */
                case substr($action,0,1)=='@':
//                            print_r(get_class($data));
                    $data = ($data instanceof Varien_Object) ? $data->getData(substr($action,1)) : null;
//                            print_r("@$key=".substr($action,1)."<br/>");
                    break;
                /* Current dataObject, doing nothing */
                case $action=='.':
                    break;
                /* Parent handling */
                case $action=='..':
                    /**
                     * @todo: exclude the parent gathering into the
                     * separate getParent($dataObject) function
                     */
                    switch(true) {
                        case $dataObject instanceof Mage_Sales_Model_Order_Address:
                        case $dataObject instanceof Mage_Sales_Model_Order_Item:
                            $data=$data->getOrder();
                            break;
                    }
                    break;
                /* Function call */
                case substr($action,-1)==')':
                    $function = substr($action,0,strpos($action,'('));
                    $params = array();
                    /**
                     *  @todo implement complex argument reference handling
                     */
                    $args = substr($action,strpos($action,'(')+1,-1);
                    if ($args) {
                        $args = explode(',',$args);
                        foreach($args as $arg) {
                            $arg = trim($arg);
                            if (substr($arg,0,1)=='@') {
                                if (strlen($arg)>1 && $data instanceof Varien_Object) {
                                    $params[]=$data->getData(substr($arg,1));
                                }else {
                                    $params[] = &$data;
                                }
                            }else {
                                $params[]=trim($arg, '\'"');
                            }
                        }
                    }
                    if (strpos($function, ':') === false){
                        /** 
                         * Expression 
                         * callable(args)
                         * calls current data object method for instance
                         * getName() calls
                         * $data->getName()
                         */
                        $callable = array(&$data,$function);
                    }else if (strpos($function, '::') === false){
                        /** 
                         * Expression 
                         * :callable(args)
                         * calls native PHP or user function like mb_strtolower
                         */
                        $callable = $callable = explode(':',$function);
                    }else{
                        /** 
                         * Expression 
                         * ::callable(args)
                         * calls callbackObject object method for instance
                         * ::getFieldExpession() calls
                         * $callbackObject->getFieldExpession()
                         */
                        $callable = explode('::',$function);
                        if (!is_null($callbackObject)){
                            $callable[0] = $callbackObject;
                        } else {
                            $callable[0] = &$this;
                        }
                    }

//                            print_r(get_class($callable[0])."::{$callable[1]}():".'<br/>');
                    if (!$callable[0]){
                        $callable = $callable[1];
                    }
                    if (is_callable($callable)){
                        $data = call_user_func_array($callable, $params);
                    }else{
                        if (is_array($callable)){
                            $callable = get_class($callable[0]). '::' . $callable[1];
                        }
                        self::log('Unable to call '. $callable);
                        $data = null;
                    }

                    if(is_object($data)) {
//                                print_r(get_class($data).'<br/>');
                    }else {
//                                print_r($data.'<br/>');
                    }
                    break;
                /* Constant value handling */
                case strlen($action):
                default:
                    if (!is_string($data)) {
                        $data = $action;
                    }
                    break;
            }
        }
        return $data;
    }

    protected static function log($message)
    {
        Mage::log($message, null, 'testimonial_system.log');
    }
}
