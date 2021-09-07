<?php
/**
 * 
 * @author claudiu
 * @since 03.08.2018
 * this behaviour is a BUG-FIX ... 
 * the Pms_CommonData::ftp_download() was NOT done via a unique selector
 */
class FtpPutQueue2RecordListener extends Doctrine_Record_Listener 
{
    
    /*
     * available options : _savedIDs, parent_table, parent_table_id
     */
    
    
    /**
     * (non-PHPdoc)
     * @see Doctrine_Record_Listener::preInsert()
     */
	public function preInsert(Doctrine_Event $event)
	{
	    
	    switch ($event->getInvoker()->getTable()->getComponentName()) {

	        case "PatientFileUpload":
	        case "ClientFileUpload":
	        case "MemberFiles":
	        case "MembersSepaXml":
	            
	            break;
	            
	        case "FtpPutQueue":
	            
	            if (empty($event->getInvoker()->parent_table) && $this->getOption('parent_table') !== null) {
	                
	                $event->getInvoker()->parent_table = $this->getOption('parent_table');
	            }
	            
	            if (empty($event->getInvoker()->parent_table_id) && $this->getOption('parent_table_id') !== null) {
	                
	                $event->getInvoker()->parent_table_id = $this->getOption('parent_table_id');
	            }
	            
	            break;
                
	        default: 
	            //you should not be here, this listener is just for the tables above
	            break;
	    }
	    
	    return;
	}
	
	
	public function postInsert(Doctrine_Event $event)
	{

	    $primaryKey = $event->getInvoker()->getTable()->getIdentifier();

	    switch ($ComponentName = $event->getInvoker()->getTable()->getComponentName()) {

	        case "PatientFileUpload":
	        case "ClientFileUpload":
	        case "MemberFiles":
	        case "MembersSepaXml":
	             
	            if ($listener = Doctrine_Core::getTable('FtpPutQueue')->getRecordListener()->get('FtpPutQueue2RecordListener')) {

	                $listener->setOption('disabled', false);
	                $listener->setOption('parent_table', $ComponentName);
	                $listener->setOption('parent_table_id', $event->getInvoker()->{$primaryKey});
	                
	                if (($savedIDs = $listener->getOption('_savedIDs')) !== null) {
	                    //this were saved before we inserted, update them too
	                    $this->_updateFPQ($savedIDs, $ComponentName, $event->getInvoker()->{$primaryKey});
	                }
	            }
	             
	            break;
	             
	        case "FtpPutQueue":
	            
	            if (($savedIDs = $this->getOption('_savedIDs')) !== null) {
	                $savedIDs = array_merge($savedIDs, [$event->getInvoker()->{$primaryKey}]);
	            } else {
	                $savedIDs = [$event->getInvoker()->{$primaryKey}];
	            }
	            
	            $this->setOption('_savedIDs', $savedIDs);
	            
	            break;
	    }
	    
		return;
		
	}
	
	
	public function preUpdate(Doctrine_Event $event)
	{
	    switch ($event->getInvoker()->getTable()->getComponentName()) {
	         
	        case "PatientFileUpload":
	        case "ClientFileUpload":
	        case "MemberFiles":
	        case "MembersSepaXml":
	            
	            break;
	            
	        case "FtpPutQueue":
	            
	            if (empty($event->getInvoker()->parent_table) && $this->getOption('parent_table') !== null) {
	                 
	                $event->getInvoker()->parent_table = $this->getOption('parent_table');
	            }
	             
	            if (empty($event->getInvoker()->parent_table_id) && $this->getOption('parent_table_id') !== null) {
	                 
	                $event->getInvoker()->parent_table_id = $this->getOption('parent_table_id');
	            }
	            
	             
	            break;
	    }
	}
	
	public function postUpdate(Doctrine_Event $event)
	{
	    return;
	}
	
	
	
	
	
 	private function _updateFPQ($ids = array(), $parent_table = '',  $parent_table_id = 0)
	{
	    
	    if (empty($ids) || empty($parent_table) || empty($parent_table_id)) {
	        return; //fail-safe
	    }
	    
	    /*
	     * preserve this listener previus state=disabled = true/false, and disable it for this action
	     */
	    $FtpPutQueue2RecordListener = null;
	    $FtpPutQueue2RecordListener_disabled = false;
	    if ($FtpPutQueue2RecordListener = Doctrine_Core::getTable('FtpPutQueue')->getRecordListener()->get('FtpPutQueue2RecordListener')) {
	        $FtpPutQueue2RecordListener_disabled = $FtpPutQueue2RecordListener->getOption('disabled');
	        $FtpPutQueue2RecordListener->setOption('disabled', true);
	    }
	    
	    $update_q = Doctrine_Core::getTable('FtpPutQueue')->createQuery()->update()
	    ->set('parent_table', '?' , $parent_table)
	    ->set('parent_table_id', '?' , $parent_table_id )
	    ->whereIn('id', $ids)
	    ->andWhere('parent_table IS NULL')
	    ->andWhere('parent_table_id IS NULL')
	    ->execute();
	    ;
	    
	    
	    /*
	     * restore previous listener state
	     */
	    if ($FtpPutQueue2RecordListener) {
	        $FtpPutQueue2RecordListener->setOption('disabled', $FtpPutQueue2RecordListener_disabled);
	    }

	    return $update_q;
	} 
	
	


}
?>