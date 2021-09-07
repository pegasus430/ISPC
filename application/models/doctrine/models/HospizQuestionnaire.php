<?php

	Doctrine_Manager::getInstance()->bindComponent('HospizQuestionnaire', 'MDAT');

	class HospizQuestionnaire extends BaseHospizQuestionnaire {
	   
	    //ISPC-2647 Lore 05.08.2020   rename to old
		public function getPatientHospizQuestionnaire_old($ipid)
		{
			$hospiz_questionarie = Doctrine_Query::create()
				->select("*")
				->from('HospizQuestionnaire')
				->where("ipid='" . $ipid . "'")
				->orderBy('id DESC')
				->limit('1');
			$hospiz_questionariearray = $hospiz_questionarie->fetchArray();

			return $hospiz_questionariearray;
		}

		//ISPC-2647 Lore 05.08.2020   rename to old
		public function get_hospiz_questionarie_old($id)
		{
			$hospiz_questionarie = Doctrine_Query::create()
				->select("*")
				->from('HospizQuestionnaire')
				->where("id='" . $id . "'");
			$hospiz_questionarie_array = $hospiz_questionarie->fetchArray();

			return $hospiz_questionarie_array;
		}
		
		//ISPC-2647 Lore 05.08.2020
		public function getPatientHospizQuestionnaire($ipid, $hospiz_nord)
		{
		    $hospiz_questionarie = Doctrine_Query::create()
		    ->select("*")
		    ->from('HospizQuestionnaire')
		    ->where("ipid='" . $ipid . "'")
		    ->andwhere('hospiz_nord = ? ', $hospiz_nord)
		    ->orderBy('id DESC')
		    ->limit('1');
		    $hospiz_questionariearray = $hospiz_questionarie->fetchArray();
		    
		    return $hospiz_questionariearray;
		}
		
		public function get_hospiz_questionarie($id, $hospiz_nord)
		{
		    $hospiz_questionarie = Doctrine_Query::create()
		    ->select("*")
		    ->from('HospizQuestionnaire')
		    ->where("id='" . $id . "'")
		    ->andwhere('hospiz_nord = ? ', $hospiz_nord);
		    $hospiz_questionarie_array = $hospiz_questionarie->fetchArray();
		    
		    return $hospiz_questionarie_array;
		}
		//.

	}

?>