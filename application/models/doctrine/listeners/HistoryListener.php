<?php

/**
 * HistoryListener 
 * Ancuta by Claudiu
 * 20.12.2018 
 * @package    ISPC
 */
class HistoryListener extends Doctrine_Record_Listener
{

    /**
     * Array of Column names that should be encrypted
     *
     * @var string
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param string $options            
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    public function postUpdate(Doctrine_Event $event)
    {
        $invoker = $event->getInvoker();
        $log_model = $invoker->getTable()->getComponentName() . 'History';
        
        if (Doctrine_Core::isValidModelClass($log_model) && $entity = new $log_model()) {

            $last_modified = $invoker->getLastModified(true);
            if (!empty($last_modified)){

                $last_modified['id'] = $invoker->id;
                $entity->fromArray($last_modified);
                $entity->save();
            }
        }
    }
}
