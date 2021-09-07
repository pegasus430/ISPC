<?php
/**
 * 
 * @author claudiu 
 * Oct 18, 2018
 * 
 * this will prevent deleting a Doctrine_Record with indrop = 0
 *
 */
class PreventIndrodDelete extends Doctrine_Record_Listener
{

    /**
     * __construct
     *
     * @param string $options
     * @return void
    */
    public function __construct(array $options)
    {
        $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
    }
    
    
    public function preDelete(Doctrine_Event $event)
    {
        if ( ! isset($this->_options['indrop'])) {
            return;
        }
                
        $invoker = $event->getInvoker();
        
        if ($invoker->getTable()->hasColumn($this->_options['indrop']) 
            && isset($invoker->{$this->_options['indrop']}) 
            && $invoker->{$this->_options['indrop']} == '0') 
        {
            
            // this will prevend deleting only from my action.. todo change this
            //$event->skipNextListener();
            if (Zend_Controller_Front::getInstance()->getRequest()->getControllerName() == 'patientnew' 
                && Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'versorger') 
            {
                
                if ($listener = $invoker->getTable()->getRecordListener()->get('SoftdeleteListener')) {
                    $listener->setOption('disabled', true);
                }
                
                $event->skipOperation(); 
            }
        }    
        
        return;
    }
    
    
    
    public function preDqlDelete(Doctrine_Event $event)
    {
       //TODO
    }
} 