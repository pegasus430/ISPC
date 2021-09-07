<?php

	Doctrine_Manager::getInstance()->bindComponent('SapvQuestionnaire', 'MDAT');

	class SapvQuestionnaire extends BaseSapvQuestionnaire {

		public function getPatientSapvQuestionnaire($ipid)
		{
			$sapv_questionarie = Doctrine_Query::create()
				->select("*")
				->from('SapvQuestionnaire')
				->where("ipid='" . $ipid . "'")
				->orderBy('id DESC')
				->limit('1');

			$sapv_questionariearray = $sapv_questionarie->fetchArray();

			return $sapv_questionariearray;
		}

		public function get_sapv_questionarie($id)
		{
			$sapv_questionarie = Doctrine_Query::create()
				->select("*")
				->from('SapvQuestionnaire')
				->where("id='" . $id . "'");

			$sapv_questionarie_array = $sapv_questionarie->fetchArray();

			return $sapv_questionarie_array;
		}

	}

?>