<?php
Doctrine_Manager::getInstance()->bindComponent('FormBlockTodos', 'MDAT');
class FormBlockTodos extends BaseFormBlockTodos
{

	/**
	 * changed the fn, original @getPatientFormBlockTodos_OLD 
	 * Jul 14, 2017 @claudiu 
	 * 
	 * @param string $ipid
	 * @param number $contact_form_id
	 * @param string $allow_deleted
	 * @return Ambigous <multitype:multitype:NULL multitype:  , multitype:, Doctrine_Collection>
	 */
	public function getPatientFormBlockTodos ( $ipid = '', $contact_form_id = 0 , $allow_deleted = false)
	{
		$result = array();
		
		$groups_sql = $this->getTable()->createQuery("fbtd")
		->select('fbtd.*, td.*')
		->where('fbtd.contact_form_id = ? ', $contact_form_id )
		->andWhere('fbtd.ipid = ?' , $ipid )
		->innerJoin("fbtd.ToDos td");
		
		if( ! $allow_deleted )
		{
			$groups_sql->andWhere('fbtd.isdelete = 0');
			$groups_sql->groupBy("td.course_id");
		} 
		
		$groupsarray = $groups_sql->fetchArray();

		if (!empty($groupsarray))
		{
			foreach ($groupsarray as $row){
	
				$result[$row['ToDos']['course_id']] = array(
						"text" => $row['ToDos']['todo'],
						"date" => $row['ToDos']['until_date'],
						"user_id" => $row['ToDos']['user_id'],
						"group_id" => $row['ToDos']['group_id'],
						"group_id" => $row['ToDos']['group_id'],
						"group_id" => $row['ToDos']['group_id'],
						"course_id" => $row['ToDos']['course_id'],
						"additional_info" => explode(";",$row['ToDos']['additional_info']),
				);
				
				if( $allow_deleted ) {
					$result['all'][] = $row;
				}
				
			}
		}
		return $result;
	}

	
	public function getPatientFormBlockTodos_OLD ( $ipid, $contact_form_id, $allow_deleted = false)
	{

		$groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockTodos')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('contact_form_id ="' . $contact_form_id . '"');
		if(!$allow_deleted)
		{
			$groups_sql->andWhere('isdelete = 0');
		}
		
		$groupsarray = $groups_sql->fetchArray();


		if ($groupsarray)
		{

			$todo_entries=array();
			foreach ($groupsarray as $row){

				$Q= Doctrine_Query::create()
					->select('*')
					->from('ToDos')
					->where("id='" . $row['todo_id'] . "'")
					->andWhere('ipid LIKE "' . $ipid . '"');
				$result = $Q->fetchArray();
				
				$entry=array();
				$entry['text']=$result[0]['todo'];
				$entry['date']=$result[0]['until_date'];
				$entry['user']=$result[0]['user_id'];
				$entry['isgroup']=$result[0]['group_id'];
				
				$todo_entries[]=$entry;
				}
			
			return $todo_entries;
		}
	}

}
?>
