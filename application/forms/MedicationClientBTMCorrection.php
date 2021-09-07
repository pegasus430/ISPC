<?php
/**
 * 
 * @author claudiu
 *
 */
class Application_Form_MedicationClientBTMCorrection extends Pms_Form 
{
	protected $logininfo = null;
	
	public function init() 
	{

		$this->logininfo = new Zend_Session_Namespace('Login_Info');
	
	}
	
	public function validate($post) 
	{
	
		$Tr = new Zend_View_Helper_Translate();
		$btmbuchhistory_lang = $Tr->translate('btmbuchhistory_lang');
	
		$mcbtmc = new MedicationClientBTMCorrection();
		$columns = $mcbtmc->getTable()->getColumns();
		$allowed_tables = $columns['correction_table']['values'];
		//array('tresor', 'user', 'patient')

		
		if ( ! in_array( $post['table'] , $allowed_tables ) ){
			//manual post? with another table?
			$this->error_message['correction'] = $btmbuchhistory_lang['error_correction_table'];
			return false;
		}
		
			
		
		switch($post['table']) {
			case "MedicationClientStock":{
				
				//first do a find
				$mcs = Doctrine::getTable('MedicationClientStock')->find( $post['id'] );
				
				
				if ( $mcs instanceof MedicationClientStock 
						&& $this->logininfo->clientid == $mcs->clientid 
						&& $mcs->medicationid ==  $post['old_medicationid']
						&& $mcs->methodid == $post['old_methodid']
						&& $mcs->amount == $post['old_amount']	 
				) {

					return true;
					
				} else {
					$this->error_message['correction'] = $btmbuchhistory_lang['error_correction_invalid_record'];
					return false;
				}
				
				
			
				
			}break;
			
			case "MedicationClientHistory":{
					
				//first do a find
				$mcs = Doctrine::getTable('MedicationClientHistory')->find( $post['id'] );
				
				
				if ( $mcs instanceof MedicationClientHistory 
						&& $this->logininfo->clientid == $mcs->clientid 
						&& $mcs->medicationid ==  $post['old_medicationid']
						&& $mcs->methodid == $post['old_methodid']
						&& $mcs->amount == $post['old_amount']	 
				) {

					return true;
					
				} else {
					$this->error_message['correction'] = $btmbuchhistory_lang['error_correction_invalid_record'];
					return false;
				}
				
			}break;
			
			case "MedicationPatientHistory":{
				
				//first do a find
				$mcs = Doctrine::getTable('MedicationPatientHistory')->find( $post['id'] );
				
				
				if ( $mcs instanceof MedicationPatientHistory
						&& $this->logininfo->clientid == $mcs->clientid
						&& $mcs->medicationid ==  $post['old_medicationid']
						&& $mcs->methodid == $post['old_methodid']
						&& $mcs->amount == $post['old_amount']
				) {

					return true;
					
				} else {
					$this->error_message['correction'] = $btmbuchhistory_lang['error_correction_invalid_record'];
					return false;
				}
			
			}break;
			
			default:
				return false;
			
		}
		return false;
	}

