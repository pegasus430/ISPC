<?php
require_once("Pms/Form.php");

class Application_Form_ComplaintForm extends Pms_Form
{

	private $triggerformid = ComplaintForm::TRIGGER_FORMID;
	private $triggerformname = ComplaintForm::TRIGGER_FORMNAME;

 
	public function validate($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $Tr = new Zend_View_Helper_Translate();
	     
	    $error = 0;
	    $val = new Pms_Validation();
	
	    if(isset($post['save_and_CloseFile_and_sendTodo']) && strlen($post['save_and_CloseFile_and_sendTodo']))
	    {
	        if(empty($post['form']['comment'])){
	            $this->error_message["comment"] = $Tr->translate('the comment must be filled');
	            $error = 1;
	        }
	        
	        
	    }
	     
	    if($error == 0)
	    {
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}
	
	
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
		 
	}
	
	public function insert_data ( $ipid, $post )
	{
		//invalidate all older formulars
		$this->set_isdelete($ipid);
		
		//remove empty rows
		foreach($post as $k=>$v) {
			if (empty($v['name']) && empty($v['ammount']) && (empty($v['application']) || $v['application']== "" ) && empty($v['freetext']) ) {
				unset($post[$k]);
			}
		}
		
		//and save this new one
		$insert_data = new ComplaintForm();
		$insert_data->ipid = $ipid;		
		$insert_data->formular_values = json_encode($post);
		$insert_data->isdelete = 0;
		$insert_data->save();
		$insert_id = $insert_data->id;

		
		//add verlauf entry - pdf link + formular link?

		
		return $insert_id;
	}

	private function set_isdelete ( $ipid = null )
	{
		$row = Doctrine_Query::create()
		->update('ComplaintForm')
		->set('isdelete', '1')
		->where('ipid = ?', $ipid)
		->andWhere('isdelete = 0')
		->execute();
		
	}
	


	
	

	/**
	 *
	 * @param unknown $ipid
	 * @param unknown $data
	 * @throws Exception
	 * @return NULL|Doctrine_Record
	 */
	public function save_compleiment_form($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		 
		$patient_complaint_form = null;
		
		  
		//formular will be saved first so we have a id
		if ( ! empty($data['form'])) {
			 
			$data['form']['ipid'] = $ipid;
			$entity  = new ComplaintForm();
// 			$patient_complaint_form =  $entity->findOrCreateOneBy('id', null, $data['form']);
			$patient_complaint_form =  $entity->findOrCreateOneByIpidAndId($ipid, $data['form']['id'], $data['form']);

			if ( ! $patient_complaint_form->id) {
	
				throw new Exception('Contact Admin, formular cannot be saved.', 1);
				return null;//we cannot save... contact admin
	
			} else {
	
				if(strlen($data['save_and_sendTodo']) > 0 ){
					//Send todos to users
					$mess = Messages::compleint_action_messages($ipid,$data['userid'],false,$data['form']['status']);
					
				}elseif(strlen($data['save_and_CloseFile_and_sendTodo']) > 0 ){
					
					if( empty($data['form']['id'])){
						$data['form']['id'] = $patient_complaint_form->id;
					}
					//Send todos to users
					$mess = Messages::compleint_action_messages($ipid,$data['userid'],$data['form']['id'],$data['form']['status']);
				}
			}
		} else {
			//nothing to save... you should not be here
			throw new Exception('Contact Admin, empty formular cannot be saved.', 0);
		}
	
	
		return $patient_complaint_form;
	}
	

	public function delete_compleiment_form($ipid = null,$id = null)
	{
		if (empty($ipid) ||  empty($id)) {
			throw new Exception('Contact Admin, formular cannot be deleted.', 0);
		}
		 
		$patient_complaint_form = null;
		
		  
		//formular will be saved first so we have a id
			 
			$data['form']['ipid'] = $ipid;
			$entity  = new ComplaintForm();
// 			$patient_complaint_form =  $entity->findOrCreateOneBy('id', null, $data['form']);
			$patient_complaint_form =  $entity->get_by_id($id,$ipid);
 
			if ( ! empty($patient_complaint_form) && $patient_complaint_form['id'] != $id) {
	
				throw new Exception('Contact Admin, formular cannot be saved.', 1);
				return null;//we cannot save... contact admin
	
			} else {
 
				$row = Doctrine_Query::create()
				->delete('ComplaintForm')
				->where('ipid = ?', $ipid)
				->andWhere('id = ?',$patient_complaint_form['id'])
				->execute();
				
			}
 
	
	
		return $patient_complaint_form;
	}
	
	
	
	
	
}
?>