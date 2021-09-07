<?php

	Doctrine_Manager::getInstance()->bindComponent('PalliativeEmergency', 'MDAT');

	class PalliativeEmergency extends BasePalliativeEmergency {

		function get_palliative_emergency_details($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PalliativeEmergency')
				->where("ipid LIKE '" . $ipid . "'")
				->orderBy('create_date ASC');
			$form_details = $drop->fetchArray();

			if($form_details)
			{
				return $form_details;
			}
			else
			{
				return false;
			}
		}

		function get_last_palliative_emergency_details($ipid)
		{

			$drop = Doctrine_Query::create()
				->select("*")
				->from('PalliativeEmergency')
				->where("ipid LIKE '" . $ipid . "'")
				->orderBy('create_date DESC')
				->limit(1);
			$form_details = $drop->fetchArray();

			if($form_details)
			{
				return $form_details;
			}
			else
			{
				return false;
			}
		}

	}

?>