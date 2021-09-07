<?php

	Doctrine_Manager::getInstance()->bindComponent('MdkSapvQuestionnaire', 'MDAT');

	class MdkSapvQuestionnaire extends BaseMdkSapvQuestionnaire {

		public function getPatientSapvQuestionnaire($ipid)
		{
			$sapv_questionarie = Doctrine_Query::create()
				->select("*")
				->from('MdkSapvQuestionnaire')
				->where('ipid=?', $ipid)
				->orderBy('id DESC')
				->limit('1');

			$sapv_questionariearray = $sapv_questionarie->fetchArray();

			return $sapv_questionariearray;
		}

		public function get_sapv_questionarie($id)
		{
			$sapv_questionarie = Doctrine_Query::create()
				->select("*")
				->from('MdkSapvQuestionnaire')
				->where('id=?', $id);

			$sapv_questionarie_array = $sapv_questionarie->fetchArray();

			return $sapv_questionarie_array;
		}

	}

?>