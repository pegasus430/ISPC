<?php
/*
 * changed by @cla on 07.03.2018 ... don't know when will be uploaded on svn or on live..
 */
class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) {
            
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Sorry for your inconvinience.this page is under manintenance';
                $this->_log_error($errors->exception);
                
                break;
            
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Sorry for your inconvinience.this page is under manintenance';
                $this->_log_error($errors->exception);
                $this->_redirect('overview/overview');
                
                break;
            
            default:
                
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Sorry for your inconvinience.this page is under manintenance';
                $this->_log_error($errors->exception);
                
                break;
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request = $errors->request;
    }

    public function previlegeAction()
    {
        $this->view->error_message = $this->view->translate("youdonothavepermission");
    }

    public function nofileAction()
    {
        $this->view->error_message = $this->view->translate("templatefiledoesnotexist");
    }

    public function noclientAction()
    {
        $this->view->error_message = $this->view->translate("selectclient");
    }

    
    
    /*
     * file logger
     * EMERG = 0; // Emergency: system is unusable
     * ALERT = 1; // Alert: action must be taken immediately
     * CRIT = 2; // Critical: critical conditions
     * ERR = 3; // Error: error conditions
     * WARN = 4; // Warning: warning conditions
     * NOTICE = 5; // Notice: normal but significant condition
     * INFO = 6; // Informational: informational messages
     * DEBUG = 7; // Debug: debug messages
     *
     * CRONINFO = 10 //cronController
     * CRONERROR = 11
     *
     * RIGHTS = 12 //PatientPermissions record model
     * RIGHTSMAIL = 13
     *
     * FTPINFO = 14 Pms_FtpFileupload, Pms_FtpFileuploadFakeLocalhost
     * FTPERROR = 15
     * 
     *Zend_Controller_Action_Exception
     *Doctrine_Connection_Mysql_Exception
     */
    private function _log_error(Exception $exception) 
    {
        try {
            $logger = $this->getHelper('Log');
            
            $message = "via ErrorController"
                . PHP_EOL . "Exception : ". $exception->getMessage()
                . PHP_EOL . "Trace : " . $exception->getTraceAsString();
            
            $logger->log($message, Zend_Log::CRIT);
            
        } catch (Exception $e) {
            //die($e->getMessage());
        }
    }
    
    public function userlimitAction()
    {
        $this->_helper->viewRenderer('previlege');
        $this->view->error_message = $this->view->translate("userlimitisexceedsocontactyourAdministratortoincreaseit");
    }

    public function undermaintainanceAction()
    {
        $this->_helper->layout->setLayout('layout_blank');
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (! $bootstrap->hasPluginResource('Log')) {
            return false;
        }
        
        $log = $bootstrap->getResource('Log');
        return $log;
    }

}