<?php

/**
 * 
 * @author claudiu✍ 
 * Oct 25, 2018
 *
 */
class Application_Controller_Helper_Layout extends Zend_Layout_Controller_Action_Helper_Layout
{

    /**
     * view-ing device type
     * @var string
     */
    private $_deviceType = null; //mobile|null
    
    private $_mobile_Layout_Target  = ":device/:script.:suffix";
    
    private $_mobile_suffix         = "phtml";
    

    /**
     * Get view-ing device type
     *
     * @return string
     */
    public function getDeviceType()
    {
        return $this->_deviceType;
    }
    
    
    /**
     * Set view-ing device type
     *
     * @param  string $device
     * @return Application_Controller_Helper_ViewRenderer
     */
    public function setDeviceType($deviceType)
    {
        $this->_deviceType = (string) $deviceType;
        return $this;
    }
    
    /**
     * Here we check to see if a mobile version of the layout template exists.
     * this allows us to load the mobile layout if it exists and the default if it doesnt.
     * 
     * inflector rules&target are set by reference for the layout... so just call this->setViewSuffix & this->setInflectorTarget
     */
    public function setLayout($name)
    {
        /*
         * DEBUG
        if (APPLICATION_ENV == 'development') {
            $nameMobile = $this->getInflector()->filter(array('script' => $name));
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->layout_requested = $nameMobile;
        }
        */
        
        //TODO this if is not 0k.... check the if from mobile..
        if ($this->_deviceType == 'mobile' && null !== ($inflector = $this->getInflector()) && $inflector->getRules('device')) {

            $inflector->setTarget($this->_mobile_Layout_Target);
            
            $nameMobile = $inflector->filter(array('script' => $name, 'suffix' => $this->_mobile_suffix));
            
            /*
             * DEBUG
            if (APPLICATION_ENV == 'development') {
                Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->layout_requested = $nameMobile;
            }
            */
            $fileMobile = $this->getLayoutInstance()->getLayoutPath() . $nameMobile;

            if ( ! file_exists($fileMobile) || ! is_readable($fileMobile)) {
                //missing mobile file, fallback to default                
                $original_suffix = $inflector->getRules('original.suffix');
                $original_target = $inflector->getRules('original.target');
                
                $this->setViewSuffix($original_suffix);
                $this->setInflectorTarget($original_target);
                
            } else {
                //this else comes from a controller called ->_helper->layout->set
                $this->setViewSuffix($this->_mobile_suffix);
                $this->setInflectorTarget($this->_mobile_Layout_Target);
            }
            
//             $_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
//             $_logger->debug(["layout requested" => $nameMobile, 'layout used' => $inflector->filter(array('script' => $name))]);
            
        }
        
        
        if (null !== ($inflector = $this->getInflector())) {
            
            $nameScript = $inflector->filter(array('script' => $name, 'suffix' =>  $inflector->getRules('suffix')));
            
            $fileScript = $this->getLayoutInstance()->getLayoutPath() . $nameScript;
            
            if ( ! file_exists($fileScript) || ! is_readable($fileScript)) {
                
//                 $inflector->setStaticRule('suffix', 'html');
                $this->setViewSuffix('html');
            }
            
//          
        }
        
        
        /*
         * DEBUG
        if (APPLICATION_ENV == 'development') {
            $nameMobile = $this->getInflector()->filter(array('script' => $name));
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->layout_used = $nameMobile;
        }
        */
        
        return parent::setLayout($name);
    }
}