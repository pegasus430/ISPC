<?php
/**
 * @claudiu Mar 19, 2018
 * re-arrenged the Bootstrap class functions
 *
 * please use
 *
 *
 * Bootstrap.php => [production] | https://www.ispc-login.de/
 *
 *
 *
 * Bootstrap.staging.php => [staging] | http://dev.smart-q.de
 * Bootstrap.development.php => [development] | localhost
 * Bootstrap.testing.php => [testing] | localhost
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initSession()
    {
        Zend_Session::start();
    }
    
    /**
     * @cla
     * for when you move the app on another server,
     * you have a config that will logout all users and show then a info page
     */
    protected function _initServerMove()
    {
        Zend_Registry::set('serverMove', $this->getOption('serverMove'));
    }
    
    /*
     * autoload module
     protected function _initAutoload()
     {
     $autoloader = new Zend_Application_Module_Autoloader(array(
     'namespace' => 'Application',
     'basePath'  => APPLICATION_PATH,
     ));
     return $autoloader;
     }
     */
    
    protected function _initPms()
    {
        Zend_Loader_Autoloader::getInstance()->registerNamespace('Pms')->pushAutoloader(array(
            'Pms',
            '__autoload'
        ));
    }
    
    
    protected function _initSecurity()
    {
        Zend_Registry::set('salt', $this->_options['salt']);
        Zend_Registry::set('idSalt', $this->_options['idsalt']);
        Zend_Registry::set('hidemagic', $this->_options['hidemagic']);
    }
    
    protected function _initAcl()
    {
        $frontController = Zend_Controller_Front::getInstance();
        
        $frontController->registerPlugin(new Pms_Plugin_Acl(new Pms_Acl_Assertion()));
        
        $frontController->registerPlugin(new Pms_Plugin_LayoutSetup()); //@cla testing
        
        
        //         foreach ($frontController->getPlugins() as $stackIndex => $plugin) {
        //             Zend_Debug::dump(get_class($plugin), $stackIndex);
        //         }
    }
    
    
    protected function _initMailConfigs()
    {
        
        define('ISPC_SMTP_SERVER', $this->getOption('mailserver'));
        
        define('ISPC_SENDER', $this->getOption('mailfrom'));
        define('ISPC_SENDERNAME', $this->getOption('sendername'));
        
        defined('ISPC_ERRORMAILTO') || define('ISPC_ERRORMAILTO', $this->getOption('errormailto'));
        defined('ISPC_ERRORSENDERNAME') || define('ISPC_ERRORSENDERNAME', $this->getOption('errormailname'));
        
        
        $mail_transport_cfg = $this->getOption('mail_transport_cfg');
        Zend_Registry::set('mail_transport_cfg', $mail_transport_cfg);
        
    }
    
    protected function _initLocalized()
    {
        $this->bootstrap('Locale');
        
        $locale = $this->getResource('Locale');
        
        /*
         * i don't know where is this LANG used
         */
        define('LANG',  $locale->getLanguage());
    }
    
    /**
     * @cla
     * translation files are taken from the folder,  not from a single files
     * + an optional _cfg[overwrite_language] has been added so you can customize translations for each client of the app (it overwrites the initial translation)
     * + update 31.07.2018 , changed to use translator.ini
     */
    protected function _initTranslator()
    {
        
        $this->bootstrap('Localized');
        
        $this->bootstrap('Translate');
        
        //overwrite language with client custom values
        $overwrite_language = $this->getOption('overwrite_language');
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        if ( ! empty($overwrite_language)
            && is_array($overwrite_language)
            && isset($overwrite_language[$logininfo->clientid]))
        {
            
            $_ext_file =  APPLICATION_PATH . "/language/overwrite/". $overwrite_language[$logininfo->clientid];
            
            if (file_exists($_ext_file)) {
                
                $translator = $this->getResource('Translate');
                
                $locale = $this->getResource('Locale');
                
                $translator->addTranslation(
                    array(
                        'content' => $_ext_file,
                        'locale'  => $locale->getLanguage(), //TODO???????
                        'clear'   => false,
                    )
                    );
            }
        }
        
        //Zend_Validate_Abstract::setDefaultTranslator($translator);//Zend_Form::validate()
        //Zend_Form::setDefaultTranslator($translator);
        
        // Return it, so that it can be stored by the bootstrap; $this->getInvokeArg('bootstrap')->getResource('Translator');
        //         return $translate;
    }
    
    
    
    protected function _initViewResource()
    {
        $this->bootstrap('LayoutResource');
        
        $this->bootstrap('AppInfo');
        
        $this->bootstrap('Useragent');
        
        $this->bootstrap('ControllerHelpers'); // Attention, ViewRenderer was extended
        
        
        $userAgent  = $this->getResource("useragent");
        $device     = $userAgent->getDevice();
        /**
         * WURFL integrated by Zend.. requirese payment/license to use...
         * https://framework.zend.com/manual/1.11/en/zend.http.user-agent.html
         * if you ever need optimal mobile detection pay for it...
         * https://my.scientiamobile.com/license/app/18718/capabilities
         *
         * application.ini -> config[] = useragent.ini
         * ;; USERAGENT
         * resources.useragent.wurflapi.wurfl_api_version = "1.X"
         * resources.useragent.wurflapi.wurfl_lib_dir = APPLICATION_PATH "/../library/wurfl-php-1.X/WURFL/"
         * resources.useragent.wurflapi.wurfl_config_file = APPLICATION_PATH "/configs/ENVIRONMENT/wurfl-config.php"
         *
         * $device->getPhysicalScreenHeight(); $device->hasPhoneNumber(); $device->isTablet();....
         *
         * till then use free github script
         * https://github.com/serbanghita/Mobile-Detect
         */
        $this->getResource("useragent")->getDevice()->setFeature('is_tablet', (new Pms_MobileDetect())->isTablet(), 'product_info');
        
        //ISPC-2827 Ancuta 06.04.2021 
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        if (( ! isset($_COOKIE['mobile_ver']) || $_COOKIE['mobile_ver'] != 'no')
            && $device->getType() == "mobile"
            && $logininfo->isEfaClient != 1 
            ) {
                $_COOKIE['mobile_ver'] = 'yes';
            }
            
            
            if (isset($_REQUEST['setMobileVersion']))
            {
                if ($_REQUEST['setMobileVersion'] == 1 && $logininfo->isEfaClient != 1 ) {
                    $_COOKIE['mobile_ver'] = 'yes';
                } else {
                    $_COOKIE['mobile_ver'] = 'no';
                }
            }
            
            if (isset($_COOKIE['mobile_ver'])) {
                $domain = parse_url(APP_BASE);
                $host = explode(':', $domain['host']);
                $hostname = $host[0];
                setcookie("mobile_ver", $_COOKIE['mobile_ver'], time() + (24 * 3600), '/', $hostname);
            }
            
            
            /**
             * this 3 constants OLD_RES_FILE_PATH, RES_FILE_PATH, IPAD_STYLE_PATH
             * should no longer be used in the view scripts... use <BASE HREF>
             * @deprecated
             */
            if (isset($_COOKIE['mobile_ver']) && $_COOKIE['mobile_ver'] == 'yes' && $logininfo->isEfaClient != 1 )         //ISPC-2827 Ancuta 06.04.2021 - EfaCLient it is not allowed in ipad
            {
                
                define('OLD_RES_FILE_PATH', $this->_options['jsfilepath']);
                define('RES_FILE_PATH', $this->_options['ipad_style']);
                define('IPAD_STYLE_PATH', $this->_options['ipad_style']);
                
                $this->_viewScriptPath = '/themes/ipad/views/';
                
                $this->getResource("useragent")->getDevice()->setFeature('is_tablet', (new Pms_MobileDetect())->isTablet(), 'product_info');
                
                define("ISPC_WEBSITE_VIEW_VERSION", "mobile");
                
            } else {
                
                define("ISPC_WEBSITE_VIEW_VERSION", "desktop");
                
                $this->_viewScriptPath = '/';
                
                define('RES_FILE_PATH', $this->_options['jsfilepath']);
                define('OLD_RES_FILE_PATH', $this->_options['jsfilepath']);
                define('IPAD_STYLE_PATH', $this->_options['ipad_style']);
            }
            
            $this->bootstrap('view');
            
            $view = $this->getResource('view');
            
            /*
             * @cla on 09.01.2018 , hardcoded as default fallback this: APPLICATION_PATH . '/views/scripts/'
             * so it's enough to add a script to the `pc version` in /view/scripts/controlerxxx/actionyyy,
             * and until you create a ipad version that the `pc` will be used
             * $view->addScriptPath() unshifts, so it will be moved down the chain by zend each time you add another)
             */
            $view->setScriptPath(null); //clear paths set in .ini
            
            if ($this->_viewScriptPath != "/") {
                $view->addScriptPath(APPLICATION_PATH . '/views/scripts/');
            }
            
            $view->addScriptPath(APPLICATION_PATH . '/views' . $this->_viewScriptPath . 'scripts/'); // set the path for ipad
            
            
            /**
             * Add Output filters to View
             */
            $view->addFilterPath(APPLICATION_PATH . '/views/filters', 'Version_Control')
            //         ->addFilter('Minify') //not working cause ispc uses no standards
            ;
            
            
            /**
             * add helper Version_Control_
             * we will extend Zend_View_Helper_HeadLink and Zend_View_Helper_HeadScript
             */
            $view->addHelperPath(APPLICATION_PATH . '/views/helpers', 'Version_Control');
            
            /**
             * simple functions that you can use in the view templates
             */
            $view->addHelperPath(APPLICATION_PATH . '/views/helpers', 'Default');
            
            
            $view->doctype('XHTML1_STRICT');//.. @dev have no standards to adhere
            
            /**
             * ATTENTION !
             * viewRenderer used/uses the :suffix = html
             * it is set on the _construct()
             */
    }
    
    
    protected function _initControllerHelpers()
    {
        //https://stackoverflow.com/questions/4701177/zend-action-helper
        //http://zend-framework.wikidot.com/
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers', 'Application_Controller_Helper_');
        
    }
    
    
    protected function _initUseragent()
    {
        return new Zend_Http_UserAgent();
    }
    
    
    
    protected function _initLayoutResource()
    {
        
        $this->bootstrap('ControllerHelpers'); // Attention, Layout was extended
        
        $this->bootstrap('Layout');
        
        $layout = $this->getResource('Layout');
        
        $layout->setViewSuffix('html');
        
    }
    
    
    protected function _initDoctrine()
    {
        // setting up mail transport
        // $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, $mail_transport_cfg);
        // Zend_Mail::setDefaultTransport($mail_transport);
        
        // autoloader
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('Doctrine');
        // $loader->pushAutoloader(array('Doctrine', 'autoload'));
        $loader->pushAutoloader(array(
            'Doctrine',
            'modelsAutoload'
        ));
        
        
        
        $manager = Doctrine_Manager::getInstance();
        
        foreach ($this->_options['doctrine']['attr'] as $key => $val) {
            $manager->setAttribute(constant("Doctrine::$key"), $val);
        }
        
        $manager->setAttribute(Doctrine::ATTR_PORTABILITY, false);
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
        // $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        
        // DQL_CALLBACKS enabled on 25.08.2017
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
        
        
        $conn_sysdat = Doctrine_Manager::connection($this->_options['doctrine']['dsn'][0], 'SYSDAT');
        
        $conn_mdat = Doctrine_Manager::connection($this->_options['doctrine']['dsn'][1], 'MDAT');
        
        $conn_idat = Doctrine_Manager::connection($this->_options['doctrine']['dsn'][2], 'IDAT');
        
        Doctrine::loadModels($this->_options["doctrine"]["module_directories"], Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
        // Doctrine::loadModels($this->_options["doctrine"]["module_directories"], Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
        
        
        //         $queryDbg = new QueryDebuggerListener();
        //         $conn_mdat->addListener($queryDbg);
        
        // Return it, so that it can be stored by the bootstrap; $this->getInvokeArg('bootstrap')->getResource('Doctrine');
        return $manager;
        /**
         * claudiu
         *
         * for utf8 is not enough to set the attributes
         * ATTR_DEFAULT_TABLE_CHARSET = 'utf8'
         * ATTR_DEFAULT_TABLE_COLLATE = 'utf8_unicode_ci'
         *
         * MySQL and PostgreSQL from Doctrine, must run 'SET NAMES utf8'
         *
         * $conn_sysdat->setCharset('utf8');
         * $conn_mdat->setCharset('utf8');
         * $conn_idat->setCharset('utf8');
         *
         * so this will be set as utf8
         * character_set_client = utf8
         * character_set_connection = utf8
         * character_set_results = utf8
         * and collate will allso be set as default(utf8) (myne is utf8_general_ci)
         * collate is locale dependant (de = utf8_german_ci)
         *
         *
         * BUTTTT....
         * in order to go full utf8 you have to change some/more union and join queries cause you get: Illegal mix of collations
         */
    }
    
    /**
     * @cla
     */
    protected function _initFtpserver()
    {
        $ftpserver_cfg = $this->getOption('ftpserver');
        
        Zend_Registry::set('ftpserver', $ftpserver_cfg['login']['host']);
        Zend_Registry::set('ftpserverport', $ftpserver_cfg['login']['port']);
        Zend_Registry::set('ftpserveruser', $ftpserver_cfg['login']['user']);
        Zend_Registry::set('ftpserverpasswd', $ftpserver_cfg['login']['passwd']);
        
        /**
         * FTP_UPLOAD
         * the string "localhost" is used to copy files localy(fake_ftp), and not use ftp
         */
        defined('FTP_UPLOAD') || define('FTP_UPLOAD', $ftpserver_cfg['folder']['type']);
        
        /**
         * FTP_PWD
         * local folder path or ftp_pwd that will hold the uploaded files
         * ftp_pwd from ftp server , default rootbase in PMS_FtpFileupload class was(is) the /
         */
        defined('FTP_PWD') || define('FTP_PWD', $ftpserver_cfg['folder']['pwd']);
        
        /**
         * FTP_QUEUE_PATH
         *
         * temporary path used to hold the ziped files, before put to ftp by a cronjob
         */
        defined('FTP_QUEUE_PATH') || define('FTP_QUEUE_PATH', $ftpserver_cfg['folder']['queue_path']);
        
        /**
         * FTP_DOWNLOAD_PATH
         *
         * temporary path used to hold the ziped and unzipped files, from the download requests
         */
        defined('FTP_DOWNLOAD_PATH') || define('FTP_DOWNLOAD_PATH', $ftpserver_cfg['folder']['download_path']);
        
        //Zend_Registry::set('ftpserver', $ftpserver_cfg);
        
        
        return $ftpserver_cfg;
    }
    
    protected function _initMMI()
    {
        Zend_Registry::set('mmilicname', $this->_options['mmilicname']);
        Zend_Registry::set('mmilicserial', $this->_options['mmilicserial']);
        Zend_Registry::set('mmilicserver', $this->_options['mmilicserver']);
        Zend_Registry::set('localsync', $this->_options['localsync']);
        
        //ISPC-2589 Ancuta 28.05.2020 [migration from clinic CISPC]
        Zend_Registry::set('ontodruglicname', $this->_options['ontodruglicname']);
        Zend_Registry::set('ontodruglicserial', $this->_options['ontodruglicserial']);
        Zend_Registry::set('ontodruglicserver', $this->_options['ontodruglicserver']);
    }
    
    /**
     * @cla
     */
    protected function _initElVi()
    {
        // elVi server loghin (elvi.de)
        Zend_Registry::set('elvi', $this->getOption('elvi'));
    }
    
    /**
     * @cla
     */
    protected function _initVitaBook()
    {
        // vitabook server loghin (order-med.de)
        Zend_Registry::set('vitabook', $this->getOption('vitabook'));
    }
    
    /**
     * @cla
     */
    protected function _initAppInfo()
    {
        // various infos
        Zend_Registry::set('appInfo', $this->getOption('appInfo'));
    }
    
    /**
     * @cla
     */
    protected function _initHospizregister()
    {
        // various infos
        Zend_Registry::set('hospizregister', $this->getOption('hospizregister'));
    }
    
    /*
     protected function _initNamespaces()
     {
     require_once 'dompdf/autoload.inc.php';
     $autoloader = Zend_Loader_Autoloader::getInstance(); // assuming we're in a controller
     $autoloader->pushAutoloader('DOMPDF_autoload');
     
     $this->getResourceLoader()
     }
     */
    
    
    /**
     * @cla on 14.06.2018
     */
    protected function _initHL7servercfg()
    {
        Zend_Registry::set('HL7', $this->getOption('HL7'));
    }
    /**
     * ISPC-2459 Ancuta 06.08.2020
     */
    protected function _initHL7Sendservercfg()
    {
        Zend_Registry::set('HL7_send', $this->getOption('HL7_send'));
    }
    
    /**
     * @cla on 14.06.2018
     */
    protected function _initNet()
    {
        Zend_Loader_Autoloader::getInstance()->registerNamespace('Net')
        //         ->pushAutoloader(array(
            //             'Net',
            //             '__autoload'
            //         ))
        ;
    }
    
    
    /**
     * @cla on 25.09.2018
     */
    protected function _initGoogleapis()
    {
        Zend_Registry::set('googleapis', $this->getOption('googleapis'));
    }
    
    /**
     * @cla
     * general idea is taken from here:
     * Creating Dynamic Log Files In Zend Framework (ZF)
     * https://ranskills.wordpress.com/2012/03/18/creating-dynamic-log-files-in-zend-framework-zf/
     *
     * @return Zend_Log
     */
    protected function _initLog()
    {
        $logersOptions = $this->_options['resources']['log'];//notice the missing getOptions().. so is more easy to read the keys
        
        // log-rotate
        foreach ($logersOptions as $logger => $options) {
            
            if (isset($options['writerName']) && $options['writerName'] == "Stream") {
                
                $baseFilename = $options['writerParams']['stream'];
                $baseFilename .= '/' . $logger . '/';
                
                if ( ! is_dir($baseFilename)) {
                    // create the subfolder
                    if ( ! mkdir($baseFilename, 0777, true)) {
                        // cannot create folder for this logger :(
                        continue;
                    }
                }
                
                $logFilename = '';
                switch (strtolower($options['writerParams']['frequency'])) {
                    case 'daily':
                        $logFilename = $baseFilename . date('Y_m_d');
                        break;
                        
                    case 'weekly':
                        $logFilename = $baseFilename . date('Y_W');
                        break;
                        
                    case 'monthly':
                        $logFilename = $baseFilename . date('Y_m');
                        break;
                        
                    case 'yearly':
                        $logFilename = $baseFilename . date('Y');
                        break;
                        
                    default:
                        $logFilename = $baseFilename . $logger_key;
                }
                
                $logFilename .= '.log'; // file extension
                
                $logersOptions[$logger]['writerParams']['stream'] = $logFilename;
            }
            
            if (isset($options['writerName']) && $options['writerName'] == "Mail") {
                $subjectHardcodedText =  $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'] . ' (' . date("d.m.Y H:i:s") . ')';
                if (isset($options['writerParams']['subject'])) {
                    $logersOptions[$logger]['writerParams']['subject'] = str_replace('%subjectHardcodedText%', $subjectHardcodedText, $options['writerParams']['subject']);
                } elseif (isset($options['writerParams']['subjectPrependText'])) {
                    $logersOptions[$logger]['writerParams']['subjectPrependText'] = str_replace('%subjectHardcodedText%', $subjectHardcodedText, $options['writerParams']['subjectPrependText']);
                }
            }
            
        }
        //
        $logger = Zend_Log::factory($logersOptions);
        
        
        $logger->addPriority('CRONINFO', 10);
        $logger->addPriority('CRONERROR', 11);
        
        $logger->addPriority('RIGHTSINFO', 12);
        $logger->addPriority('RIGHTSERROR', 13);
        
        $logger->addPriority('FTPINFO', 14);
        $logger->addPriority('FTPERROR', 15);
        
        $logger->addPriority('DGPINFO', 16);
        $logger->addPriority('DGPERROR', 17);
        
        
        Zend_Registry::set('logger', $logger);
        
        return $logger;
    }
    
    /**
     * @ancuta on 17.07.2019
     * ISPC-2346 datamatrix scanner for reading medi plan
     */
    protected function _initBarcodereader()
    {
        Zend_Registry::set('barcodereader', $this->getOption('barcodereader'));
    }
    
    /**
     * @ancuta on 11.09.2019
     * ISPC-2411 Palli-Monitor
     */
    protected function _initMypain()
    {
        Zend_Registry::set('mypain', $this->getOption('mypain'));
    }
    
    
    /**
     * @al3x on 30.01.2020
     * ISPC-2432 mePatient
     */
    protected function _initmePatient()
    {
        Zend_Registry::set('mepatient', $this->getOption('mepatient'));
    }
    
    
    
}
