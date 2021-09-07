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

/**
    interface Zend_Application_Bootstrap_Bootstrapper
    {
        public function __construct($application);
        public function setOptions(array $options);
        public function getApplication();
        public function getEnvironment();
        public function getClassResources();
        public function getClassResourceNames();
        public function bootstrap($resource = null);
        public function run();
    }
 */
class BootstrapDevelopment extends Bootstrap
{
    
//     protected function _initDompdf()
//     {
//         Zend_Loader_Autoloader::getInstance()->registerNamespace('Dompdf')->pushAutoloader(array(
//             'Dompdf', 'what is the entry?'
//         ));
//     }

    /**
     * overwrite, so I can have my custom session_name
     * (non-PHPdoc)
     * @see Bootstrap::_initSession()
     */
    protected function _initSession()
    {
        //Zend_Session::start();
        Zend_Session::start(['name' => 'ISPC_DEV_CLA' , 'remember_me_seconds' => 3600]);
    }
    
    /*
     * TODO: TEST cache gain/loss !
     * after test(if OK) move to parent
     */
    protected function _initDoctrine()
    {
        parent::_initDoctrine();
        
        $manager = Doctrine_Manager::getInstance();
        
        $cacheDriver =  new Doctrine_Cache_Array();
        
        $manager->setAttribute(Doctrine_Core::ATTR_CACHE,  $cacheDriver); // this is the same
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE,  $cacheDriver);
        
        //$manager->setAttribute(Doctrine_Core::ATTR_CACHE_LIFESPAN,  3600); // this is the same
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN,  3600);
        $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE_LIFESPAN,  3600);
        
        /* CANNOT auto-free cause you cannot re-run queries like q->count() */
//         $manager->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS,  true); 

        
        
        return $manager;
        
    }    
    
    /**
     * _init just for this Environment
     * ($this->getEnvironment() == 'development')
     */
    protected function _initDevelopment()
    {
        
        if ( isset ($_COOKIE['development_notranslation']) && $_COOKIE['development_notranslation'] == '1') {
            $this->setOptions(['resources' => ['translate'=> ['locale' => 'en', 'content'=> APPLICATION_PATH."/language/en", 'adapter' => 'Array']]]);
        }
        
        /**
         * init this first
         */
        $this->bootstrap('AppInfo');
        $this->bootstrap('Pms');
        $this->bootstrap('Session');
        $this->bootstrap('Acl');
        $this->bootstrap('Locale');
        $this->bootstrap('Translator');
        $this->bootstrap('LayoutResource');
        $this->bootstrap('ViewResource');
        $this->bootstrap('Doctrine');
        $this->bootstrap('Log');
        

        //@cla pc
        date_default_timezone_set('Europe/Berlin');
        setlocale(LC_ALL, 'de_DE.utf8', 'de_DE.UTF-8', 'de_DE', 'de_DE@euro', 'deu_deu'); //i only have on my machine de_DE
        
        
        
        
	    /**
	     * stacktrace.js
	     * Generate, parse, and enhance JavaScript stack traces
	     * https://github.com/stacktracejs
	     */
//         if ((empty($_COOKIE['mobile_ver']) || $_COOKIE['mobile_ver'] != 'yes') && $view = $this->getResource('view')) {
        if ($view = $this->getResource('view')) {
		    //text/javascript
		    //content-type: application/javascript;charset=utf-8
		    
$debug_script = <<<EOT
var DEBUG = true;
//@cla debug stacktrace
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true && typeof(StackTrace) === 'object')
{
    window.onerror = function(msg, file, line, col, error) {
        // callback is called with an Array[StackFrame]
        StackTrace.fromError(error).then(callback).catch(errback);
    };

    var callback = function(stackframes) {
        var _tabs = "";
        var stringifiedStack = stackframes.map(function(sf) {
            _tabs += "  ";
            return _tabs + sf.toString();
        }).join('\\n');
        console.log(stringifiedStack);
    };

    var errback = function(err) {console.log("HORROR"); console.log(err.message); };
}
EOT;
		    //https://cdnjs.cloudflare.com/ajax/libs/stacktrace.js/2.0.0/stacktrace.min.js
		   // $view->headScript()->appendFile('http://10.0.0.36/ispc2017_08/public/javascript/stacktrace.js-2.0.0/dist/stacktrace.min.js');
		    //$view->headScript()->appendFile('http://10.0.0.36/ispc2017_08/public/javascript/stacktrace.js-2.0.0/error-stack-parser-master/dist/error-stack-parser.min.js');
		    //$view->headScript()->appendFile('http://10.0.0.36/ispc2017_08/public/javascript/stacktrace.js-2.0.0/stack-generator-master/dist/stack-generator.min.js');
		    //$view->headScript()->appendFile('http://10.0.0.36/ispc2017_08/public/javascript/stacktrace.js-2.0.0/stacktrace-gps-master/dist/stacktrace-gps.min.js');
		    
		    //$view->headScript()->appendFile('http://10.0.0.36/ispc2017_08/public/javascript/jquery.cookie.js',  $type = 'text/javascript', $attrs = array('defer' => true));
            //$view->headScript()->appendScript($debug_script, $type = 'text/javascript', $attrs = array());
            
            //$view->headScript()->appendFile('http://10.0.0.36/ispc2017_08/public/javascript/layouts/development.js', $type = 'text/javascript', $attrs = array('defer' => true));

        }
       
        
       
        /**
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
    
}