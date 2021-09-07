<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockBefund', 'MDAT');

	class FormBlockBefund extends BaseFormBlockBefund {

		public function getPatientFormBlockBefund($ipid, $contact_form_id, $allow_deleted = false)
		{

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockBefund')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('contact_form_id ="' . $contact_form_id . '"');

			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}

			$groupsarray = $groups_sql->fetchArray();


			if($groupsarray)
			{
				return $groupsarray;
			}
		}

	}

?>