	/**
	 * Created by Caludiu 
	 * Ancuta 09.04.2019 - added some changes
	 * 
	 */
	public function InsertData($post , $fake_recursion = false)
	{
		$table_name = $post['table'];

		//delete all other corrections  of this entry
		$mcbtmc = new MedicationClientBTMCorrection();		
		$mcbtmc_correction_new_id = $mcbtmc->get_by_correction_table_correction_id($table_name, array($post['id']), $this->logininfo->clientid);

		if( ! empty($mcbtmc_correction_new_id) ) 
		{
			$correction_new_id = array_column( $mcbtmc_correction_new_id, "correction_new_id");
			
			$q = Doctrine_Query::create()
			->update($table_name)
			->set('isdelete', '1' )
			->WhereIn('id', $correction_new_id)
			->andWhere('clientid = ? ', $this->logininfo->clientid) //extra
			->andWhere('medicationid = ?', $post['old_medicationid'])//extra
			->andWhere('methodid = 13 ')//extra
			->andWhere('isdelete = 0')//extra
			->execute();
			

			//delete all entries into  MedicationClientBTMCorrection table  
			$del_correction_ids = array_column( $mcbtmc_correction_new_id, "id");
				
			$q = Doctrine_Query::create()
			->update("MedicationClientBTMCorrection")
			->set('isdelete', '1' )
			->WhereIn('id', $del_correction_ids)
			->andWhere('clientid = ? ', $this->logininfo->clientid) //extra
			->andWhere('correction_table = ?', $table_name)//extra
			->andWhere('correction_id = ? ', $post['id'])//extra
			->andWhere('isdelete = 0')
			->execute();
				
			//Pms_DoctrineUtil::get_raw_sql($dateddd);
		}

 
		
		// ANCUTA 09.04.2019
		// TODO-2233
		// the new line added in patien history when the method was 8 ( Verbrauch ) from USER stock - incresed, wrongfully,  the patient stock  
		
		$insert_new_ammount = 1;     	
		if ( $table_name == "MedicationPatientHistory" ) {
		    $row_details = MedicationPatientHistory::get_connected_from_id($post['id']);
		    // if Method is Verbrauch from USER stock - do not add in patient stock a new line 
            if ( !empty($row_details) && $row_details['source'] == "u" && $row_details['methodid'] == "8") {
                $insert_new_ammount = 0 ;     	
		    }
		}
 
		    
		$object = new $table_name();
		
		
		$object->clientid = $this->logininfo->clientid;
		$object->medicationid = $post['old_medicationid'];
		
		// TODO-2233
	    if($insert_new_ammount == 0){
			$object->amount = 0;
			$object->sonstige_more = '[Neu menge: '.( abs($post['amount']) - abs($post['old_amount']) )  * (int)self::sign($post['old_amount']).']';
	    } else {
		  $object->amount =  ( abs($post['amount']) - abs($post['old_amount']) )  * (int)self::sign($post['old_amount']) ;
	    }
	    
	    // $object->amount =  ( abs($post['amount']) - abs($post['old_amount']) )  * (int)self::sign($post['old_amount']) ;
	    //--
		
		$object->methodid = 13;
		$object->userid = $post['datatables_data']['userid'];
		$object->ipid = $post['datatables_data']['ipid'];
		$object->done_date = date("Y-m-d H:i:s", strtotime($post['old_done_date']));
		$object->isdelete = 0;
		$object->save();
		
		//add this id into our correction table
						
		$mcbtmc =  new MedicationClientBTMCorrection();
		$mcbtmc->clientid = $this->logininfo->clientid;
		$mcbtmc->correction_table = $table_name;
		$mcbtmc->correction_id = $post['id'];
		$mcbtmc->correction_new_id = $object->id;
		$mcbtmc->amount = abs($post['amount']);
		$mcbtmc->amount_corrected = ( abs($post['amount']) - abs($post['old_amount']) )  * (int)self::sign($post['old_amount']) ;
		$mcbtmc->amount_original = $post['old_amount'];
		$mcbtmc->comment = htmlentities($post['comment'] , ENT_QUOTES | ENT_HTML401 , "UTF-8");
		$mcbtmc->isdelete = 0;
		$mcbtmc->save();

		
		if ( $fake_recursion !== false) {
			return;
		}
		
// 		now search for interconnected rows to fix them too
// 		interconnected rows can be found on MedicationClientHistory\
// 		MedicationClientHistory <-> MedicationClientStock		: stid 
// 		MedicationClientHistory <-> MedicationClientHistory		: self_id
// 		MedicationClientHistory <-> MedicationPatientHistory	: patient_stock_id

		
		
		//update the connected row also
		
		$r_connected = self::get_connected_row($post);
		
		

		
		
		//if table is patient history search for self_id
		$from_patient_connected_id = false;
		$connected_patient = array();
		if ( $table_name == "MedicationPatientHistory" ) {
			$connected_patient = MedicationPatientHistory::find_connected_row($post['id']);
			if ( !empty($connected_patient) && $connected_patient['self_id'] > 0 ) {
			
				$from_patient_connected_id = true;
				$post2 = $post;
				$post2['id'] = $connected_patient['self_id'];
			}
			
		}
		
		if ( empty($r_connected) 
				&& $table_name == "MedicationPatientHistory" 
				&& !empty($connected_patient)
				&& $connected_patient['self_id'] > 0
				&& ! empty($post2)
		) {				
				$r_connected = self::get_connected_row($post2);
		}
		
		

		if ( ! empty($r_connected) 
				&& ( $r_connected['stid'] != 0						
						|| $r_connected['patient_stock_id'] !=0 
						|| $r_connected['self_id'] != 0)) 
		{
			
			if ($r_connected['stid'] > 0 ) {
				
				if ( $table_name == "MedicationClientHistory") {
					//update comes from a user-table event
					$id_connected = $r_connected['stid'];
					$table_name = "MedicationClientStock";
						
				} elseif ( $table_name == "MedicationClientStock") {
					//update comes from a patient-table event
					$id_connected = $r_connected['id'];
					$table_name = "MedicationClientHistory";
				}
				
				
			} elseif ($r_connected['patient_stock_id'] > 0 ) {
				
				if ( $table_name == "MedicationClientHistory") {
					//update comes from a user-table event
					$id_connected = $r_connected['patient_stock_id'];
					$table_name = "MedicationPatientHistory";
					
					//check for verbrauch double-update
					$connected_patient = MedicationPatientHistory::find_connected_row($r_connected['patient_stock_id']);
					if ( !empty($connected_patient) && $connected_patient['self_id'] > 0 ) {
						$post2 = $post;
						$post2['table'] = "MedicationPatientHistory";
						$post2['id'] =  $connected_patient['self_id'];
						self::InsertData($post2, true);
					}
					
									
				} elseif ( $table_name == "MedicationPatientHistory") {
					//update comes from a patient-table event
					$id_connected = $r_connected['id'];
					$table_name = "MedicationClientHistory";

					if ($from_patient_connected_id) {
						
						$connected_id = $connected_patient['self_id'];
						$post2 = $post;
						$post2['table'] = "MedicationPatientHistory";
						$post2['id'] = $connected_id;
						//we work on the assumption that 2 connected row ALLWAYS have SUM(amount) == 0
						$post2['old_amount'] = (-1) * $post2['old_amount'];
						
						self::InsertData($post2, true);
						
						
					} else {
						
						//this else should never happen
// 						(print_r($post));
					}
					
				}
				
				
			} elseif ($r_connected['self_id'] > 0) {

				$id_connected = $r_connected['self_id'];
				$table_name = "MedicationClientHistory";
				
				//get the userid from the connected
				$connected_details = MedicationClientHistory::get_connected_from_self_id($r_connected['self_id']);
				
				if (empty($connected_details)) {
					//something went wrong... can/t update the connected row
					return;
					
				} else {
					$post['datatables_data']['userid'] = $connected_details['userid'];
				}
			 
			} 
						
			
			$post['table'] = $table_name;
			$post['id'] = $id_connected;
			
			//we work on the assumption that 2 connected row ALLWAYS have SUM(amount) == 0
			$post['old_amount'] = (-1) * $post['old_amount'];
			
// 			die(print_r($post));
			
			if ($from_patient_connected_id) {
				
				$post['old_amount'] = (-1) * $post['old_amount'];
				
			}
			
			self::InsertData($post, true);
			
		} 




		
		
		
		return;
		


		
		
	}


