<?php
/**
 * 
 * @author claudiu 
 * May 22, 2018
 * 
 * @since 21.09.2018
 * i've excluded some session vars: PatientCourse_Fetched_Contactforms, Navigation_Menus
 * 
 * @since 03.10.2018 from Login_Info exclude _clientModules
 *
 */
class Application_Controller_Helper_Log extends Zend_Controller_Action_Helper_Abstract
{
    
    protected $logininfo = NULL;

    private $_environment = NULL;
    
    private $_message = NULL;
    
    private $_errorLevel = NULL;
    
    public function __construct() 
    {
        $this->logininfo = new Zend_Session_Namespace('Login_Info');
    }
    
    /**
     * Strategy pattern: allow calling helper as broker method
     * 
     * @param string $message
     * @param number $errorLevel
     */
    public function direct($message = '', $errorLevel = Zend_Log::INFO)
    {
        return $this->log($message, $errorLevel); 
    }
    
    
    /**
     * 
     * @param string $message
     * @param number $errorLevel
     */
    public function log($message = '', $errorLevel = Zend_Log::INFO)
    {
        $this->_message = $message;
        
        $this->_errorLevel = $errorLevel;
        
        if (($logger = $this->getFrontController()->getParam('bootstrap')->getResource('Log')) 
            && $logger instanceof Zend_Log) 
        {
            
            /*
             * errors in 1,2,3 and error modulo 2, will send a local backtrace 
             */
            
            
            if (($this->_errorLevel < 4 || $this->_errorLevel % 2 == 1) 
                && $this->getRequest()->getControllerName() != 'error') 
            {
                $this->_message = $this->_niceVarDump($this->_message) . $this->_backtrace();
            }
            
            $this->_setEventItems($logger);
            
            $logger->log($this->_message, $this->_errorLevel);
    
            
            if ($this->_isDevelopment()) {
                try {
                    if ($_zf_debug = $this->getFrontController()->getPlugin('ZFDebug_Controller_Plugin_Debug')) {
                        if (($_loger = $_zf_debug->getPlugin('log'))) {
                            
                            $message = $this->_niceVarDump( $this->_message);
                            
                            $_loger->mark('<font color=fuchsia>' . $message . '</font>', true);
                            
                        }
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
        
        return;
    }
    
    /**
     * $errorLevel param is not used
     * i have added it as param so you can learn them 
     */
    
    public function error($message, $errorLevel = 3)
    {
        return $this->log($message, Zend_Log::ERR);
    }
    
    public function info($message, $errorLevel = 6)
    {
        return $this->log($message, Zend_Log::INFO);
    }
    
    public function debug($message = '', $errorLevel = 7)
    {
        if ($this->_isDevelopment()) {
            return $this->log($message, Zend_Log::DEBUG);
        }
    }
    
    
    /**
     * new error levels are defined/appended in bootstrap
     */
        
    /*
     * 10, 11 for cronjob
     */
    public function croninfo($message = '', $errorLevel = 10)
    {
        if ($this->_isDevelopment()) {
            echo $this->_niceVarDump($message);
        }
        
        return $this->log($message, 10);
    }
    
    public function cronerror($message = '', $errorLevel = 11)
    {
        if ($this->_isDevelopment()) {
            echo $this->_niceVarDump($message);
        }
        
        return $this->log($message, 11);
    }
    
    /*
     * 12, 13 for rights permission
     */
    public function rightsinfo($message = '', $errorLevel = 12)
    {
        return $this->log($message, 12);
    }
    
    public function rightserror($message = '', $errorLevel = 13)
    {
        return $this->log($message, 13);
    }
    
    /*
     * 14,15 for ftp upload
     */
    public function ftpinfo($message = '', $errorLevel = 14)
    {
        return $this->log($message, 14);
    }
    
    public function ftperror($message = '', $errorLevel = 15)
    {
        return $this->log($message, 15);
    }

    /*
     * 16,17 are for DGP
     */
    public function dgpinfo($message = '', $errorLevel = 16)
    {
        return $this->log($message, 16);
    }
    
    public function dgperror($message = '', $errorLevel = 17)
    {
        return $this->log($message, 17);
    }
    
    
    
    
    
    private function _isDevelopment()
    {
        if (is_null($this->_environment)) {
            $this->_environment = $this->getFrontController()->getParam('bootstrap')->getEnvironment();   
        }
    
        return ($this->_environment == 'development') || ($this->_environment == 'staging') ? true : false;
    }
    


    private function _setEventItems(Zend_Log $logger)
    {
        $logger->setEventItem('message', $this->_message);
        
        //$logger->setEventItem('exception_message', '');
        //$logger->setEventItem('exception_trace', '');
    
        //set default tokens
        $logger->setEventItem('errorLevel', $this->_errorLevel);
        $logger->setEventItem('REQUEST_URI', $this->getRequest()->getRequestUri());
    
        $logger->setEventItem('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
        $logger->setEventItem('HTTP_X_FORWARDED_FOR', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $logger->setEventItem('REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);
        $logger->setEventItem('date', date("d.m.Y"));
        $logger->setEventItem('datetime', date("d.m.Y H:i:s"));
        $logger->setEventItem('username', $this->logininfo->username);
        $logger->setEventItem('controllerName', $this->getRequest()->getControllerName());
        $logger->setEventItem('actionName', $this->getRequest()->getActionName());
        $logger->setEventItem('APPLICATION_ENV', APPLICATION_ENV);
        
        
        $server = '';
        foreach ($_SERVER as $var => $value) {
            $server .= '$_SERVER[' . $var . '] : ' . $value . PHP_EOL;
        }
        $logger->setEventItem('PRINT_R_SERVER', $server);
    
    
        $session = '';
        foreach ($_SESSION as $var => $value) {
            
            if (in_array($var, ['PatientCourse_Fetched_Contactforms', 'Navigation_Menus'])){
                
                $session .= '$_SESSION[' . $var . '] : was removed from log' . PHP_EOL;
                
                continue;
            }
            
            if (is_object($value)) {
                $value = json_encode(get_object_vars($value));
            } elseif (is_array($value)) {
                if (isset($value['filepass'])) $value['filepass'] = " -- removed from debug --";
                if (isset($value['_clientModules'])) $value['_clientModules'] = " -- removed from debug --";
                $value = print_r($value, true);
            }
            $session .= '$_SESSION[' . $var . '] : ' . $value . PHP_EOL;
        }
        $logger->setEventItem('PRINT_R_SESSION', $session);
    
        return $logger;
    }
    
    
    
    
    private function _niceVarDump($obj, $ident = 0)
    {
        $data = '';
        $data .= str_repeat('&nbsp;', $ident);
        $original_ident = $ident;
        $toClose = false;
        switch (gettype($obj)) {
            case 'object':
                $vars = (array) $obj;
                $data .= gettype($obj).' ('.get_class($obj).') ('.count($vars).") {\n<br/>\n";
                $ident += 2;
                foreach ($vars as $key => $var) {
                    $type = '';
                    $k = bin2hex($key);
                    if (strpos($k, '002a00') === 0) {
                        $k = str_replace('002a00', '', $k);
                        $type = ':protected';
                    } elseif (strpos($k, bin2hex("\x00".get_class($obj)."\x00")) === 0) {
                        $k = str_replace(bin2hex("\x00".get_class($obj)."\x00"), '', $k);
                        $type = ':private';
                    }
                    $k = hex2bin($k);
                    if (is_subclass_of($obj, 'ProtobufMessage') && $k == 'values') {
                        $r = new ReflectionClass($obj);
                        $constants = $r->getConstants();
                        $newVar = [];
                        foreach ($constants as $ckey => $cval) {
                            if (substr($ckey, 0, 3) != 'PB_') {
                                $newVar[$ckey] = $var[$cval];
                            }
                        }
                        $var = $newVar;
                    }
                    $data .= str_repeat('&nbsp;', $ident)."[$k$type]=>\n<br/>\n".self::_niceVarDump($var, $ident)."\n<br/>\n";
                }
                $toClose = true;
                break;
            case 'array':
                $data .= 'array ('.count($obj).") {\n<br/>\n";
                $ident += 2;
                foreach ($obj as $key => $val) {
                    $data .= str_repeat('&nbsp;', $ident).'['.(is_int($key) ? $key : "\"$key\"")."]=>\n".self::_niceVarDump($val, $ident)."\n<br/>\n";
                }
//                 $toClose = true;

//                 $data .= "<pre>" . print_r($obj, true) . "</pre>";                
                $toClose = false;
                break;
            case 'string':
                //$data .= 'string "'.$this->_parseText($obj)."\"\n";
                $data .= 'string "'. $obj."\"\n<br/>\n";
                break;
            case 'NULL':
                $data .= gettype($obj);
                break;
            default:
                $data .= gettype($obj).'('.strval($obj).")\n<br/>\n";
                break;
        }
        if ($toClose) {
            $data .= str_repeat('&nbsp;', $original_ident)."}\n<br/>\n";
        }
        return $data;
    }
    
    
    private function _parseText($txt)
    {
        for ($x = 0; $x < strlen($txt); $x++) {
            if (ord($txt[$x]) < 20 || ord($txt[$x]) > 230) {
                $txt = 'HEX:'.bin2hex($txt);
                return $txt;
            }
        }
        return $txt;
    }
    
    
    
    private function _backtrace()
    {
        $stackLimit = 30;
        $tabs = "»";
        
        $text_output = "\ndebug_backtrace(" . DEBUG_BACKTRACE_IGNORE_ARGS.", stackLimit: $stackLimit)\n";
        $call = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $stackLimit);

        for($i=count($call); $i>=0; $i--){
            if (isset($call[$i])) {
                $text_output .= "#{$i}" . $tabs . $call[$i]['class'] . "::" . $call[$i]['function'] . " (line ".$call[$i-1]['line'] . ")\n";
            }
            $tabs = " " . $tabs;
        }
        return $text_output;
    }
    
    
}