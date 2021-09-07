<?php

	Doctrine_Manager::getInstance()->bindComponent('DoctorLettersPermissions', 'SYSDAT');

	class DoctorLettersPermissions extends BaseDoctorLettersPermissions {

		public function getClientLetters($id)
		{
			$letters = Doctrine_Query::create()
				->select('*, l.name as letter_name, l.id as letter_id')
				->from('DoctorLettersPermissions lp')
				->leftJoin('lp.DoctorLetters l')
				->where("lp.clientid=" . $id)
				->orderBy('lp.id,l.id');

			$lettersarray = $letters->fetchArray();
			if(sizeof($lettersarray) > 0)
			{
				return $lettersarray;
			}
		}

	}

?>