	private function get_connected_row($post)
	{
		$table_name = $post['table'];
		
		$q_connected = Doctrine_Query::create()
		->select('id, stid, self_id, patient_stock_id')
		->from("MedicationClientHistory")
		->Where('clientid = ? ', $this->logininfo->clientid) //extra
		->andWhere('medicationid = ?', $post['old_medicationid'])//extra
		->andWhere('isdelete = 0');
		
		
		switch($table_name) {
			case "MedicationClientStock":{
		
				$q_connected->andWhere('stid = ?', $post['id']);
		
			}break;
				
			case "MedicationClientHistory":{
		
				$q_connected->andWhere('id = ? ', $post['id']);
		
			}break;
				
			case "MedicationPatientHistory":{
		
				$q_connected->andWhere('patient_stock_id = ? ', $post['id']);
		
			}break;
		
		}
		
		$r_connected = $q_connected->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY );
		
		return $r_connected;
		
	}
		
	public function DeleteData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$mcss = Doctrine::getTable('MedicationClientBTMCorrection')->find($post['id']);
		
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

	
	
	private function sign($n) {
		return ($n > 0) - ($n < 0);
	}

	
	public function validate_PositiveStock($post)
	{

		$post['validate_step'] = 1;
		
		switch($post['table']) {
			case "MedicationClientStock": {
				$result = MedicationClientStock :: validate_positive_stock_after_correction_date($post);
			}
			break;
			case "MedicationClientHistory": {
				$result = MedicationClientHistory :: validate_positive_stock_after_correction_date($post);
			}
			break;
			case "MedicationPatientHistory": {
				$result = MedicationPatientHistory :: validate_positive_stock_after_correction_date($post);
			}
			break;
				
		}
		return $result;
		
	}
	
	
	
}

?>