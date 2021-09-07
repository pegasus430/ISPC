<?php
/**
 * 
 * @author claudiu
 *
 */
class PatientContactphoneHydrateListener extends Doctrine_Record_Listener 
{
	
	protected $_options = array();
	
	

	public function __construct(array $options)
	{
		$this->_options = $options;		

	}
	
	
	//isadminvisible is defined as single patient setting
	public function preHydrate( Doctrine_Event $event )
	{			
		$data = $event->data;
	
			
		$data['phone_number'] =  '';
		
		$data['phone_number'] .= ! empty($data['phone']) ? $data['phone'] : '';

		$data['phone_number'] .= empty($data['mobile']) ? '' : ( ! empty($data['phone']) ? "; " . $data['mobile'] : $data['mobile']);
		
		$event->data = $data;
	
	}
	
}
?>