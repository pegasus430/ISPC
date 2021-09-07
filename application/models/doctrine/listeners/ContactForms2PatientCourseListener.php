<?php
/**
 * 
 * @author claudiu
 * created for ISPC-2071
 * 
 * 
 * we use it to link a ContactForms with PC-entries done in this same life-cycle
 * if you insert a ContactForms, this listener will add done_id+done_name if they are empty
 * 
 * TODO 1: preDqlUpdate if/maybe needed
 * TODO 2: after the linking, unset _savedPCs, so you cannot set-it again to another cf.. if you mess up 
 * 
 *  
 * @package    ISPC
 * @subpackage Application (2018-01-09)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ContactForms2PatientCourseListener extends Doctrine_Record_Listener 
{
	
	public function preInsert(Doctrine_Event $event)
	{
	    switch ($event->getInvoker()->getTable()->getComponentName()) {
	        
	        case "ContactForms":
	            
	            break;
	            
	        case "PatientCourse":
	            
	            if (empty($event->getInvoker()->done_id) && $this->getOption('done_id') !== null) {
	                
	                $event->getInvoker()->done_id = $this->getOption('done_id');
	            }
	            
	            if (empty($event->getInvoker()->done_name) && $this->getOption('done_name') !== null) {
	                
	                $event->getInvoker()->done_name = $this->getOption('done_name');
	            }
	            
	            
	            break;
                
	        default: 
	            //you should not be here, this listener is just for the 2 tables above
	            break;
	    }
	    
	    return;
	}
	
	
	public function postInsert(Doctrine_Event $event)
	{
	    $primaryKey = $event->getInvoker()->getTable()->getIdentifier();
	    
	    switch ($event->getInvoker()->getTable()->getComponentName()) {
	         
	        case "ContactForms":
	            
	            if ($listener = Doctrine_Core::getTable("PatientCourse")->getRecordListener()->get('ContactForms2PatientCourseListener')) {

	                $done_name = ContactForms::PatientCourse_DONE_NAME;
	                $done_name_encrypted = Pms_CommonData::aesEncrypt($done_name);
	    
	                $listener->setOption('disabled', false);
	                $listener->setOption('done_id', $event->getInvoker()->{$primaryKey});
	                $listener->setOption('done_name', $done_name_encrypted);
	                
	                if (($savedPCs = $listener->getOption('_savedPCs')) !== null) {
	                    //this pc's were saved before we inserted the contactform, update them too
	                    $this->_updatePC($savedPCs, $event->getInvoker()->{$primaryKey}, $done_name_encrypted);
	                }
	            }
	             
	            break;
	             
	        case "PatientCourse":
	            
	            if (($savedPCs = $this->getOption('_savedPCs')) !== null) {
	                $savedPCs = array_merge($savedPCs, [$event->getInvoker()->{$primaryKey}]);
	            } else {
	                $savedPCs = [$event->getInvoker()->{$primaryKey}];
	            }
	            
	            $this->setOption('_savedPCs', $savedPCs);
	            
	            break;
	            
            default:
                //you should not be here, this listener is just for the 2 tables above
                break;
	    }
	    
		return;
		
	}
	
	
	public function preUpdate(Doctrine_Event $event)
	{
	    switch ($event->getInvoker()->getTable()->getComponentName()) {
	         
	        case "ContactForms":
	            
	            break;
	             
	        case "PatientCourse":
	            
	            if (empty($event->getInvoker()->done_id) && $this->getOption('done_id') !== null) {
	                 
	                $event->getInvoker()->done_id = $this->getOption('done_id');
	            }
	             
	            if (empty($event->getInvoker()->done_name) && $this->getOption('done_name') !== null) {
	                 
	                $event->getInvoker()->done_name = $this->getOption('done_name');
	            }
	            
	             
	            break;
	    }
	}
	
	public function postUpdate(Doctrine_Event $event)
	{
	    return;
	}
	
 	private function _updatePC($ids = array(), $done_id = 0,  $done_name = '')
	{
	    
	    if (empty($ids) || empty($done_id) || empty($done_name)) {
	        return; //fail-safe
	    }
	    
	    /*
	     * preserve this listener previus state=disabled = true/false, and disable it for this action
	     */
	    $ContactForms2PatientCourseListener = null;
	    $ContactForms2PatientCourseListener_disabled = false; 
	    if (($ContactForms2PatientCourseListener = Doctrine_Core::getTable("PatientCourse")->getRecordListener()->get('ContactForms2PatientCourseListener'))) {
	        $ContactForms2PatientCourseListener_disabled = $ContactForms2PatientCourseListener->getOption('disabled');
	        $ContactForms2PatientCourseListener->setOption('disabled', true);
	    }
	    
	    $PatientInsertListener = null;
	    $PatientInsertListener_disabled = false;
	    if (($PatientInsertListener = Doctrine_Core::getTable("PatientCourse")->getRecordListener()->get('PatientInsertListener'))) {
	        $PatientInsertListener_disabled = $PatientInsertListener->getOption('disabled');
	        $PatientInsertListener->setOption('disabled', true);
	    }
	    
	    
	    $update_q = Doctrine_Core::getTable("PatientCourse")->createQuery()->update()
	    ->set('done_id', '?' , $done_id)
	    ->set('done_name', '?' , $done_name)
	    ->whereIn('id', $ids)
	    ->andWhere('done_id = 0')
	    ->andWhere('done_name = \'\'')
	    ->execute();
	    ;
	    
	    
	    /*
	     * restore previous listener state
	     */
	    if ($ContactForms2PatientCourseListener) {
	        $ContactForms2PatientCourseListener->setOption('disabled', $ContactForms2PatientCourseListener_disabled);
	    }
	    if ($PatientInsertListener) {
	        $PatientInsertListener->setOption('disabled', $PatientInsertListener_disabled);
	    }

	    return $update_q;
	} 
	
	


}
?>