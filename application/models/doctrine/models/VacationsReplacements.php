<?php

	Doctrine_Manager::getInstance()->bindComponent('VacationsReplacements', 'SYSDAT');

	class VacationsReplacements extends BaseVacationsReplacements {

		public function get_user_vacation_replacements($userid, $vacation, $ipid = false)
		{
			$replacements = Doctrine_Query::create()
				->select("*")
				->from('VacationsReplacements')
				->where("userid='" . $userid . "'")
				->andWhere('vacation = "' . $vacation . '"');
			if($ipid)
			{
				$replacements->andWhere('ipid = "' . $ipid . '"');
			}

			$replacements_array = $replacements->fetchArray();

			return $replacements_array;
		}

		public function get_vacation_replacements($vacation)
		{
			$repl = Doctrine_Query::create()
				->select('*')
				->from('VacationsReplacements');

			if(is_array($vacation))
			{
				$repl->whereIn('vacation', $vacation);
			}
			else
			{
				$repl->where('vacation = "' . $vacation . '"');
			}
			$repl->andWhere('replacement !=0');
			$replacements = $repl->fetchArray();

			if($replacements)
			{
				foreach($replacements as $k_repl => $v_repl)
				{
					$replacements_array[$v_repl['ipid']] = $v_repl['replacement'];
				}

				return $replacements_array;
			}
			else
			{
				return false;
			}
		}

		/**
		 * 
		 * @param unknown $users
		 * @param string $vacation
		 * @param string $ipid
		 * @return multitype:|Ambigous <multitype:, Doctrine_Collection>
		 */
		
		public function get_multiple_user_vacation_replacements($users,$ipid = false, $vacation=false)
		{
			if(empty($users)){
				return array();
			}
			
			$replacements = Doctrine_Query::create()
			->select("*")
			->from('VacationsReplacements')
			->whereIn("userid",$users);
			
			if($vacation){
				$replacements->andWhere('vacation = ?',$vacation);
			}
			if($ipid)
			{
				$replacements->andWhere('ipid = ?',$ipid );
			}
		
			$replacements_array = $replacements->fetchArray();
		
			return $replacements_array;
		}
		
	}

?>