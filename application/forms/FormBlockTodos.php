<?php
require_once("Pms/Form.php");

/**
 * http://10.0.0.36/ispc20172/public/patientform/kvnoassessment?id=aFFONDhqV01SUXBLN0dYQWZ3PT0=
 * @author claudiu  - changed all the fn
 * Jul 13, 2017 
 * 
 * @update Jan 30, 2018: @author claudiu, checked/changed for ISPC-2071
 * 
 */
class Application_Form_FormBlockTodos extends Pms_Form
{
	
	private $triggerformid = 0; //use 0 if you want not to trigger 
	
	private $triggerformname = "frmFormBlockTodos";  //define the name if you want to piggyback some triggers

	
	public function InsertData($post = array(), $allowed_blocks = array())
	{
	    
	    if ( ! in_array($this->_block_name, $allowed_blocks) && empty($post['old_contact_form_id'])) {
	        return;// we have no permission to this block
	    }
	    
		//added for Pms_Form
		$clientid = $this->logininfo->clientid;	

		//ISPC-1857 Administrator : 2) hmm. i would say, that "old" TODOs are removed from the edit view. so todos are fired in the moment the form is submitted first time. to prevent re-firing them remove them in edit form.
		//replicate the old todos here, so you an delete them only by verlayf=>wrong entry
		$this->duplicate_old_values( $post , $allowed_blocks);
		
		if ( ! in_array($this->_block_name, $allowed_blocks) )
		{
			return;// we have no permission to this block, nothing else to do here
		}	
		
		foreach ($post['todos'] as $k=>$entry)
		{	
			$entry['user'] = array_values($entry['user']);

			if ( trim($entry['text']) == "" 
					|| empty($entry['user'])
					|| (sizeof($entry['user']) == 1 && $entry['user'][0] == "0")
			) {
				unset($post['todos'][$k]);
				continue; //this entry is not good
			}			
		}
		
		/*
		//permissions ok, compare old block entrys with the new ones in order to not overwrite the performed todos
		$compare_val = $this->old_vs_new_values( $post , $allowed_blocks);
		if (! empty($compare_val)) {
			
			$not_changed_arr = $compare_val["not_changed"];
			$changed_arr = $compare_val["changed"];
			$to_be_deleted = $compare_val["to_be_deleted"];
			$to_be_inserted = $compare_val["to_be_inserted"];
			
			$post['todos'] = $to_be_inserted;
			..... to be continued...
			
		}
		*/
		//set as deleted the values from the old formular
		//$this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
				
		if (empty ($post['todos'])) {
			return; //break the execution cycle because we have nothing to insert
		}
	
		$course_date = date("Y-m-d H:i:s",time());
		$course_type = "W";
		
		$fbt_array = array();
		$todoid_array = array();
		
		foreach ($post['todos'] as $entry)
		{
			
			$entry['verlauftext'] = $entry['text'] . " |---------| " . implode(',', $entry['user']) . " |---------| " . $entry['date'] ." |---------| 0";

			$cust = array();
			$cust['ipid'] = $post['ipid'];
			$cust['course_date'] = $course_date;
			$cust['course_type'] = $course_type;
			$cust['course_title'] = $entry['verlauftext'];
			$cust['user_id'] = $this->logininfo->userid;
			$cust['tabname'] = "block_todos";
			$cust['done_id'] = $post['contact_form_id'];
						
			$pc_obj = new PatientCourse();
			$pc_obj->triggerformid = 0;//force exit the PatientCourse dbf triggers
			$pc_obj->triggerformname = 0;
			$course_id = $pc_obj->set_new_record($cust);

// 			$cust_pc->recordid = $todo_id;
		
			//piggyback	
			$gpost = array(
					"todo_text"		=> $entry['text'],
					"todo_date"		=> $entry['date'],
					"todo_users"	=> $entry['user'],
			);
			
			$event = new Doctrine_Event($pc_obj, Doctrine_Event::RECORD_SAVE);
			$trigger_ToDos_obj = new application_Triggers_addValuetoToDos();
			$trigger_ToDos_obj->triggerAddValuetoTodos($event, null, $this->triggerformname, $this->triggerformid, 2, $gpost);
			$todos = $trigger_ToDos_obj->get_last_insert_ids();
			
			if (! empty($todos)) {
				$pc_obj->recorddata = serialize(array(
						"todo_id" => $todos,
						"contact_form_id" => $post['contact_form_id'],
				));
				$pc_obj->recordid = 0;
				$pc_obj->save();
					
				foreach($todos[$course_id] as $todoid) {

					$fbt_array[] = array(
							"ipid" => $post['ipid'],
							"contact_form_id" => $post['contact_form_id'],
							"todo_id" => $todoid,
								
					);
					
					$todoid_array[] = $todoid;
				}
				
				
				$pc_obj->set_new_record(array(
						'recorddata' => serialize($todoid_array),
						'isserialized' => 1
				 ));
				
			}
			
		}
		
		//insert FormBlockTodos
		$collection = new Doctrine_Collection('FormBlockTodos');
		$collection->fromArray($fbt_array);
		$collection->save();
		
		return; 
	}
	
	
	
