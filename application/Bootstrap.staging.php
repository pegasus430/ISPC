<?php

/* Attention ! */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Bootstrap.php");
/**
 * @claudiu Mar 19, 2018
* re-arrenged the Bootstrap class functions
*
* please use
*
* Bootstrap.php => [production] | https://www.ispc-login.de/
*
* Bootstrap.staging.php => [staging] | http://dev.smart-q.de
* Bootstrap.development.php => [development] | localhost
* Bootstrap.testing.php => [testing] | localhost
*/
class BootstrapStaging extends Bootstrap
{
    
    /**
     * displayExceptions and ZFDebug bar only for this ip's
     * 
     * @var array
     */
    private $debug_ips = array(
        '10.0.0.36', //claudiu @cla
        '10.0.0.15', //ancuta
        '79.118.159.147',//'79.116.11.234', //carmen
        '10.0.0.5', //laptop win
    );
    
    private $debug_cookie_developer = array(
        '@cla', //claudiu
        'ancuta', //ancuta
        'carmen',//carmen
    );

    /**
     * overwrites the parent, so I can have my custom session_name
     * (non-PHPdoc)
     * @see Bootstrap::_initSession()
     */
    protected function _initSession()
    {
        Zend_Session::start(['name' => 'ISPC_STAGING' , 'remember_me_seconds' => 3600]);
    }
    
 
    
    /**
     * _init just for this Environment
     * ($this->getEnvironment() == 'development')
     */
    protected function _initStageing()
    {
        
        date_default_timezone_set('Europe/Berlin');
        setlocale(LC_ALL, 'de_DE.utf8', 'de_DE.UTF-8', 'de_DE', 'de_DE@euro', 'deu_deu');
        
        $this->bootstrap('frontController');
        
        $frontController = $this->getResource('frontController');
        
        $frontController->setRequest(new Zend_Controller_Request_Http());
        
        $remoteAddr = $frontController->getRequest()->getServer('REMOTE_ADDR'); //HTTP_X_FORWARDED_FOR if you're behind a proxy
        
        if ( ! in_array($remoteAddr, $this->debug_ips) 
            && ( ! isset($_COOKIE['developer']) || ! in_array($_COOKIE['developer'], $this->debug_cookie_developer)) ) 
        {
            
            $frontController->setParam('displayExceptions', 0);
            
            return;
        }
        
        
        if ( isset ($_COOKIE['development_notranslation']) && $_COOKIE['development_notranslation'] == '1') {
            $this->setOptions(['resources' => ['translate'=> ['locale' => 'en', 'content'=> APPLICATION_PATH."/language/en", 'adapter' => 'Array']]]);
        }
        
        
        
        /*
         * init this first
         */
        $this->bootstrap('AppInfo');
        $this->bootstrap('Pms');
        $this->bootstrap('Session');
        $this->bootstrap('Acl');
        $this->bootstrap('Locale');
        $this->bootstrap('Translator');
        $this->bootstrap('ViewResource');
        
        
        $this->bootstrap('View');
        $this->bootstrap('Doctrine');
        $this->bootstrap('Log');

        
        if ((empty($_COOKIE['mobile_ver']) || $_COOKIE['mobile_ver'] != 'yes') && $view = $this->getResource('view')) {
        
            $view->headScript()->appendFile(RES_FILE_PATH . '/javascript/jquery.cookie.js',  $type = 'text/javascript', $attrs = array('defer' => true));
            $view->headScript()->appendFile(RES_FILE_PATH . '/javascript/layouts/development.js', $type = 'text/javascript', $attrs = array('defer' => true));
        
        }
        
        
        /*
         * ZFDebug
         */
        if ($this->getResource('Doctrine')) {
            Zend_Loader_Autoloader::getInstance()->registerNamespace('ZFDebug')->pushAutoloader(array(
                'ZFDebug',
                '__autoload'
            ));
            $options = array(
                'plugins' => array(
                    
                    'Variables',
                    'File',
                    'Log',
                    'Time',
                    new ZFDebug_Controller_Plugin_Debug_Plugin_Doctrine(),
                    new ZFDebug_Controller_Plugin_Debug_Plugin_Exception(),
                    new ZFDebug_Controller_Plugin_Debug_Plugin_Auth(),
                    'Memory'
                )
            );
            // new ZFDebug_Controller_Plugin_Debug_Plugin_Memory(),
            
            $ZFDebug = new ZFDebug_Controller_Plugin_Debug($options);
            $frontController = Zend_Controller_Front::getInstance();
            $frontController->registerPlugin($ZFDebug);
            
            $ZFDebug->getPlugin('log')->mark('<font color=fuchsia><pre>' . print_r(get_defined_constants(true)['user'], true)  . '</pre></font>', true);
            
        }
 
    }
    
    /**
     * @ancuta on 17.07.2019
     * ISPC-2346 datamatrix scanner for reading medi plan
     */
    protected function _initBarcodereader()
    {
        Zend_Registry::set('barcodereader', $this->getOption('barcodereader'));
    }
    
}