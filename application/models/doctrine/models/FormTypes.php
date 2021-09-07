<?php

	Doctrine_Manager::getInstance()->bindComponent('FormTypes', 'SYSDAT');

	class FormTypes extends BaseFormTypes {

		public function get_form_types($client = 0, $action = false, $includedel = false) //ISPC-2629 Carmen 12.08.2020
		{
			$types = Doctrine_Query::create()
				->select('*')
				->from('FormTypes indexBy id')
				->where('clientid= ?', $client);
			//ISPC-2629 Carmen 12.08.2020
			if(!$includedel)
			{
				$types->andWhere('isdelete =?','0');
			}

			if($action)
			{
				$types->andWhere('action = "' . $action . '"');
			}

			$types_res = $types->fetchArray();

			if($types_res)
			{
				return $types_res;
			}
			else
			{
				return false;
			}
		}

		public function get_form_type($ftid)
		{
			$ftype = Doctrine_Query::create()
				->select('*')
				->from('FormTypes')
				->where('id="' . $ftid . '"');
			$ftype_res = $ftype->fetchArray();

			if($ftype_res)
			{
				return $ftype_res;
			}
			else
			{
				return false;
			}
		}

	}

?>