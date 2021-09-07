<?php
// class Zend_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_Abstract
//class Application_Controller_Helper_ViewRenderer extends Zend_Controller_Action_Helper_Abstract

/**
 * 
 * @author claudiu✍ 
 * Oct 15, 2018
 *
 * TODO: something like,.... https://akrabat.com/extending-viewrenderer-for-layouts/
 * BUT without  $viewRenderer = new Controller_Action_Helper_ViewRenderer();
 */
class Application_Controller_Helper_ViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer
{
    /**
     * view-ing device type
     * @var string
     */
    private $_deviceType = null; //mobile|null
    
    /**
     *
     * Set the viewSuffix to "html" unless a viewSuffix option is 
     * provided in the $options parameter.
     * 
     * @param  Zend_View_Interface $view 
     * @param  array $options 
     * @return void
     */
    public function __construct(Zend_View_Interface $view = null, array $options = array())
    {
        if ( ! isset($options['viewSuffix'])) {
            $options['viewSuffix'] = 'html'; //ISPC had this changed as default... a very `good` idea 
        }
        
        parent::__construct($view, $options);
        
    }
    
   
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
     * 
     * (non-PHPdoc)
     * @see Zend_Controller_Action_Helper_ViewRenderer::setViewSuffix()
     */
    public function setViewSuffix($suffix)
    {
        if ($this->_deviceType == 'mobile' && null !== ($inflector = $this->getInflector()) ) 
        {
            $inflector->setStaticRule('original.suffix', $suffix);
        }
        
        return parent::setViewSuffix($suffix);
    }
    
    
    /**
     * we check to see if a mobile version of the view template exists.
     * this allows us to load the mobile view if it exists and the default if it doesnt.
     * 
     * not all inflector rules&target are set by reference for the view... but this->setViewSuffix & this->setViewScriptPathSpec is enough for now

     */
    public function renderScript($script, $name = null)
    {
        /*
         * DEBUG
        $script0 = $script;
        */
        if ($this->_deviceType == 'mobile') {
            
            $scriptPath = $this->view->getScriptPath($script);
            
            if ( ! $scriptPath) {
                
                $inflector = $this->getInflector();
                            
                $original_suffix  = $inflector->getRules('original.suffix');            
                $original_target  = $inflector->getRules('original.target');
                
                $this->setViewSuffix($original_suffix);
                $this->setViewScriptPathSpec($original_target);
                
                $script = $this->getViewScript();
            }
        } else {
            //change extension to html || phtml if current one is missing
            $scriptPath = $this->view->getScriptPath($script);
            if ( ! $scriptPath) {

                if ($this->getViewSuffix() == 'phtml')
                    $this->setViewSuffix('html');
                elseif ($this->getViewSuffix() == 'html') 
                    $this->setViewSuffix('phtml');
                
                $script = $this->getViewScript();
            }
        }
        /*
         * DEBUG
        if (  ! 1 && APPLICATION_ENV == 'development') {
            
            $_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
            $_logger->debug(["view requested" => $script0, 'view used' => $script]);
            
            if (null === $name) {
                $name = $this->getResponseSegment();
            }
            
            $this->getResponse()->appendBody(
                implode("<br/>\n", [
                    ! empty($this->view->layout_requested) ? ($this->view->layout_requested == $this->view->layout_used ? "layout: {$this->view->layout_requested}" : "<b style='color:fuchsia'>layout NOT found: {$this->view->layout_requested} <br/>layout used : {$this->view->layout_used}</b>") : '',
                    $script0 == $script ? "view: {$script}" : "<b style='color:red'>view NOT found: {$script0} <br/>view used : {$script}</b>",
//                     $this->view->getScriptPath($script)
                ])
                . $this->view->render($script),
                $name
            );
            
            $this->setNoRender();
            
        } else {
        
            parent::renderScript($script, $name);
        }
        */
        
        parent::renderScript($script, $name);
    }
    
   
}
    