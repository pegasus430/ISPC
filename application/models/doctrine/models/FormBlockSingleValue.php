<?php
Doctrine_Manager::getInstance()->bindComponent('FormBlockSingleValue', 'MDAT');
class FormBlockSingleValue extends BaseFormBlockSingleValue
{

	public function getPatientFormBlockSingleValue( $ipid, $contact_form_id, $allow_deleted = false, $blockname)
	{

		$groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockSingleValue')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('blockname=?',$blockname)
		    ->andWhere('contact_form_id ="' . $contact_form_id . '"');
		if(!$allow_deleted)
		{
			$groups_sql->andWhere('isdelete = 0');
		}
		
		$groupsarray = $groups_sql->fetchArray();


		if ($groupsarray)
		{
			return $groupsarray;
		}
	}

}
?>
