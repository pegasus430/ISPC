<?php

	Doctrine_Manager::getInstance()->bindComponent('Hospiceassociation', 'SYSDAT');

	class Hospiceassociation extends BaseHospiceassociation {

		public function getHospiceassociation($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Hospiceassociation')
				->where("id='" . $id . "'");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function get_hospiceassociations($ids)
		{
			if(is_array($ids))
			{
				$array_ids = $ids;
			}
			else
			{
				$array_ids = array($ids);
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Hospiceassociation')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		/**
		 * ISPC-2614 Ancuta 20.07.2020
		 * @param unknown $id
		 * @param unknown $target_client
		 * @return unknown
		 */
		public function clone_record($id, $target_client)
		{
		    $hp = $this->getHospiceassociation($id);
		    if($hp)
		    {
		        $hp_obj = new Hospiceassociation();
		        $obj_columns = $hp_obj->getTable()->getColumns();
		        
		        
		        foreach($hp as $hp_key => $hp_data)
		        {
		            $hp_obj = new Hospiceassociation();
		            foreach($obj_columns as $column_name=>$column_info){
		                if(!in_array($column_name,array('id','clientid'))){
		                    $hp_obj->$column_name = $hp_data[$column_name];
		                }
		                $hp_obj->clientid = $target_client;
		            }
		            $hp_obj->save();
		            
		            return $hp_obj->id;
		        }
		    }
		}
		

	}

?>