	public function clear_block_data_OLD($ipid, $contact_form_id )
	{
		if (!empty($contact_form_id))
		{
			//first get linked todos an mark them as delete
			$Q= Doctrine_Query::create()
			->select('todo_id')
			->from('FormBlockTodos')
			->where("contact_form_id='" . $contact_form_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
			$result = $Q->fetchArray();
				
			$todos=array();
			foreach ($result as $row){
				$todos[]=$row['todo_id'];
			}
	
			if (count($todos)>0){
				$todos=implode(",",$todos);
	
	
				$T= Doctrine_Query::create()
				->update('ToDos')
				->set('isdelete','1')
				->where("id IN (".$todos.")")
				->andWhere('ipid LIKE "' . $ipid . '"');
				$result = $T->execute();
	
				//then delete todo-block itself
				$Q = Doctrine_Query::create()
				->update('FormBlockTodos')
				->set('isdelete','1')
				->where("contact_form_id='" . $contact_form_id. "'")
				->andWhere('ipid LIKE "' . $ipid . '"');
				$result = $Q->execute();
			}
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	public function InsertData_OLD($post, $allowed_blocks)
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$todos_block = new FormBlockTodos();
		
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);
		if (strlen($post['old_contact_form_id']) > 0)
		{
			$todos_old_data = $todos_block->getPatientFormBlockTodos($post['ipid'], $post['old_contact_form_id'], true);

			if ($todos_old_data)
			{
				// overide post data if no permissions on ebm block
				if (!in_array('todos', $allowed_blocks))
				{					
					$post['todos']=$todos_old_data;
				}
			}
		}

		
		foreach ($post['todos'] as $entry){
			if (intval($entry['user'])>0 && strlen($entry['text'])>0)
			{
				
				$group_id=0;

				if($entry['isgroup']>0){
					$Q= Doctrine_Query::create()
					->select('*')
					->from('User')
					->where("id='" . $entry['user'] . "'");
					$result = $Q->fetchArray();

					$group_id=$result[0]['groupid'];
				}
				
				$t=new ToDos();
				$t->client_id=$clientid;
				$t->user_id=$entry['user'];
				$t->ipid=$post['ipid'];
				$t->todo=$entry['text'];
				$t->group_id=$group_id;
				$t->create_date=date("Y-m-d H:i:s", time());
				$form_date = explode(".", $entry['date']);			 
				$t->until_date=$form_date[2] . "-" . $form_date[1] . "-" . $form_date[0] . ' ' . date("H") . ':' . date("i") . ":00";;
				$t->additional_info='u'.$entry['user'];
				$t->save();	
					
				$todo_id=$t->id;
				
				$cust_fbt = new FormBlockTodos();
				$cust_fbt->ipid = $post['ipid'];
				$cust_fbt->contact_form_id = $post['contact_form_id'];
				$cust_fbt->todo_id = $todo_id;
				$cust_fbt->save();		

				
				$cust_pc = new PatientCourse();
				$cust_pc->ipid = $post['ipid'];
				$cust_pc->course_date = date("Y-m-d H:i:s",time());
				$cust_pc->course_type=Pms_CommonData::aesEncrypt('W');
				$verlauftext=$entry['text']." |---------| ".$entry['user']." |---------| ".$entry['date']." |---------| 0";	
				$cust_pc->course_title=Pms_CommonData::aesEncrypt($verlauftext);
				$cust_pc->user_id = $userid;
				$cust_pc->recordid = $todo_id;
				$cust_pc->save(); 
				$course_id = $cust_pc->id; 
				
				// update todo - add course_id
				$todo_entry = Doctrine::getTable('ToDos')->find($todo_id);
				if($todo_entry){
					$todo_entry->course_id  = $course_id ;
					$todo_entry->save();
				}
			}	
		}
	}
	
	private function duplicate_old_values($post = array(), $allowed_blocks = array()) 
	{
		//if this user has no permisions o this contactform block
		//duplicate the old values
		if ((int)$post['old_contact_form_id'] > 0)
		{
			$todos_block = new FormBlockTodos();
			$todos_old_data = $todos_block->getPatientFormBlockTodos($post['ipid'], $post['old_contact_form_id'], true);
			
			$new_rows =  array();
			$deleted_rows_ids =  array();
			$change_date = date("Y-m-d H:i:s", time());
			
			if ( ! empty($todos_old_data['all']))
			foreach ($todos_old_data['all'] as $row) {
					
				$deleted_rows_ids[] = $row['id'];
				unset($row['ToDos']);
					
				$row['change_date'] = $change_date;
				$row['change_user'] = $this->logininfo->userid;
				$row['contact_form_id'] = $post['contact_form_id'] ;
				$row['id'] = null;
				$new_rows[] = $row;
			}
		
			if (! empty($new_rows))
			{
				//insert the FormBlockTodos
				$collection = new Doctrine_Collection('FormBlockTodos');
				$collection->fromArray($new_rows);
				$collection->save();
					
				//update the old ones as deleted
				$Q = Doctrine_Query::create()
				->update('FormBlockTodos')
				->set('isdelete','1')
				->set('change_date', '?' , $change_date)
				->set('change_user','?', $this->logininfo->userid)
				->whereIn('id', $deleted_rows_ids)
				->execute();
			}
		}
		return;
	}
	
	
	private function old_vs_new_values($post = array(), $allowed_blocks = array())
	{
		$result = array();
		
		if ( ! empty($post['old_contact_form_id']) && (int)$post['old_contact_form_id'] > 0)
		{
			$todos_block = new FormBlockTodos();
			$todos_old_data = $todos_block->getPatientFormBlockTodos($post['ipid'], $post['old_contact_form_id']);
			$duplicated_old =  array(); // values that are the same
			$changed_old =  array(); // values that have changed
			foreach($todos_old_data as $old_row) {
				
				$found_duplicate = false;
				
				foreach($post['todos'] as $k=>$post_row) {
					
					if ( $old_row['course_id'] == $k
							&& $old_row['text'] == $post_row['text']
							&& strtotime($old_row['date']) == strtotime($post_row['date'])
							&& $old_row['additional_info'] === $post_row['user']
					) 
					{
						//old row found in our new post... this should not be re-inserted as todo, just duplicated
						$duplicated_old[$k] = $post_row;
						$found_duplicate = true;
						break;
					} elseif ( $old_row['course_id'] == $k) 
					{
						//old row id canged... what do we do with this?
						$changed_old[$k] = $post_row;
					}
				}
				if ( ! $found_duplicate ) {
					//this todoblock is now deleted or was been edited with new values, what do we do with the allready assigned/completed todos?
				}
			}
			
			//values we must delete
			$to_be_deleted_array = array_diff_key($todos_old_data , $duplicated_old);
				
			//values we must insert
			$to_be_inserted_array = array_diff_key($post['todos'] , $duplicated_old);
			
			$result =  array(
					"not_changed" => $duplicated_old,
					"changed" => $changed_old,
					"to_be_deleted" => $to_be_deleted_array,
					"to_be_inserted" => $to_be_inserted_array,
			);
		}
		
		return $result;
	}
	
	private function clear_block_data($ipid = 0, $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id) && (int)$contact_form_id > 0)
		{
			//first get linked todos an mark them as delete
			$fbt = Doctrine_Query::create()
			->select('id, todo_id')
			->from('FormBlockTodos')
			->where("contact_form_id = ? " , $contact_form_id)
			->andWhere('ipid = ? ', $ipid)
			->fetchArray();
	
			if ( ! empty($fbt))
			{
				$fbt_ids = array_column($fbt, 'id');
				$todo_ids = array_column($fbt, 'todo_id');				
				$change_date = date("Y-m-d H:i:s", time());
	
				$T= Doctrine_Query::create()
				->update('ToDos')
				->whereIn("id", $todo_ids)
				->set('isdelete','1')
				->set('change_date', '?' , $change_date)
				->set('change_user', '?' , $this->logininfo->userid)
				->execute();
					
				//then delete todo-block itself
				$Q = Doctrine_Query::create()
				->update('FormBlockTodos')
				->whereIn("id", $fbt_ids)
				->set('isdelete','1')
				->set('change_date', '?' , $change_date)
				->set('change_user', '?' , $this->logininfo->userid)
				->execute();
			}
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>
