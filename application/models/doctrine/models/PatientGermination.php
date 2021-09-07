<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientGermination', 'IDAT');
	//ISPC-1897 bacteria = germination
	class PatientGermination extends BasePatientGermination {

		private $colums2fetch = "*";
		
		public function getPatientGermination($ipids = array() , $params = null )
		{
			if(empty($ipids)) {
				return;
			}
			
			$ipids = is_array($ipids) ? $ipids : [$ipids];
			
			if (!is_null($params)) {
				if (isset($params['colums2fetch'])) {
					$this->colums2fetch = $params['colums2fetch'] ;
				}
			} 			
			$q = Doctrine_Query::create()
				->select($this->colums2fetch)
				->from('PatientGermination')		
				->whereIn('ipid', $ipids)
				->andWhere('isdelete = 0 ')
				->fetchArray();

			$ret_array = array();
			
// 			print_r($ret_array);
// 			die();
			if ($q) {
				foreach ($q as $k=>$v) {
					$ret_array [$v['ipid']] = $v;
				}
			}	
			
			return $ret_array;
			
		}

	}

?>