<?php

	Doctrine_Manager::getInstance()->bindComponent('SpecialistsTypes', 'SYSDAT');

	class SpecialistsTypes extends BaseSpecialistsTypes {

		public function get_specialists_types($client)
		{
		    
		    //ISPC-2612 Ancuta 27.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('Specialists',$client);
		    
		    
			$types = Doctrine_Query::create()
				->select('*')
				->from('SpecialistsTypes')
				->where('clientid= ? ', $client);
				if($client_is_follower){
				    $types->andWhere('connection_id is NOT null');
				    $types->andWhere('master_id is NOT null');
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

		public function get_specialists_type($stid)
		{
			$ftype = Doctrine_Query::create()
				->select('*')
				->from('SpecialistsTypes')
				->where('id = ? ', $stid);
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

		//ISPC-2254 from Nico, ONLY for ClinicVersorger
		public static function get_specialists_types_mapping ( $client )
		{
		    $sp=new SpecialistsTypes();
		    $arr=$sp->get_specialists_types($client);
		    $arr[]=array('id'=>0, 'name'=>'');
		    $out=array();
		    if($arr){
		
		        $out=array_combine(array_column($arr, 'id'), array_column($arr, 'name'));
		    }
		
		    return $out;
		
		}
		
	}

?>