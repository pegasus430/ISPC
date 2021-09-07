<?php
/**
 * 
 * @author claudiu
 *
 */
class HidemagicListener extends Doctrine_Record_Listener 
{
	
	protected $_options = array();
	
	protected $_hidemagic = null; // cfg ini
	
	protected $_last_ipid = null; // _session['last_ipid']
	// 		$this->_last_ipid->ipid
	// 		$this->_last_ipid->isadminvisible
	
	
	public function __construct(array $options)
	{
		$this->_options = $options;
		
		$this->_hidemagic = Zend_Registry::get('hidemagic');

		$this->_last_ipid = new Zend_Session_Namespace('last_ipid');
		

	}
	
	
	//isadminvisible is defined as single patient setting
	public function preHydrate( Doctrine_Event $event )
	{
		
		if ( isset($this->_last_ipid->isadminvisible) && ($this->_last_ipid->isadminvisible == 0) ) {
			
			$data = $event->data;
			
			//compare the ipid only if we have one
			if ( isset($data['ipid']) && ( ! isset($this->_last_ipid->ipid) || $data['ipid'] != $this->_last_ipid->ipid ) ) {
				
				return;//ipids do not match
			}		
			
			foreach($this->_options as $column)
			{				
				if( isset($data[$column]) )
				{
					$data[$column] = $this->_hidemagic;
				}
			}
			
			$event->data = $data;
		}
	
		
	}
	
}
?>