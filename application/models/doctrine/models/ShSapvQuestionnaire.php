<?php

	Doctrine_Manager::getInstance()->bindComponent('ShSapvQuestionnaire', 'MDAT');

	class ShSapvQuestionnaire extends BaseShSapvQuestionnaire {

		public function getPatientShSapvQuestionnaire($ipid)
		{
			$sapv_questionarie = Doctrine_Query::create()
				->select("*")
				->from('ShSapvQuestionnaire')
				->where("ipid='" . $ipid . "'")
				->orderBy('id DESC')
				->limit('1');

			$sapv_questionariearray = $sapv_questionarie->fetchArray();

			return $sapv_questionariearray;
		}

		public function get_shsapv_questionarie($id)
		{
			$sapv_questionarie = Doctrine_Query::create()
				->select("*")
				->from('ShSapvQuestionnaire')
				->where("id='" . $id . "'");

			$sapv_questionarie_array = $sapv_questionarie->fetchArray();

			return $sapv_questionarie_array;
		}

	}

?>
