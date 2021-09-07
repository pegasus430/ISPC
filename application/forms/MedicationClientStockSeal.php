<?php
/**
 * 
 * @author claudiu
 *
 */
class Application_Form_MedicationClientStockSeal extends Pms_Form 
{
	public function validate($post) 
	{
		/**
		 * @todo
		 * validate if same seal_date allready exists, so we don't re-insert again
		 */
		
		if ( empty ($post['seal_date']) ) {
			return false;
		}
		
		$max_time = strtotime("now");
		
		$min_time = MedicationClientStockSeal::get_default_seal_timestamp(); //strtotime("-5 Years");
		
		$post_time = strtotime($post['seal_date']);
		
		if ($post_time > $min_time && $post_time < $max_time){
			return true;	
		} else {
			$Tr = new Zend_View_Helper_Translate();
			$btmseal_lang = $Tr->translate('btmseal_lang');
			$this->error_message['date'] = $btmseal_lang['error_seal_date'];
			return false;
		}
	}

	
	public function InsertData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		
		$date = date_create($post['seal_date']);
		$post['seal_date'] = date_format($date, 'Y-m-d');
		
		$mcss = new MedicationClientStockSeal();
		$mcss->seal_date = $post['seal_date'];
		$mcss->clientid = $clientid;
		$mcss->isdelete = 0;
		$mcss->save();
				
		return $mcss->id;
	}


		
	public function DeleteData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$mcss = Doctrine::getTable('MedicationClientStockSeal')->find($post['id']);
		
		if ($mcss instanceof MedicationClientStockSeal ) {
			if ($clientid == $mcss->clientid) {
				$mcss->delete();
				return true;
			} 
		}		
		
		$Tr = new Zend_View_Helper_Translate();
		$btmseal_lang = $Tr->translate('btmseal_lang');
		$this->error_message['delete'] = $btmseal_lang['error_delete_fail'];
		return false;
	}		

}

?>