<?php

require_once("Pms/Form.php");

class Application_Form_FinalDocumentationLocation extends Pms_Form{




	public function clear_form_data($ipid, $form_id )
	{
		if (!empty($form_id))
		{
			$Q = Doctrine_Query::create()
			->update('FinalDocumentationLocation')
			->set('isdelete','1')
			->where("form_id='" . $form_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
			$Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}



	public function insertFinalDocumentationLocation($post, $ipid, $form_id){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		foreach($post['ff_location']  as $row_id=>$row_values){

				
			if(!empty($row_values['von'])){
				$row_values['von'] = date('Y-m-d 00:00:00', strtotime($row_values['von']));
			} else{
				$row_values['von'] = '0000-00-00 00:00:00';
			}
				

				
			if(!empty($row_values['bis']) && $row_values['bis'] != '-'){
				$row_values['bis'] = date('Y-m-d 00:00:00', strtotime($row_values['bis']));
			} else{
				$row_values['bis'] = '0000-00-00 00:00:00';
			}
				
			$records[] = array(
					"ipid" => $ipid,
					"form_id" => $form_id,
					"von" => $row_values['von'],
					"bis" => $row_values['bis'],
					"krankenhaus" => $row_values['krankenhaus'],
					"decomp_pat" => $row_values['decomp_pat'],
					"decomp_um" => $row_values['decomp_um'],
					"pat_wun" => $row_values['pat_wun']
			);
		}

		$clear_form_data = $this->clear_form_data( $ipid, $form_id);

		$collection = new Doctrine_Collection('FinalDocumentationLocation');
		$collection->fromArray($records);
		$collection->save();

	}

}

?>